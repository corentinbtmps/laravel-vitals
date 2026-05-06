<div id="{{ $id }}" wire:ignore></div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    new ApexCharts(document.querySelector('#{{ $id }}'), {
        chart:  { type: 'radialBar', height: 240 },
        series: [@json((int) ($data['value'] ?? 0))],
        labels: [@json($options['label'] ?? 'Score')],
    }).render();
})();
</script>
