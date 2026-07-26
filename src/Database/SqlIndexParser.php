<?php

declare(strict_types=1);

namespace LaravelVitals\Database;

/**
 * Extracts index-relevant columns from a SELECT statement using conservative
 * regex heuristics — the primary FROM table plus the columns used in WHERE,
 * JOIN ... ON, and ORDER BY clauses.
 *
 * This is intentionally best-effort: it only reasons about columns on the
 * primary table (unqualified, or qualified with that table's name/alias). Any
 * mis-association is caught downstream by the schema inspector, which discards
 * columns that do not actually exist on the table. Non-SELECT statements and
 * anything it cannot confidently parse return null.
 */
final class SqlIndexParser
{
    /** SQL keywords that must never be treated as column names. */
    private const STOP_WORDS = [
        'and', 'or', 'not', 'null', 'is', 'in', 'like', 'between', 'exists',
        'select', 'from', 'where', 'join', 'on', 'as', 'by', 'order', 'group',
        'having', 'limit', 'offset', 'asc', 'desc', 'inner', 'left', 'right',
        'outer', 'cross', 'union', 'distinct', 'case', 'when', 'then', 'else', 'end',
    ];

    public function parse(string $sql): ?ParsedQuery
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql) ?? $sql);

        if (! preg_match('/^select\b/i', $sql)) {
            return null;
        }

        $table = $this->primaryTable($sql);
        if ($table === null) {
            return null;
        }

        $alias = $this->primaryAlias($sql, $table);

        /** @var array<string, string> $columns */
        $columns = [];

        foreach ($this->whereColumns($sql) as $ref) {
            $this->add($columns, $ref, $table, $alias, 'where');
        }
        foreach ($this->joinColumns($sql) as $ref) {
            $this->add($columns, $ref, $table, $alias, 'join');
        }
        foreach ($this->orderColumns($sql) as $ref) {
            $this->add($columns, $ref, $table, $alias, 'order');
        }

        return new ParsedQuery($table, $columns);
    }

    private function primaryTable(string $sql): ?string
    {
        // The operand of the FIRST `from` — a derived table starts with "(".
        if (! preg_match('/\bfrom\s+(\S+)/i', $sql, $m)) {
            return null;
        }

        if (str_starts_with($m[1], '(')) {
            return null;
        }

        $table = $this->clean($m[1]);

        return ($table === '' || str_contains($table, '(')) ? null : $table;
    }

    private function primaryAlias(string $sql, string $table): string
    {
        // `from <table> [as] <alias>` where alias is not a following keyword.
        if (preg_match('/\bfrom\s+[`"\[\]\w.]+\s+(?:as\s+)?([`"\[\]\w]+)/i', $sql, $m)) {
            $alias = strtolower($this->clean($m[1]));
            if ($alias !== '' && ! in_array($alias, self::STOP_WORDS, true)) {
                return $alias;
            }
        }

        return strtolower($table);
    }

    /**
     * @return list<string> raw column references (possibly `alias.col`)
     */
    private function whereColumns(string $sql): array
    {
        $clause = $this->clause($sql, '/\bwhere\b/i', '/\b(group by|order by|limit|having|union)\b/i');
        if ($clause === null) {
            return [];
        }

        preg_match_all(
            '/([`"\[\]\w.]+)\s*(?:=|<=|>=|<>|!=|<|>|\bin\b|\blike\b|\bbetween\b)/i',
            $clause,
            $m,
        );

        return $m[1];
    }

    /**
     * @return list<string>
     */
    private function joinColumns(string $sql): array
    {
        preg_match_all('/\bon\s+(.+?)(?=\b(?:where|inner|left|right|join|group by|order by|limit|having|union)\b|$)/i', $sql, $clauses);

        $refs = [];
        foreach ($clauses[1] as $clause) {
            preg_match_all('/([`"\[\]\w.]+)\s*=\s*([`"\[\]\w.]+)/', $clause, $m, PREG_SET_ORDER);
            foreach ($m as $pair) {
                $refs[] = $pair[1];
                $refs[] = $pair[2];
            }
        }

        return $refs;
    }

    /**
     * @return list<string>
     */
    private function orderColumns(string $sql): array
    {
        $clause = $this->clause($sql, '/\border by\b/i', '/\b(limit|offset|having|union)\b/i');
        if ($clause === null) {
            return [];
        }

        $refs = [];
        foreach (explode(',', $clause) as $term) {
            $term = trim(preg_replace('/\b(asc|desc)\b/i', '', $term) ?? $term);
            $first = preg_split('/\s+/', $term)[0] ?? '';
            if ($first !== '') {
                $refs[] = $first;
            }
        }

        return $refs;
    }

    /**
     * Isolate a clause between a start marker and the first following marker.
     */
    private function clause(string $sql, string $start, string $end): ?string
    {
        if (! preg_match($start, $sql, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $from = $m[0][1] + strlen($m[0][0]);
        $rest = substr($sql, $from);

        if (preg_match($end, $rest, $e, PREG_OFFSET_CAPTURE)) {
            $rest = substr($rest, 0, $e[0][1]);
        }

        return trim($rest);
    }

    /**
     * Resolve a raw reference to a bare column on the primary table, or skip it.
     *
     * @param array<string, string> $columns
     */
    private function add(array &$columns, string $ref, string $table, string $alias, string $reason): void
    {
        $ref = $this->clean($ref);
        if ($ref === '' || str_contains($ref, '(') || str_contains($ref, '*')) {
            return;
        }

        $prefix = null;
        $column = $ref;
        if (str_contains($ref, '.')) {
            [$prefix, $column] = explode('.', $ref, 2);
            $prefix = strtolower($prefix);
        }

        $column = strtolower($column);

        if ($column === '' || is_numeric($column) || in_array($column, self::STOP_WORDS, true)) {
            return;
        }

        // Only reason about the primary table: unqualified, or qualified with its
        // name/alias. Columns owned by joined tables are left to a future pass.
        if (!in_array($prefix, [null, $alias, strtolower($table)], true)) {
            return;
        }

        // Prefer the strongest reason if the same column appears more than once.
        $rank = ['where' => 3, 'join' => 2, 'order' => 1];
        if (! isset($columns[$column]) || $rank[$reason] > $rank[$columns[$column]]) {
            $columns[$column] = $reason;
        }
    }

    private function clean(string $identifier): string
    {
        return trim(str_replace(['`', '"', '[', ']'], '', $identifier));
    }
}
