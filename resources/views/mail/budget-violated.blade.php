<x-mail::message>
# Budget violation: {{ $audit->url?->label }}

The audit for **{{ $audit->url?->label }}** violated {{ count($violations->all()) }} budget(s).

@foreach ($violations->all() as $v)
- `{{ $v['metric'] }}` = {{ $v['actual'] }} (>{{ $v['severity'] }} threshold {{ $v['threshold'] }})
@endforeach

Thanks,<br>
Laravel Vitals
</x-mail::message>
