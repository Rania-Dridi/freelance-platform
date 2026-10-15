<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProjectController::class, 'home'])->name('home');
Route::get('/missions', [ProjectController::class, 'index'])->name('projects.index');

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');
    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('dashboard');

    // Client: publish and manage missions, review proposals.
    Route::middleware('role:client')->group(function () {
        Route::get('/missions/nouvelle', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/missions', [ProjectController::class, 'store'])->name('projects.store');
        Route::post('/missions/{project}/terminer', [ProjectController::class, 'complete'])->name('projects.complete');
        Route::post('/candidatures/{proposal}/accepter', [ProposalController::class, 'accept'])->name('proposals.accept');
        Route::post('/candidatures/{proposal}/refuser', [ProposalController::class, 'reject'])->name('proposals.reject');
    });

    // Freelance: apply to missions.
    Route::post('/missions/{project}/candidater', [ProposalController::class, 'store'])
        ->middleware('role:freelance')->name('proposals.store');

    // Client (own missions) or admin (any mission).
    Route::delete('/missions/{project}', [ProjectController::class, 'destroy'])
        ->middleware('role:client,admin')->name('projects.destroy');

    // Admin only.
    Route::delete('/admin/utilisateurs/{user}', [DashboardController::class, 'destroyUser'])
        ->middleware('role:admin')->name('admin.users.destroy');
});

// Registered last so '/missions/nouvelle' is not captured by the {project} binding.
Route::get('/missions/{project}', [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
