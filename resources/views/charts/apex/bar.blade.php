<div id="{{ $id }}" wire:ignore></div>
<script>
(function () {
    new ApexCharts(document.querySelector('#{{ $id }}'), {
        chart:  { type: 'bar', height: 320 },
        series: @json($data['series'] ?? []),
        xaxis:  { categories: @json($data['categories'] ?? []) },
        title:  { text: @json($options['title'] ?? '') },
    }).render();
})();
</script>
