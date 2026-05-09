@if (config('vitals.rum.enabled', true))
<script>
    window.__VITALS_RUM__ = {
        endpoint: @json(route('vitals.rum.ingest')),
        sample_rate: {{ config('vitals.rum.sample_rate', 1.0) }},
    };
</script>
<script src="{{ route('vitals.assets', 'vitals-rum.js') }}" defer></script>
@endif
