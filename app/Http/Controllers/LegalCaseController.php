<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalCaseController extends Controller
{
    public function index(): View
    {
        return view('casos.index');
    }

    public function create(): View
    {
        return view('casos.create');
    }

    public function store(Request $request)
    {
        abort(501, 'Not implemented yet.');
    }

    public function show(string $id): View
    {
        return view('casos.show');
    }

    public function edit(string $id): View
    {
        return view('casos.edit');
    }

    public function update(Request $request, string $id)
    {
        abort(501, 'Not implemented yet.');
    }

    public function destroy(string $id)
    {
        abort(501, 'Not implemented yet.');
    }
}
