<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'FreelancePro') — FreelancePro</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="nav">
  <div class="container">
    <a class="brand" href="{{ route('home') }}">Freelance<span>Pro</span></a>
    <nav class="nav-links">
      <a class="opt" href="{{ route('projects.index') }}">Missions</a>
      @auth
        <a href="{{ route('dashboard') }}">Tableau de bord</a>
        @if(auth()->user()->isClient())
          <a class="btn small" href="{{ route('projects.create') }}">Publier une mission</a>
        @endif
        <span class="nav-user">{{ auth()->user()->name }} <span class="badge">{{ auth()->user()->roleLabel() }}</span></span>
        <form class="inline" method="POST" action="{{ route('logout') }}">@csrf<button class="btn ghost small" type="submit">Déconnexion</button></form>
      @else
        <a href="{{ route('login') }}">Connexion</a>
        <a class="btn small" href="{{ route('register') }}">S'inscrire</a>
      @endauth
    </nav>
  </div>
</header>

<main class="container">
  @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
  @yield('content')
</main>

<footer><div class="container">FreelancePro, plateforme de mise en relation entre freelances et clients. Projet académique, Esprit School of Business.</div></footer>
</body>
</html>
