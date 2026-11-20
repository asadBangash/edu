<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->page_title ?? config('edux.product_name') }}</title>
    <link rel="stylesheet" href="{{ url('backend/dist/css/edux-front.css') }}">
</head>
<body>
<main class="container" style="margin:24px auto;max-width:960px;">
    <h1>{{ $page->page_title ?? '' }}</h1>
    <div>{!! $page->description ?? '' !!}</div>
    <p><a href="{{ route('frontend.home') }}">&larr; Back to home</a></p>
</main>
</body>
</html>