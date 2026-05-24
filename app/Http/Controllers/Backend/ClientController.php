<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('clients.index');
    }
}
