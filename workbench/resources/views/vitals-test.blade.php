<!doctype html>
<html><body>
<h1>Vitals Test Page</h1>
<ul>
@foreach($records as $r)
    <li>{{ $r->name }}</li>
@endforeach
</ul>
</body></html>
