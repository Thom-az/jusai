<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        return view('documentos.index');
    }

    public function create(): View
    {
        return view('documentos.create');
    }

    public function store(Request $request)
    {
        abort(501, 'Not implemented yet.');
    }

    public function show(string $id): View
    {
        return view('documentos.show');
    }

    public function edit(string $id): View
    {
        return view('documentos.edit');
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
