@extends('layouts.master')
@section('title','New Expense')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>New Expense</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.expenses.index') }}">Expenses</a></li>
                        <li class="breadcrumb-item active" aria-current="page">New</li>
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
                <form method="post" action="{{ route('hrm.expenses.store') }}" class="row g-3" enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" value="{{ old('type') }}" class="form-control" required>
                        @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="form-control" required>
                        @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">None</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ (string)old('supplier_id')===(string)$s->id?'selected':'' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Organization</label>
                        <select name="organization_id" class="form-select">
                            <option value="">None</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ (string)old('organization_id')===(string)$org->id?'selected':'' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                        @error('organization_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Document (PDF/DOC/DOCX/JPG/PNG)</label>
                        <input type="file" name="upload_document" class="form-control">
                        @error('upload_document')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ old('status')==='pending'?'selected':'' }}>Pending</option>
                        </select>
                        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
