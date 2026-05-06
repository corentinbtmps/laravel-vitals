<div id="{{ $id }}" wire:ignore></div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    new ApexCharts(document.querySelector('#{{ $id }}'), {
        chart:  { type: 'line', height: 320 },
        series: @json($data['series'] ?? []),
        xaxis:  { categories: @json($data['categories'] ?? []) },
        title:  { text: @json($options['title'] ?? '') },
    }).render();
})();
</script>
