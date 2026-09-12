@extends('layouts.app')
@section('title', 'Sign Up — BookNest')
@section('content')
<div class="auth-wrap">
    <div class="auth-card card">
        <div class="auth-header">
            <img src="{{ asset('images/logo.png') }}" alt="BookNest">
            <strong>BookNest Library</strong>
        </div>
        <div class="auth-body">
            <h2 style="margin-top:0;">Create your account</h2>
            <p class="book-author" style="margin-bottom:14px;">Join the BookNest community</p>
            <div class="card card-pad auth-role-note" style="margin-bottom:20px;">
                <strong>New accounts are User accounts.</strong>
                <p class="book-author" style="margin:5px 0 0;">For security, an Admin account cannot be created from public registration. An existing Admin can create or promote accounts from Admin → Users.</p>
            </div>

            @if ($errors->any())
                <div class="form-error" style="margin-bottom:16px;">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Sign Up</button>
            </form>
            <p style="margin-top:18px;font-size:0.88rem;">Already have an account? <a href="{{ route('login') }}" style="color:var(--color-button);font-weight:600;">Login</a></p>
        </div>
    </div>
</div>
@endsection
