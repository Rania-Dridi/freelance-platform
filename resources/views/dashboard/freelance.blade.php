@extends('layouts.app')
@section('title', 'Tableau de bord freelance')
@section('content')
<div class="section-title"><h2>Mes candidatures</h2><a class="btn" href="{{ route('projects.index') }}">Trouver une mission</a></div>
@php $me = auth()->user(); @endphp
<div class="card" style="margin-bottom:20px">
  <div class="row"><div><strong>{{ $me->name }}</strong><p class="meta" style="margin:0">{{ $me->title ?? 'Freelance' }}@if($me->hourly_rate) · {{ $me->hourly_rate }} TND/h @endif</p></div></div>
  @if($me->skills)<div class="skills" style="margin-top:10px">@foreach($me->skillList() as $skill)<span class="badge">{{ $skill }}</span>@endforeach</div>@endif
</div>
<table class="table">
  <thead><tr><th>Mission</th><th>Client</th><th>Mon prix</th><th>Statut</th></tr></thead>
  <tbody>
  @forelse($proposals as $p)
    <tr>
      <td><a href="{{ route('projects.show', $p->project) }}">{{ $p->project->title }}</a></td>
      <td>{{ $p->project->client->name }}</td>
      <td>{{ number_format($p->price, 0, ',', ' ') }} TND</td>
      <td><span class="badge {{ $p->status }}">{{ $p->statusLabel() }}</span></td>
    </tr>
  @empty
    <tr><td colspan="4">Aucune candidature pour le moment.</td></tr>
  @endforelse
  </tbody>
</table>

<div class="section-title"><h2>Missions à découvrir</h2></div>
<div class="grid">@foreach($matching as $project)@include('partials.project-card', ['project' => $project->load('client')])@endforeach</div>
@endsection
