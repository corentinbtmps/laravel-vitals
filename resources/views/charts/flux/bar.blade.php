@php $series = $data['series'] ?? []; @endphp
<flux:chart class="aspect-[3/1]">
    <flux:chart.svg>
        @foreach($series as $s)
            <flux:chart.bar :field="@json($s['name'] ?? 'value')" />
        @endforeach
    </flux:chart.svg>
</flux:chart>
