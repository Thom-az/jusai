<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        $organizations = Organization::withCount('users')
            ->latest()
            ->paginate(20);

        return view('admin.organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('admin.organizations.create');
    }

    public function store()
    {
        abort(501, 'Not implemented yet.');
    }

    public function show(Organization $organization): View
    {
        $organization->load('users', 'subscriptions', 'supportTickets');

        return view('admin.organizations.show', compact('organization'));
    }

    public function edit(Organization $organization): View
    {
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update()
    {
        abort(501, 'Not implemented yet.');
    }

    public function destroy()
    {
        abort(501, 'Not implemented yet.');
    }
}
