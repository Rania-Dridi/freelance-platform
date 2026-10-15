<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Each role lands on its own dashboard. */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('dashboard.admin', [
                'stats' => [
                    'users' => User::count(),
                    'projects' => Project::count(),
                    'open' => Project::where('status', 'open')->count(),
                    'proposals' => Proposal::count(),
                ],
                'users' => User::latest()->take(12)->get(),
                'projects' => Project::with('client')->withCount('proposals')->latest()->take(10)->get(),
            ]);
        }

        if ($user->isClient()) {
            return view('dashboard.client', [
                'projects' => $user->projects()->withCount('proposals')->latest()->get(),
            ]);
        }

        return view('dashboard.freelance', [
            'proposals' => $user->proposals()->with('project.client')->latest()->get(),
            'matching' => Project::where('status', 'open')->latest()->take(4)->get(),
        ]);
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Un administrateur ne peut pas être supprimé.');

        $user->delete();

        return back()->with('status', 'Compte supprimé.');
    }
}
