<x-mail::message>
# Performance regression: {{ $url->label }}

Performance score for **{{ $url->label }}** dropped from {{ $baselineScore }} to {{ $currentScore }} (-{{ $dropPercent }}%).

Investigate recent deploys or content changes.

Thanks,<br>
Laravel Vitals
</x-mail::message>
