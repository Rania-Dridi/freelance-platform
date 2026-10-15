@extends('layouts.app')
@section('title', $project->title)
@section('content')
<div class="layout">
  <div>
    <div class="card">
      <div class="row"><span class="badge">{{ $project->categoryLabel() }}</span><span class="badge {{ $project->status }}">{{ $project->statusLabel() }}</span></div>
      <h1 style="margin-top:14px;font-size:1.7rem">{{ $project->title }}</h1>
      <p class="meta">Publiée par {{ $project->client->name }} le {{ $project->created_at->format('d/m/Y') }}@if($project->deadline) · Échéance {{ $project->deadline->format('d/m/Y') }}@endif</p>
      <p style="white-space:pre-line">{{ $project->description }}</p>
      <p class="price">Budget : {{ number_format($project->budget, 0, ',', ' ') }} TND</p>
      @if($isOwner && $project->status === 'in_progress')
        <form method="POST" action="{{ route('projects.complete', $project) }}">@csrf<button class="btn good" type="submit">Marquer comme terminée</button></form>
      @endif
      @if($isOwner || (auth()->check() && auth()->user()->isAdmin()))
        <form method="POST" action="{{ route('projects.destroy', $project) }}" style="margin-top:10px">@csrf @method('DELETE')<button class="btn ghost small" type="submit">Supprimer la mission</button></form>
      @endif
    </div>

    @if($isOwner || (auth()->check() && auth()->user()->isAdmin()))
      <div class="section-title"><h2>Candidatures ({{ $proposals->count() }})</h2></div>
      @forelse($proposals as $proposal)
        <div class="proposal">
          <div class="row"><strong>{{ $proposal->freelancer->name }}</strong><span class="badge {{ $proposal->status }}">{{ $proposal->statusLabel() }}</span></div>
          <p class="meta">{{ $proposal->freelancer->title }} · {{ number_format($proposal->price, 0, ',', ' ') }} TND proposés</p>
          <p style="margin:0 0 10px">{{ $proposal->message }}</p>
          @if($isOwner && $project->status === 'open' && $proposal->status === 'pending')
            <div style="display:flex;gap:8px">
              <form method="POST" action="{{ route('proposals.accept', $proposal) }}">@csrf<button class="btn small good" type="submit">Accepter</button></form>
              <form method="POST" action="{{ route('proposals.reject', $proposal) }}">@csrf<button class="btn small ghost" type="submit">Refuser</button></form>
            </div>
          @endif
        </div>
      @empty
        <p>Aucune candidature pour le moment.</p>
      @endforelse
    @endif
  </div>

  <aside>
    @auth
      @if(auth()->user()->isFreelance())
        <div class="card">
          <h3>Postuler</h3>
          @if($myProposal)
            <p>Votre candidature : <span class="badge {{ $myProposal->status }}">{{ $myProposal->statusLabel() }}</span></p>
            <p class="meta">{{ number_format($myProposal->price, 0, ',', ' ') }} TND proposés</p>
          @elseif($project->status !== 'open')
            <p class="meta">Cette mission n'accepte plus de candidatures.</p>
          @else
            @if($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('proposals.store', $project) }}">
              @csrf
              <label for="price">Votre prix (TND)</label>
              <input id="price" name="price" type="number" min="1" value="{{ old('price', $project->budget) }}" required>
              <label for="message">Votre message</label>
              <textarea id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
              <p class="hint">Présentez votre approche en quelques lignes.</p>
              <button class="btn" type="submit" style="width:100%;margin-top:14px">Envoyer ma candidature</button>
            </form>
          @endif
        </div>
      @endif
    @else
      <div class="card"><h3>Postuler</h3><p class="meta">Connectez-vous avec un compte freelance pour envoyer une candidature.</p><a class="btn" href="{{ route('login') }}">Connexion</a></div>
    @endauth
  </aside>
</div>
@endsection
