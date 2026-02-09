@extends('layouts.main')

@section('title', 'Admin Dashboard – Blue Wave Music')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 text-white mb-1">Admin Dashboard</h1>
            <p class="text-muted">Welcome back, {{ auth()->user()->name }}</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h5 class="card-title text-muted small">Total Users</h5>
                    <h2 class="text-white">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h5 class="card-title text-muted small">Total Songs</h5>
                    <h2 class="text-white">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h5 class="card-title text-muted small">Total Albums</h5>
                    <h2 class="text-white">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h5 class="card-title text-muted small">Active Singers</h5>
                    <h2 class="text-white">0</h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
