@extends('layouts.master')
@section('title','Edit Department')
@section('content')
<div class="page-heading">
    <h3>Edit Department</h3>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('hrm.departments.update',$department) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name',$department->name) }}" class="form-control" required>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control">{{ old('description',$department->description) }}</textarea>
                        @error('description')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('hrm.departments.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection