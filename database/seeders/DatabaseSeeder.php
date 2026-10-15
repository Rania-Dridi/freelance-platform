<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data. Every seeded account uses the password "password" (development only).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create(['name' => 'Administrateur', 'email' => 'admin@freelancepro.test', 'password' => 'password', 'role' => 'admin']);

        $clients = collect([
            ['Salma Gharbi', 'salma@freelancepro.test'],
            ['Karim Ben Salah', 'karim@freelancepro.test'],
            ['Agence Médina Digitale', 'medina@freelancepro.test'],
        ])->map(fn ($c) => User::create(['name' => $c[0], 'email' => $c[1], 'password' => 'password', 'role' => 'client']));

        $freelancers = collect([
            ['Yassine Trabelsi', 'yassine@freelancepro.test', 'Développeur full-stack', 'Laravel, Vue.js, MySQL, API REST', 45, 'Sept ans d\'expérience sur des applications métiers et des boutiques en ligne.'],
            ['Imen Chaabane', 'imen@freelancepro.test', 'Designer UI/UX', 'Figma, Design system, Prototypage', 38, 'Interfaces claires et accessibles pour le web et le mobile.'],
            ['Walid Mejri', 'walid@freelancepro.test', 'Développeur mobile', 'Flutter, Android, Firebase', 42, 'Applications mobiles livrées sur les deux stores.'],
            ['Ons Hamdi', 'ons@freelancepro.test', 'Analyste de données', 'Python, SQL, Power BI', 40, 'Tableaux de bord et analyses pour les équipes commerciales.'],
        ])->map(fn ($f) => User::create([
            'name' => $f[0], 'email' => $f[1], 'password' => 'password', 'role' => 'freelance',
            'title' => $f[2], 'skills' => $f[3], 'hourly_rate' => $f[4], 'bio' => $f[5],
        ]));

        $missions = [
            [0, 'Boutique en ligne pour une marque d\'huile d\'olive', 'web', 2800, 'Création d\'une boutique avec catalogue, panier, paiement en ligne et espace client. Livraison en Tunisie et à l\'export.'],
            [0, 'Refonte du site vitrine d\'un cabinet dentaire', 'web', 1200, 'Site de quatre pages, rapide, adapté au mobile, avec prise de rendez-vous en ligne et référencement local.'],
            [1, 'Application mobile de suivi de livraisons', 'mobile', 4500, 'Application pour les livreurs : liste de tournées, preuve de livraison par photo et statut en temps réel.'],
            [1, 'Identité visuelle et maquettes pour une application de fitness', 'design', 1600, 'Logo, charte graphique et maquettes de douze écrans, avec un petit design system réutilisable.'],
            [2, 'Tableau de bord des ventes pour une chaîne de magasins', 'data', 2100, 'Connexion aux exports de caisse, nettoyage des données et tableau de bord mensuel par magasin et par produit.'],
            [2, 'Campagne de lancement sur les réseaux sociaux', 'marketing', 900, 'Plan éditorial de quatre semaines, création des visuels et suivi des résultats pour un lancement de produit.'],
            [0, 'Traduction français-anglais d\'un catalogue produits', 'redaction', 600, 'Traduction de 120 fiches produits avec relecture et glossaire des termes techniques.'],
            [1, 'API de réservation pour un réseau de salles de sport', 'web', 3200, 'API REST avec authentification, gestion des créneaux et notifications, documentée et testée.'],
            [2, 'Application web de gestion de stock', 'web', 3600, 'Suivi des entrées et sorties, alertes de seuil, rôles utilisateurs et export des inventaires.'],
        ];

        $projects = collect($missions)->map(fn ($m) => Project::create([
            'client_id' => $clients[$m[0]]->id, 'title' => $m[1], 'category' => $m[2], 'budget' => $m[3],
            'description' => $m[4], 'deadline' => now()->addWeeks(rand(3, 10)),
        ]));

        $pitch = [
            'Bonjour, ce type de mission correspond à mon expérience. Je propose une première maquette sous trois jours puis des livraisons hebdomadaires.',
            'Je peux démarrer la semaine prochaine. Mon plan : cadrage rapide, développement par étapes et recette avec vous avant la mise en ligne.',
            'Votre brief est clair. Je livre un travail documenté et je reste disponible un mois après la livraison pour les corrections.',
        ];

        $plan = [
            [0, 0, 0.92], [0, 1, 1.0], [2, 2, 0.95], [3, 1, 0.9], [4, 3, 0.97],
            [7, 0, 0.88], [7, 2, 1.0], [8, 0, 0.93], [8, 2, 1.0], [1, 0, 0.85],
        ];

        foreach ($plan as $i => [$pi, $fi, $ratio]) {
            Proposal::create([
                'project_id' => $projects[$pi]->id, 'freelancer_id' => $freelancers[$fi]->id,
                'message' => $pitch[$i % 3], 'price' => (int) round($projects[$pi]->budget * $ratio),
            ]);
        }

        // One mission already in progress, one completed.
        $accepted = Proposal::where('project_id', $projects[4]->id)->first();
        $accepted->update(['status' => 'accepted']);
        $projects[4]->update(['status' => 'in_progress']);
        $projects[6]->update(['status' => 'completed']);
    }
}
