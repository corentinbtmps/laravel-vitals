@php $value = (int) ($data['value'] ?? 0); @endphp
<flux:card class="text-center">
    <div class="text-5xl font-bold">{{ $value }}</div>
    <div class="text-sm text-muted">{{ $options['label'] ?? 'Score' }}</div>
</flux:card>
