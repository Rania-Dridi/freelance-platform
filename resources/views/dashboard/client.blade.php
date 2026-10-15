@extends('layouts.app')
@section('title', 'Tableau de bord client')
@section('content')
<div class="section-title"><h2>Mes missions</h2><a class="btn" href="{{ route('projects.create') }}">Publier une mission</a></div>
<div class="stats">
  <div class="stat"><b>{{ $projects->count() }}</b><span>missions</span></div>
  <div class="stat"><b>{{ $projects->where('status', 'open')->count() }}</b><span>ouvertes</span></div>
  <div class="stat"><b>{{ $projects->sum('proposals_count') }}</b><span>candidatures reçues</span></div>
</div>
<table class="table" style="margin-top:20px">
  <thead><tr><th>Mission</th><th>Budget</th><th>Candidatures</th><th>Statut</th></tr></thead>
  <tbody>
  @forelse($projects as $project)
    <tr>
      <td><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></td>
      <td>{{ number_format($project->budget, 0, ',', ' ') }} TND</td>
      <td>{{ $project->proposals_count }}</td>
      <td><span class="badge {{ $project->status }}">{{ $project->statusLabel() }}</span></td>
    </tr>
  @empty
    <tr><td colspan="4">Vous n'avez pas encore publié de mission.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
