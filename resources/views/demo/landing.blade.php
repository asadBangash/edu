<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>EduX School System – Demo</title>
<link rel="icon" href="{{ url(config('edux.favicon_path')) }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ url('backend/usertemplate/assets/font-awesome/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ url('backend/dist/css/edux-login.css') }}">
<style>
body{margin:0;font-family:Inter,Arial,sans-serif;color:#0f172a;background:#f8fafc}
.hero{padding:64px 20px 130px;text-align:center;color:#fff;background:linear-gradient(135deg,rgba(13,71,161,.92),rgba(21,101,216,.85)),url('{{ url('uploads/school_content/login_image/1662796099-1140705836631c4143b6dc3!login_bg6.jpg') }}') center/cover}
.hero img{max-width:min(320px,90vw);height:auto;margin-bottom:12px}.hero h1{font-size:42px;margin:10px 0}.hero p{opacity:.92;max-width:640px;margin:0 auto}
.wrap{max-width:1100px;margin:-90px auto 50px;padding:0 16px}
.grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 8px 24px rgba(30,64,175,.1);border:1px solid #e2e8f0}
.card h3{margin:8px 0;font-size:16px}.card p{font-size:13px;color:#64748b;min-height:56px}.card a{color:#1565d8;font-weight:600;text-decoration:none}
.ic{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff}
.c1{background:#1e3a8a}.c2{background:#6366f1}.c3{background:#ec4899}.c4{background:#0ea5e9}.c5{background:#475569}
.c6{background:#2563eb}.c7{background:#059669}.c8{background:#ca8a04}.c9{background:#db2777}.c10{background:#0d9488}
footer{text-align:center;color:#64748b;font-size:12px;padding:24px}
@media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>
<section class="hero">
  <img src="{{ url(config('edux.logo_path')) }}" alt="EduX">
  <h1 style="font-size:28px;margin:0 0 8px">EduX School System</h1>
  <p>Powered by Global TechX Solutions</p>
  <h2 style="font-size:36px;margin:16px 0;font-weight:800">Try the <span style="color:#00b4ff">EduX</span> Demo</h2>
  <p>Click a role — login opens with that user’s credentials filled in. Password: <strong>Demo@123</strong>.</p>
</section>
<div class="wrap"><div class="grid">
  <div class="card"><div class="ic c1"><i class="fa fa-star"></i></div><h3>Superadmin</h3><p>System control and settings</p><a href="{{ route('site.login') }}#superadmin">Login &rarr;</a></div>
  <div class="card"><div class="ic c2"><i class="fa fa-graduation-cap"></i></div><h3>Student</h3><p>Academic panel</p><a href="{{ route('site.userlogin') }}#student">Login &rarr;</a></div>
  <div class="card"><div class="ic c3"><i class="fa fa-users"></i></div><h3>Parent</h3><p>Monitor child progress</p><a href="{{ route('site.userlogin') }}#parent">Login &rarr;</a></div>
  <div class="card"><div class="ic c4"><i class="fa fa-globe"></i></div><h3>Front Website</h3><p>Public school site</p><a href="{{ route('frontend.home') }}">Visit &rarr;</a></div>
  <div class="card"><div class="ic c5"><i class="fa fa-mobile"></i></div><h3>Mobile App</h3><p>Android app (CodeCanyon)</p><a href="https://1.envato.market/smart_school_android_app" target="_blank">Info &rarr;</a></div>
  <div class="card"><div class="ic c6"><i class="fa fa-user"></i></div><h3>Admin</h3><p>School operations</p><a href="{{ route('site.login') }}#admin">Login &rarr;</a></div>
  <div class="card"><div class="ic c7"><i class="fa fa-blackboard"></i></div><h3>Teacher</h3><p>Teaching tools</p><a href="{{ route('site.login') }}#teacher">Login &rarr;</a></div>
  <div class="card"><div class="ic c8"><i class="fa fa-money"></i></div><h3>Accountant</h3><p>Fees &amp; finance</p><a href="{{ route('site.login') }}#accountant">Login &rarr;</a></div>
  <div class="card"><div class="ic c9"><i class="fa fa-phone"></i></div><h3>Receptionist</h3><p>Front desk</p><a href="{{ route('site.login') }}#receptionist">Login &rarr;</a></div>
  <div class="card"><div class="ic c10"><i class="fa fa-book"></i></div><h3>Librarian</h3><p>Library</p><a href="{{ route('site.login') }}#librarian">Login &rarr;</a></div>
</div></div>
<footer>EduX · Developed by <a href="https://globaltechxsolutions.com/" style="color:#1e40af">Global TechX Solutions</a></footer>
</body>
</html>