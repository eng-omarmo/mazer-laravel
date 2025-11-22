@extends('layouts.master')

@section('title','Account')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Account</h3>
                <p class="text-subtitle text-muted">Manage your profile and security</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Account</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header"><h4><i class="bi bi-person-circle me-2"></i>Profile Information</h4></div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header"><h4><i class="bi bi-shield-lock me-2"></i>Update Password</h4></div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h4><i class="bi bi-activity me-2"></i>Recent Activity</h4></div>
                    <div class="card-body">
                        @include('profile.partials.activity-logs')
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
