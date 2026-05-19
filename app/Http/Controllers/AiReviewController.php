<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AiReviewController extends Controller
{
    public function index(): View
    {
        return view('revisor.index');
    }
}
