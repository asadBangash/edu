<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1565D8" />
    <title>{{ config('edux.product_name') }} : Admin Login</title>
    <link href="{{ \App\Support\EduxBrand::faviconUrl() }}" rel="shortcut icon" type="image/svg+xml">
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
                                <h3 class="font-white bolds" style="color:#0f172a!important;">Admin Login</h3>
                                @if (!empty($errors['login']))
                                    <div class="alert alert-danger">{{ $errors['login'] }}</div>
                                @endif
                                @if (session('status'))
                                    <div class="alert alert-success">{{ session('status') }}</div>
                                @endif
                                <form action="{{ route('site.login') }}" method="post">
                                    @csrf
                                    <div class="form-group has-feedback">
                                        <input type="text" name="username" placeholder="Username" class="form-username form-control" id="form-username" value="{{ old('username') }}" required>
                                        <span class="fa fa-envelope form-control-feedback"></span>
                                    </div>
                                    <div class="form-group has-feedback">
                                        <input type="password" name="password" placeholder="Password" class="form-password form-control" id="form-password" required>
                                        <span class="fa fa-lock form-control-feedback"></span>
                                    </div>
                                    <button type="submit" class="btn">Sign In</button>
                                </form>
                                <div class="edux-role-grid" aria-label="Staff roles">
                                    <button type="button" class="edux-role-btn edux-r-super" onclick="fillStaff('superadmin@gmail.com')"><i class="fa fa-star"></i> Super Admin</button>
                                    <button type="button" class="edux-role-btn edux-r-admin" onclick="fillStaff('admin@gmail.com')"><i class="fa fa-user"></i> Admin</button>
                                    <button type="button" class="edux-role-btn edux-r-teacher" onclick="fillStaff('teacher@gmail.com')"><i class="fa fa-blackboard"></i> Teacher</button>
                                    <button type="button" class="edux-role-btn edux-r-accountant" onclick="fillStaff('accountant@gmail.com')"><i class="fa fa-money"></i> Accountant</button>
                                    <button type="button" class="edux-role-btn edux-r-receptionist" onclick="fillStaff('receptionist@gmail.com')"><i class="fa fa-phone"></i> Receptionist</button>
                                    <button type="button" class="edux-role-btn edux-r-librarian" onclick="fillStaff('librarian@gmail.com')"><i class="fa fa-book"></i> Librarian</button>
                                </div>
                                <p class="edux-login-links">
                                    <a href="{{ route('demo') }}" class="forgot"><i class="fa fa-home"></i> Demo home</a> |
                                    <a href="{{ route('site.userlogin') }}" class="forgot"><i class="fa fa-users"></i> User login</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-12 nopadding-2">
                        <div class="d-flex align-items-center text-wrap flex-column justify-content-center bg-position-sm-left bg-position-lg-center" style="background: linear-gradient(rgba(15,23,42,.55),rgba(15,23,42,.55)), url('{{ url('uploads/school_content/login_image/'.($school->admin_login_page_background ?? '')) }}') center/cover no-repeat; min-height: 420px;">
                            <div class="edux-login-notices p-4 text-white"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function fillStaff(email) {
    document.getElementById('form-username').value = email;
    document.getElementById('form-password').value = 'Demo@123';
}
(function () {
    var map = { superadmin: 'superadmin@gmail.com', admin: 'admin@gmail.com', teacher: 'teacher@gmail.com', accountant: 'accountant@gmail.com', receptionist: 'receptionist@gmail.com', librarian: 'librarian@gmail.com' };
    var role = (location.hash || '').replace('#', '');
    if (map[role]) { fillStaff(map[role]); }
})();
</script>
</body>
</html>