<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::orderBy('name')->paginate(10);

        return view('hrm.organizations', compact('organizations'));
    }

    public function create()
    {
        return view('hrm.organizations-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:organizations,name'],
        ]);
        Organization::create(['name' => $validated['name']]);

        return redirect()->route('hrm.organizations.index')->with('status', 'Organization created');
    }

    public function edit(Organization $organization)
    {
        return view('hrm.organizations-edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:organizations,name,'.$organization->id],
        ]);
        $organization->update(['name' => $validated['name']]);

        return redirect()->route('hrm.organizations.index')->with('status', 'Organization updated');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('hrm.organizations.index')->with('status', 'Organization deleted');
    }
}
