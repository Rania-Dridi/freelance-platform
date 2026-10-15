@extends('layouts.app')
@section('title', 'Accueil')
@section('content')
<section class="hero">
  <h1>Trouvez le bon freelance pour votre prochain projet.</h1>
  <p>Les clients publient leurs missions, les freelances proposent leurs services, l'administrateur veille à la qualité de la plateforme.</p>
  <div class="btns" style="display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn" href="{{ route('projects.index') }}">Voir les missions</a>
    @guest<a class="btn ghost" href="{{ route('register') }}">Créer un compte</a>@endguest
  </div>
  <div class="stats">
    <div class="stat"><b>{{ $stats['projects'] }}</b><span>missions publiées</span></div>
    <div class="stat"><b>{{ $stats['freelancers'] }}</b><span>freelances inscrits</span></div>
    <div class="stat"><b>{{ $stats['proposals'] }}</b><span>candidatures envoyées</span></div>
  </div>
</section>

<div class="section-title"><h2>Dernières missions ouvertes</h2><a href="{{ route('projects.index') }}">Tout voir →</a></div>
<div class="grid">
  @foreach($latest as $project)@include('partials.project-card', ['project' => $project])@endforeach
</div>
@endsection
