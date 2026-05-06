<div id="{{ $id }}" wire:ignore></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new ApexCharts(document.querySelector('#{{ $id }}'), {
        chart:  { type: 'radialBar', height: 240 },
        series: [@json((int) ($data['value'] ?? 0))],
        labels: [@json($options['label'] ?? 'Score')],
    }).render();
});
</script>
