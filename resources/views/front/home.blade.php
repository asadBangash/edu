<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $setting->name ?? config('edux.product_name') }}</title>
    <link rel="stylesheet" href="{{ url('backend/dist/css/edux-front.css') }}">
    <link rel="stylesheet" href="{{ url('backend/usertemplate/assets/bootstrap/css/bootstrap.min.css') }}">
</head>
<body>
<header class="p-3" style="background:#1e40af;color:#fff;">
    <strong>{{ config('edux.product_full') }}</strong>
    <nav class="pull-right">
        <a href="{{ route('demo') }}" style="color:#fff;margin-left:12px;">Demo</a>
        <a href="{{ route('site.login') }}" style="color:#fff;margin-left:12px;">Staff Login</a>
    </nav>
</header>
<main class="container" style="margin-top:24px;">
    <h1>{{ $page->page_title ?? $setting->name ?? 'Welcome' }}</h1>
    <div class="cms-content">
        {!! $page->description ?? '<p>Front CMS content</p>' !!}
    </div>
</main>
<footer class="text-center p-4 text-muted">
    {{ config('edux.developed_by') }}
</footer>
</body>
</html>