@extends('layouts.app')
@section('title', 'Administration')
@section('content')
<div class="section-title"><h2>Administration</h2></div>
<div class="stats">
  <div class="stat"><b>{{ $stats['users'] }}</b><span>utilisateurs</span></div>
  <div class="stat"><b>{{ $stats['projects'] }}</b><span>missions</span></div>
  <div class="stat"><b>{{ $stats['open'] }}</b><span>missions ouvertes</span></div>
  <div class="stat"><b>{{ $stats['proposals'] }}</b><span>candidatures</span></div>
</div>

<div class="section-title"><h2>Utilisateurs</h2></div>
<table class="table">
  <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th></th></tr></thead>
  <tbody>
  @foreach($users as $u)
    <tr>
      <td>{{ $u->name }}</td><td>{{ $u->email }}</td><td><span class="badge">{{ $u->roleLabel() }}</span></td>
      <td style="text-align:right">
        @unless($u->isAdmin())
          <form class="inline" method="POST" action="{{ route('admin.users.destroy', $u) }}">@csrf @method('DELETE')<button class="btn ghost small" type="submit">Supprimer</button></form>
        @endunless
      </td>
    </tr>
  @endforeach
  </tbody>
</table>

<div class="section-title"><h2>Missions récentes</h2></div>
<table class="table">
  <thead><tr><th>Mission</th><th>Client</th><th>Candidatures</th><th>Statut</th></tr></thead>
  <tbody>
  @foreach($projects as $project)
    <tr>
      <td><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></td>
      <td>{{ $project->client->name }}</td><td>{{ $project->proposals_count }}</td>
      <td><span class="badge {{ $project->status }}">{{ $project->statusLabel() }}</span></td>
    </tr>
  @endforeach
  </tbody>
</table>
@endsection
