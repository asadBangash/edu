<img src="{{ \App\Support\EduxBrand::logoUrl() }}" alt="{{ config('edux.product_name') }}" class="edux-login-product-logo" />
<h2 style="color:#1565d8;font-size:22px;font-weight:700;margin:8px 0 4px;line-height:1.3">{{ config('edux.product_full') }}</h2>
<p class="edux-login-tagline">{{ $school->name ?? config('edux.product_name') }}</p>
<div class="edux-powered">
    {{ config('edux.powered_by') }}<br>
    <a href="{{ config('edux.company_url') }}" target="_blank" rel="noopener">{{ config('edux.developed_by') }}</a>
</div>