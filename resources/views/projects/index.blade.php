@extends('layouts.app')
@section('title', 'Missions')
@section('content')
<div class="section-title"><h2>Missions</h2></div>
<form class="filters" method="GET" action="{{ route('projects.index') }}">
  <input name="q" value="{{ request('q') }}" placeholder="Rechercher une mission" aria-label="Rechercher">
  <select name="category" aria-label="Catégorie">
    <option value="">Toutes les catégories</option>
    @foreach(\App\Models\Project::CATEGORIES as $key => $label)
      <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
    @endforeach
  </select>
  <select name="status" aria-label="Statut">
    <option value="open" @selected(request('status') !== 'all')>Missions ouvertes</option>
    <option value="all" @selected(request('status') === 'all')>Toutes les missions</option>
  </select>
  <button class="btn" type="submit">Filtrer</button>
</form>

<div class="grid">
  @forelse($projects as $project)@include('partials.project-card', ['project' => $project])@empty
    <p>Aucune mission ne correspond à votre recherche.</p>
  @endforelse
</div>
<div class="pager">
  @foreach($projects->getUrlRange(1, $projects->lastPage()) as $page => $url)
    @if($page == $projects->currentPage())<span class="on">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
  @endforeach
</div>
@endsection
