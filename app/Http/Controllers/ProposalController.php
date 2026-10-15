<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->status === 'open', 422, 'Cette mission n\'accepte plus de candidatures.');

        $data = $request->validate([
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'price' => ['required', 'integer', 'min:1'],
        ]);

        $already = $project->proposals()->where('freelancer_id', $request->user()->id)->exists();
        if ($already) {
            return back()->withErrors(['message' => 'Vous avez déjà postulé à cette mission.']);
        }

        $project->proposals()->create($data + ['freelancer_id' => $request->user()->id]);

        return back()->with('status', 'Votre candidature a été envoyée.');
    }

    public function accept(Request $request, Proposal $proposal): RedirectResponse
    {
        $project = $proposal->project;
        abort_unless($project->client_id === $request->user()->id, 403);
        abort_unless($project->status === 'open', 422, 'Cette mission n\'est plus ouverte.');

        DB::transaction(function () use ($proposal, $project) {
            $project->proposals()->where('id', '!=', $proposal->id)->update(['status' => 'rejected']);
            $proposal->update(['status' => 'accepted']);
            $project->update(['status' => 'in_progress']);
        });

        return back()->with('status', 'Candidature acceptée, la mission est en cours.');
    }

    public function reject(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->project->client_id === $request->user()->id, 403);

        $proposal->update(['status' => 'rejected']);

        return back()->with('status', 'Candidature refusée.');
    }
}
