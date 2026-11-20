@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<section class="content-header">
    <h1>Dashboard <small>{{ $admin['school_name'] ?? '' }}</small></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Signed in as</span>
                    <span class="info-box-number">{{ $admin['username'] ?? '' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Laravel migration status</h3>
                </div>
                <div class="box-body">
                    <p>Core authentication and this dashboard are running on Laravel {{ app()->version() }}.</p>
                    <p>Additional admin modules from the CodeIgniter application are being ported module-by-module. See <code>docs/MIGRATION_CHECKLIST.md</code> in the Laravel project.</p>
                </div>
            </div>
        </div>
    </div>
    @if ($notifications->count())
        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">Notifications</h3></div>
            <div class="box-body">
                <ul class="list-unstyled">
                    @foreach ($notifications as $notice)
                        <li>{{ $notice->title ?? $notice->message ?? 'Notification' }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</section>
@endsection