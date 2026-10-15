@extends('layouts.app')
@section('title', 'Connexion')
@section('content')
<div class="form">
  <div class="card">
    <h2>Connexion</h2>
    @if($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login') }}">
      @csrf
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
      <label for="password">Mot de passe</label>
      <input id="password" name="password" type="password" required>
      <button class="btn" type="submit">Se connecter</button>
    </form>
    <p class="hint" style="margin-top:16px">Pas encore de compte ? <a href="{{ route('register') }}">S'inscrire</a></p>
  </div>
</div>
@endsection
