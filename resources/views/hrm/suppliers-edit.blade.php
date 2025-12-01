@extends('layouts.master')
@section('title','Edit Supplier')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Supplier</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.suppliers.index') }}">Suppliers</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form method="post" action="{{ route('hrm.suppliers.update', $supplier) }}" class="row g-3">
                    @csrf
                    @method('patch')
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="form-control" required>
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="form-control" required>
                        @error('contact_person')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-control" required>
                        @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account</label>
                        <input type="text" name="account" value="{{ old('account', $supplier->account) }}" class="form-control">
                        @error('account')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="form-control">
                        @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ (string)old('status', $supplier->status)==='active'?'selected':'' }}>Active</option>
                            <option value="inactive" {{ (string)old('status', $supplier->status)==='inactive'?'selected':'' }}>Inactive</option>
                        </select>
                        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $supplier->notes) }}</textarea>
                        @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Update</button>
                        <a href="{{ route('hrm.suppliers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
