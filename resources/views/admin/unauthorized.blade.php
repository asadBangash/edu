@extends('layouts.admin')

@section('title', 'Unauthorized')

@section('content')
    <section class="content-header">
        <h1>Unauthorized</h1>
    </section>
    <section class="content">
        <div class="alert alert-warning">
            You do not have permission to access this page.
        </div>
        <a href="{{ url('admin/admin/dashboard') }}" class="btn btn-primary">Back to dashboard</a>
    </section>
@endsection