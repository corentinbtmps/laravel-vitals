<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Spatie\Searchable\Search;

final class Spotlight extends Component
{
    public string $query = '';

    /**
     * @return array<string, Collection<int, \Spatie\Searchable\SearchResult>>
     */
    public function getResultsByType(): array
    {
        if (mb_strlen(trim($this->query)) < 2) {
            return [];
        }

        $search = new Search();

        foreach ((array) app('vitals.search-aspects') as $aspect) {
            $search->registerAspect($aspect);
        }

        return $search->perform($this->query)->groupByType()->toArray();
    }

    public function render(): View
    {
        return view('vitals::livewire.components.spotlight', [
            'resultsByType' => $this->getResultsByType(),
            'query'         => $this->query,
        ]);
    }
}
