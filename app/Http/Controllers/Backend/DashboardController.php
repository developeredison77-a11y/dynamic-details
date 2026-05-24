<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index', [
            'settings' => Setting::allSettings(),
        ]);
    }
}
