@extends('layouts.master')
@section('title','Create Department')
@section('content')
<div class="page-heading">
    <h3>Create Department</h3>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('hrm.departments.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                        @error('description')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('hrm.departments.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection