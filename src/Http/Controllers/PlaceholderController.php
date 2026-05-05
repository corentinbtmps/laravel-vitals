<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class PlaceholderController extends Controller
{
    public function __invoke(): View
    {
        /** @var view-string */
        $view = 'vitals::dashboard.placeholder';

        return view($view);
    }
}
