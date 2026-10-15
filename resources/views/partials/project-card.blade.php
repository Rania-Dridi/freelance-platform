<article class="card">
  <div class="row"><span class="badge">{{ $project->categoryLabel() }}</span><span class="badge {{ $project->status }}">{{ $project->statusLabel() }}</span></div>
  <h3 style="margin-top:12px"><a href="{{ route('projects.show', $project) }}" style="color:inherit">{{ $project->title }}</a></h3>
  <p class="meta">Par {{ $project->client->name }}@isset($project->proposals_count) · {{ $project->proposals_count }} candidature(s)@endisset</p>
  <p>{{ \Illuminate\Support\Str::limit($project->description, 110) }}</p>
  <div class="row"><span class="price">{{ number_format($project->budget, 0, ',', ' ') }} TND</span><a href="{{ route('projects.show', $project) }}">Voir la mission →</a></div>
</article>
