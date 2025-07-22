@extends('assets.mainLogin')
@section('content')

<form action="/cekLogin" method="post" class="form">
    @csrf
    <h3>sistem pengajuan cuti</h3>
    <p>Username</p>
    <input type="text" name="username" placeholder="Masukkan Username" required>
    @error('username')
    <small>{{ $message }}</small>    
    @enderror
    <p>Password</p>
    <input type="password" name="password" placeholder="Masukkan Password" required>
    @error('password')
    <small>{{ $message }}</small>    
    @enderror
    <span></span>
    {{-- <a href="{{ route('password.request') }}">
    {{ __('Forgot Your Password?') }}
    </a> --}}

    <button type="submit" class="button-submit">Login</button>
</form>
    
@endsection