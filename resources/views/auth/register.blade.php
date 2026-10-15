@extends('layouts.app')
@section('title', 'Inscription')
@section('content')
<div class="form">
  <div class="card">
    <h2>Créer un compte</h2>
    @if($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('register') }}">
      @csrf
      <label for="role">Je suis</label>
      <select id="role" name="role">
        <option value="client" @selected(old('role') === 'client')>Client : je publie des missions</option>
        <option value="freelance" @selected(old('role') === 'freelance')>Freelance : je propose mes services</option>
      </select>
      <label for="name">Nom</label>
      <input id="name" name="name" value="{{ old('name') }}" required>
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" value="{{ old('email') }}" required>
      <div class="two">
        <div><label for="password">Mot de passe</label><input id="password" name="password" type="password" required></div>
        <div><label for="password_confirmation">Confirmation</label><input id="password_confirmation" name="password_confirmation" type="password" required></div>
      </div>
      <p class="hint">8 caractères minimum.</p>
      <button class="btn" type="submit">Créer mon compte</button>
    </form>
  </div>
</div>
@endsection
