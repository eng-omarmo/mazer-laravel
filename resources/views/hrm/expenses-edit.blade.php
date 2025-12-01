@extends('layouts.master')
@section('title','Edit Expense')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Expense</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.expenses.index') }}">Expenses</a></li>
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
                <form method="post" action="{{ route('hrm.expenses.update', $expense) }}" class="row g-3" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" value="{{ old('type', $expense->type) }}" class="form-control" required>
                        @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="form-control" required>
                        @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">None</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ (string)old('supplier_id', $expense->supplier_id)===(string)$s->id?'selected':'' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Organization</label>
                        <select name="organization_id" class="form-select">
                            <option value="">None</option>
                            @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ (string)old('organization_id', $expense->organization_id)===(string)$org->id?'selected':'' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                        @error('organization_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Document (PDF/DOC/DOCX/JPG/PNG)</label>
                        <input type="file" name="upload_document" class="form-control">
                        @error('upload_document')<small class="text-danger">{{ $message }}</small>@enderror
                        @if($expense->document_path)
                        <small class="text-muted">Current: <a href="{{ asset('storage/'.$expense->document_path) }}" target="_blank">View</a></small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ (string)old('status', $expense->status)==='pending'?'selected':'' }}>Pending</option>
                            <option value="reviewed" {{ (string)old('status', $expense->status)==='reviewed'?'selected':'' }}>Reviewed</option>
                            <option value="approved" {{ (string)old('status', $expense->status)==='approved'?'selected':'' }}>Approved</option>
                        </select>
                        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Update</button>
                        <a href="{{ route('hrm.expenses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
