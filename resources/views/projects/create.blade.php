@extends('layouts.app')
@section('title', 'Publier une mission')
@section('content')
<div class="form" style="max-width:640px">
  <div class="card">
    <h2>Publier une mission</h2>
    @if($errors->any())<div class="errors"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('projects.store') }}">
      @csrf
      <label for="title">Titre</label>
      <input id="title" name="title" value="{{ old('title') }}" required>
      <label for="category">Catégorie</label>
      <select id="category" name="category">
        @foreach(\App\Models\Project::CATEGORIES as $key => $label)<option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>@endforeach
      </select>
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="6" required>{{ old('description') }}</textarea>
      <div class="two">
        <div><label for="budget">Budget (TND)</label><input id="budget" name="budget" type="number" min="50" value="{{ old('budget') }}" required></div>
        <div><label for="deadline">Échéance</label><input id="deadline" name="deadline" type="date" value="{{ old('deadline') }}"></div>
      </div>
      <button class="btn" type="submit">Publier la mission</button>
    </form>
  </div>
</div>
@endsection
