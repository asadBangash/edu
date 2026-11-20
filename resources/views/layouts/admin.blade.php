<!DOCTYPE html>
<html lang="en" {!! $customlib->getRTL() !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $customlib->getAppName() }} — @yield('title', 'Admin')</title>
    <link href="{{ \App\Support\EduxBrand::faviconUrl() }}" rel="shortcut icon" type="image/svg+xml">
    <link rel="stylesheet" href="{{ url('backend/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('backend/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ url('backend/dist/css/skins/_all-skins.min.css') }}">
    <link rel="stylesheet" href="{{ url('backend/dist/css/style-main.css') }}">
    <link rel="stylesheet" href="{{ url('theme.css') }}">
    @stack('styles')
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <header class="main-header">
        <a href="{{ url('admin/admin/dashboard') }}" class="logo">
            <span class="logo-mini"><b>E</b>X</span>
            <span class="logo-lg">{{ config('edux.product_name') }}</span>
        </a>
        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li><span class="navbar-text">{{ $admin['username'] ?? '' }}</span></li>
                    <li>
                        <a href="{{ route('site.logout') }}">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    @include('partials.admin.sidebar')

    <div class="content-wrapper">
        @yield('content')
    </div>
    <footer class="main-footer text-center">
        {{ config('edux.developed_by') }}
    </footer>
</div>
<script src="{{ url('backend/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<script src="{{ url('backend/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ url('backend/dist/js/app.min.js') }}"></script>
@stack('scripts')
</body>
</html>