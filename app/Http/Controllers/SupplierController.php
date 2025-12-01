<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(10);

        return view('hrm.suppliers', compact('suppliers'));
    }

    public function create()
    {
        return view('hrm.suppliers-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:suppliers,name'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'account' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);
        Supplier::create($validated);

        return redirect()->route('hrm.suppliers.index')->with('status', 'Supplier created');
    }

    public function edit(Supplier $supplier)
    {
        return view('hrm.suppliers-edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:suppliers,name,'.$supplier->id],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'account' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);
        $supplier->update($validated);

        return redirect()->route('hrm.suppliers.index')->with('status', 'Supplier updated');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return back()->with('status', 'Supplier deleted');
    }
}
