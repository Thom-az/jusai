<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        return view('admin.leads.index');
    }

    public function comparison(): View
    {
        return view('admin.leads.comparison');
    }
}
