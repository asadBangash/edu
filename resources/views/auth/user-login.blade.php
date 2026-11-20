<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1565D8" />
    <title>{{ config('edux.product_name') }} : User Login</title>
    <link href="{{ \App\Support\EduxBrand::faviconUrl() }}" rel="shortcut icon" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700">
    <link rel="stylesheet" href="{{ url('backend/usertemplate/assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('backend/usertemplate/assets/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('backend/usertemplate/assets/css/form-elements.css') }}">
    <link rel="stylesheet" href="{{ url('backend/usertemplate/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ url('backend/dist/css/edux-login.css') }}">
</head>
<body>
<div class="top-content">
    <div class="inner-bg">
        <div class="container-fluid">
            <div class="row">
                <div class="bgoffsetbg">
                    <div class="col-lg-4 col-md-4 col-sm-12 nopadding bgoffsetbgno">
                        <div class="loginbg">
                            <div class="form-top">
                                <div class="form-top-left logowidth">
                                    @include('partials.edux-login-brand')
                                </div>
                            </div>
                            <div class="form-bottom">
                                <h3 class="edux-login-heading">User Login</h3>
                                <div class="edux-demo-hint">
                                    <strong>Demo:</strong> Student <code>std2</code> or Parent <code>parent</code> — password <code>Demo@123</code>
                                </div>
                                @if (!empty($errors['login']))
                                    <div class="alert alert-danger">{{ $errors['login'] }}</div>
                                @endif
                                <form action="{{ route('site.userlogin') }}" method="post" autocomplete="on">
                                    @csrf
                                    <div class="form-group has-feedback">
                                        <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" class="form-username form-control" id="email" required>
                                        <span class="fa fa-user form-control-feedback"></span>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <input type="password" name="password" placeholder="Password" class="form-password form-control" id="password" required>
                                        <span class="fa fa-lock form-control-feedback"></span>
                                    </div>
                                    <button type="submit" class="btn">Sign In</button>
                                </form>
                                <div class="edux-role-grid">
                                    <button type="button" class="edux-role-btn edux-r-student" id="role-student" onclick="fillUser('std2')"><i class="fa fa-graduation-cap"></i> Student</button>
                                    <button type="button" class="edux-role-btn edux-r-parent" id="role-parent" onclick="fillUser('parent')"><i class="fa fa-users"></i> Parent</button>
                                </div>
                                <p class="edux-login-links">
                                    <a href="{{ route('demo') }}">Demo home</a> |
                                    <a href="{{ route('site.login') }}">Staff login</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-12 nopadding-2">
                        @php
                            $bg = $school->user_login_page_background ?? null;
                            $bgUrl = $bg ? url('uploads/school_content/login_image/'.$bg) : null;
                        @endphp
                        <div class="d-flex align-items-center text-wrap flex-column justify-content-center bg-position-sm-left bg-position-lg-center edux-login-hero"
                             @if($bgUrl) style="background: linear-gradient(135deg, rgba(13,71,161,.85), rgba(21,101,216,.7)), url('{{ $bgUrl }}') center/cover no-repeat;" @endif>
                            @include('partials.edux-login-notices')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function fillUser(u) {
    document.getElementById('email').value = u;
    document.getElementById('password').value = 'Demo@123';
    document.getElementById('role-student').classList.toggle('active', u === 'std2');
    document.getElementById('role-parent').classList.toggle('active', u === 'parent');
}
var role = (location.hash || '').replace('#', '');
if (role === 'student') fillUser('std2');
if (role === 'parent') fillUser('parent');
</script>
</body>
</html>