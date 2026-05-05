<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class PlaceholderController extends Controller
{
    public function __invoke(): View
    {
        return view('vitals::dashboard.placeholder');
    }
}
