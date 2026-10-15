<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'latest' => Project::with('client')->where('status', 'open')->latest()->take(6)->get(),
            'stats' => [
                'projects' => Project::count(),
                'freelancers' => \App\Models\User::where('role', 'freelance')->count(),
                'proposals' => \App\Models\Proposal::count(),
            ],
        ]);
    }

    public function index(Request $request): View
    {
        $projects = Project::with('client')
            ->withCount('proposals')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $term = '%'.$request->string('q').'%';
                $w->where('title', 'like', $term)->orWhere('description', 'like', $term);
            }))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->string('status')->toString() !== 'all', fn ($q) => $q->where('status', 'open'))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('projects.index', ['projects' => $projects]);
    }

    public function show(Request $request, Project $project): View
    {
        $project->load('client');
        $user = $request->user();
        $isOwner = $user && $user->id === $project->client_id;

        return view('projects.show', [
            'project' => $project,
            'isOwner' => $isOwner,
            'proposals' => $isOwner || ($user && $user->isAdmin())
                ? $project->proposals()->with('freelancer')->latest()->get()
                : collect(),
            'myProposal' => $user && $user->isFreelance()
                ? $project->proposals()->where('freelancer_id', $user->id)->first()
                : null,
        ]);
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['required', 'string', 'min:30'],
            'category' => ['required', Rule::in(array_keys(Project::CATEGORIES))],
            'budget' => ['required', 'integer', 'min:50'],
            'deadline' => ['nullable', 'date', 'after:today'],
        ]);

        $project = $request->user()->projects()->create($data);

        return redirect()->route('projects.show', $project)->with('status', 'Votre mission est publiée.');
    }

    public function complete(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->client_id === $request->user()->id, 403);
        abort_unless($project->status === 'in_progress', 422, 'La mission n\'est pas en cours.');

        $project->update(['status' => 'completed']);

        return back()->with('status', 'Mission marquée comme terminée.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $project->client_id === $user->id, 403);

        $project->delete();

        return redirect()->route('dashboard')->with('status', 'Mission supprimée.');
    }
}
