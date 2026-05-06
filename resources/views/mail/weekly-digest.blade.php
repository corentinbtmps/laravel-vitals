<x-mail::message>
# Laravel Vitals — weekly digest

Total audits this week: {{ $totalAudits }}

@foreach ($rows as $r)
- **{{ $r['label'] }}**: {{ $r['audits'] }} audit(s), avg perf {{ $r['avg_perf'] }}
@endforeach

Thanks,<br>
Laravel Vitals
</x-mail::message>
