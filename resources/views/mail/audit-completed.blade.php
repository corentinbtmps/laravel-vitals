<x-mail::message>
# {{ $audit->url?->label }} — audit completed

Performance: {{ $audit->score_performance ?? 'n/a' }}
Accessibility: {{ $audit->score_accessibility ?? 'n/a' }}
Best Practices: {{ $audit->score_best_practices ?? 'n/a' }}
SEO: {{ $audit->score_seo ?? 'n/a' }}

LCP: {{ $audit->lcp_ms !== null ? round((float) $audit->lcp_ms) . ' ms' : 'n/a' }}

Thanks,<br>
Laravel Vitals
</x-mail::message>
