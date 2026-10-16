// Demo data, same content as database/seeders/DatabaseSeeder.php. Every demo account uses the password "password".
const iso = (offsetDays) => new Date(Date.now() + offsetDays * 86_400_000).toISOString().slice(0, 10);

export function seedState() {
  const users = [
    { id: 1, name: 'Administrateur', email: 'admin@freelancepro.test', role: 'admin' },
    { id: 2, name: 'Salma Gharbi', email: 'salma@freelancepro.test', role: 'client' },
    { id: 3, name: 'Karim Ben Salah', email: 'karim@freelancepro.test', role: 'client' },
    { id: 4, name: 'Agence Médina Digitale', email: 'medina@freelancepro.test', role: 'client' },
    { id: 5, name: 'Yassine Trabelsi', email: 'yassine@freelancepro.test', role: 'freelance', title: 'Développeur full-stack', skills: 'Laravel, Vue.js, MySQL, API REST', rate: 45 },
    { id: 6, name: 'Imen Chaabane', email: 'imen@freelancepro.test', role: 'freelance', title: 'Designer UI/UX', skills: 'Figma, Design system, Prototypage', rate: 38 },
    { id: 7, name: 'Walid Mejri', email: 'walid@freelancepro.test', role: 'freelance', title: 'Développeur mobile', skills: 'Flutter, Android, Firebase', rate: 42 },
    { id: 8, name: 'Ons Hamdi', email: 'ons@freelancepro.test', role: 'freelance', title: 'Analyste de données', skills: 'Python, SQL, Power BI', rate: 40 },
  ].map((u) => ({ ...u, password: 'password' }));

  const clientIds = [2, 3, 4];
  const missions = [
    [0, "Boutique en ligne pour une marque d'huile d'olive", 'web', 2800, "Création d'une boutique avec catalogue, panier, paiement en ligne et espace client. Livraison en Tunisie et à l'export."],
    [0, "Refonte du site vitrine d'un cabinet dentaire", 'web', 1200, 'Site de quatre pages, rapide, adapté au mobile, avec prise de rendez-vous en ligne et référencement local.'],
    [1, 'Application mobile de suivi de livraisons', 'mobile', 4500, 'Application pour les livreurs : liste de tournées, preuve de livraison par photo et statut en temps réel.'],
    [1, "Identité visuelle et maquettes pour une application de fitness", 'design', 1600, 'Logo, charte graphique et maquettes de douze écrans, avec un petit design system réutilisable.'],
    [2, 'Tableau de bord des ventes pour une chaîne de magasins', 'data', 2100, 'Connexion aux exports de caisse, nettoyage des données et tableau de bord mensuel par magasin et par produit.'],
    [2, 'Campagne de lancement sur les réseaux sociaux', 'marketing', 900, 'Plan éditorial de quatre semaines, création des visuels et suivi des résultats pour un lancement de produit.'],
    [0, "Traduction français-anglais d'un catalogue produits", 'redaction', 600, 'Traduction de 120 fiches produits avec relecture et glossaire des termes techniques.'],
    [1, 'API de réservation pour un réseau de salles de sport', 'web', 3200, 'API REST avec authentification, gestion des créneaux et notifications, documentée et testée.'],
    [2, 'Application web de gestion de stock', 'web', 3600, 'Suivi des entrées et sorties, alertes de seuil, rôles utilisateurs et export des inventaires.'],
  ];
  const projects = missions.map(([c, title, category, budget, description], i) => ({
    id: i + 1, clientId: clientIds[c], title, category, budget, description, deadline: iso(21 + i * 4), status: 'open', createdAt: iso(-i),
  }));

  const pitch = [
    'Bonjour, ce type de mission correspond à mon expérience. Je propose une première maquette sous trois jours puis des livraisons hebdomadaires.',
    'Je peux démarrer la semaine prochaine. Mon plan : cadrage rapide, développement par étapes et recette avec vous avant la mise en ligne.',
    'Votre brief est clair. Je livre un travail documenté et je reste disponible un mois après la livraison pour les corrections.',
  ];
  const freelancerIds = [5, 6, 7, 8];
  const plan = [[0, 0, 0.92], [0, 1, 1.0], [2, 2, 0.95], [3, 1, 0.9], [4, 3, 0.97], [7, 0, 0.88], [7, 2, 1.0], [8, 0, 0.93], [8, 2, 1.0], [1, 0, 0.85]];
  const proposals = plan.map(([p, f, ratio], i) => ({
    id: i + 1, projectId: projects[p].id, freelancerId: freelancerIds[f], message: pitch[i % 3], price: Math.round(projects[p].budget * ratio), status: 'pending',
  }));

  proposals.find((p) => p.projectId === projects[4].id).status = 'accepted';
  projects[4].status = 'in_progress';
  projects[6].status = 'completed';
  return { users, projects, proposals, today: iso(0) };
}
