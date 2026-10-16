// In-browser FreelancePro demo: same pages and rules as the Laravel app, state kept in localStorage.
import { seedState } from './data.js';
import { CATEGORIES, PROJECT_STATUS, PROPOSAL_STATUS, ROLE_LABEL, accept, apply, complete, createProject, deleteProject, deleteUser, login, register, reject } from './rules.js';

const KEY = 'freelancepro-demo-v1';
const loadStore = () => { try { return JSON.parse(localStorage.getItem(KEY)) || null; } catch { return null; } };
const saveStore = () => { try { localStorage.setItem(KEY, JSON.stringify({ state, session })); } catch { /* storage unavailable */ } };
const stored = loadStore();
let state = stored?.state || seedState();
let session = stored?.session || null; // user id
let flash = '';
let flashKind = 'flash';

const app = document.getElementById('app');
const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const money = (n) => `${Number(n).toLocaleString('fr-FR').replace(/ | /g, ' ')} TND`;
const user = () => state.users.find((u) => u.id === session) || null;
const userOf = (id) => state.users.find((u) => u.id === id);
const projectOf = (id) => state.projects.find((p) => p.id === id);
const date = (iso) => new Date(`${iso}T12:00:00`).toLocaleDateString('fr-FR');
const go = (hash) => { location.hash = hash; };
const notify = (msg, kind = 'flash') => { flash = msg; flashKind = kind; };

const proposalsCount = (id) => state.proposals.filter((p) => p.projectId === id).length;
const clientOf = (p) => userOf(p.clientId);

function card(p) {
  return `<article class="card"><div class="row"><span class="badge">${CATEGORIES[p.category]}</span><span class="badge ${p.status}">${PROJECT_STATUS[p.status]}</span></div>
    <h3 style="margin-top:12px"><a href="#/mission/${p.id}" style="color:inherit">${esc(p.title)}</a></h3>
    <p class="meta">Par ${esc(clientOf(p)?.name)} · ${proposalsCount(p.id)} candidature(s)</p>
    <p>${esc(p.description.length > 110 ? p.description.slice(0, 107) + '...' : p.description)}</p>
    <div class="row"><span class="price">${money(p.budget)}</span><a href="#/mission/${p.id}">Voir la mission →</a></div></article>`;
}

function layout(inner) {
  const u = user();
  const links = u
    ? `<a href="#/dashboard">Tableau de bord</a>${u.role === 'client' ? '<a class="btn small" href="#/mission/new">Publier une mission</a>' : ''}
       <span class="nav-user">${esc(u.name)} <span class="badge">${ROLE_LABEL[u.role]}</span></span><button class="btn ghost small" data-action="logout">Déconnexion</button>`
    : '<a href="#/login">Connexion</a><a class="btn small" href="#/register">S\'inscrire</a>';
  const msg = flash ? `<div class="${flashKind}">${esc(flash)}</div>` : '';
  flash = '';
  return `<div class="demo-bar">Mode démo : les données restent dans votre navigateur. <button type="button" data-action="reset">Réinitialiser</button></div>
  <header class="nav"><div class="container"><a class="brand" href="#/">Freelance<span>Pro</span></a>
    <nav class="nav-links"><a class="opt" href="#/missions">Missions</a>${links}</nav></div></header>
  <main class="container">${msg}${inner}</main>
  <footer><div class="container">FreelancePro, plateforme de mise en relation entre freelances et clients. Projet académique.</div></footer>`;
}

const pages = {
  home() {
    const latest = state.projects.filter((p) => p.status === 'open').slice(-6).reverse();
    return `<section class="hero"><h1>Trouvez le bon freelance pour votre prochain projet.</h1>
      <p>Les clients publient leurs missions, les freelances proposent leurs services, l'administrateur veille à la qualité de la plateforme.</p>
      <div style="display:flex;gap:10px;flex-wrap:wrap"><a class="btn" href="#/missions">Voir les missions</a>${user() ? '' : '<a class="btn ghost" href="#/register">Créer un compte</a>'}</div>
      <div class="stats"><div class="stat"><b>${state.projects.length}</b><span>missions publiées</span></div>
      <div class="stat"><b>${state.users.filter((u) => u.role === 'freelance').length}</b><span>freelances inscrits</span></div>
      <div class="stat"><b>${state.proposals.length}</b><span>candidatures envoyées</span></div></div></section>
      <div class="section-title"><h2>Dernières missions ouvertes</h2><a href="#/missions">Tout voir →</a></div>
      <div class="grid">${latest.map(card).join('')}</div>`;
  },

  missions(params) {
    const q = (params.get('q') || '').toLowerCase();
    const category = params.get('category') || '';
    const status = params.get('status') || 'open';
    const page = Number(params.get('page') || 1);
    let list = state.projects.filter((p) => (!q || p.title.toLowerCase().includes(q) || p.description.toLowerCase().includes(q)) && (!category || p.category === category) && (status === 'all' || p.status === 'open'));
    list = list.slice().reverse();
    const pages = Math.max(1, Math.ceil(list.length / 9));
    const shown = list.slice((page - 1) * 9, page * 9);
    return `<div class="section-title"><h2>Missions</h2></div>
      <form class="filters" id="filters"><input name="q" value="${esc(params.get('q') || '')}" placeholder="Rechercher une mission" aria-label="Rechercher">
        <select name="category" aria-label="Catégorie"><option value="">Toutes les catégories</option>${Object.entries(CATEGORIES).map(([k, v]) => `<option value="${k}" ${category === k ? 'selected' : ''}>${v}</option>`).join('')}</select>
        <select name="status" aria-label="Statut"><option value="open" ${status !== 'all' ? 'selected' : ''}>Missions ouvertes</option><option value="all" ${status === 'all' ? 'selected' : ''}>Toutes les missions</option></select>
        <button class="btn" type="submit">Filtrer</button></form>
      <div class="grid">${shown.length ? shown.map(card).join('') : '<p>Aucune mission ne correspond à votre recherche.</p>'}</div>
      <div class="pager">${Array.from({ length: pages }, (_, i) => (i + 1 === page ? `<span class="on">${i + 1}</span>` : `<a href="#/missions?${new URLSearchParams({ q: params.get('q') || '', category, status, page: i + 1 })}">${i + 1}</a>`)).join('')}</div>`;
  },

  mission(id) {
    const p = projectOf(id);
    if (!p) return '<p>Mission introuvable.</p>';
    const u = user();
    const isOwner = u && u.id === p.clientId;
    const list = isOwner || u?.role === 'admin' ? state.proposals.filter((x) => x.projectId === id).slice().reverse() : [];
    const mine = u?.role === 'freelance' ? state.proposals.find((x) => x.projectId === id && x.freelancerId === u.id) : null;
    const proposals = list.map((x) => {
      const f = userOf(x.freelancerId);
      return `<div class="proposal"><div class="row"><strong>${esc(f?.name)}</strong><span class="badge ${x.status}">${PROPOSAL_STATUS[x.status]}</span></div>
        <p class="meta">${esc(f?.title)} · ${money(x.price)} proposés</p><p style="margin:0 0 10px">${esc(x.message)}</p>
        ${isOwner && p.status === 'open' && x.status === 'pending' ? `<div style="display:flex;gap:8px"><button class="btn small good" data-action="accept" data-id="${x.id}">Accepter</button><button class="btn small ghost" data-action="reject" data-id="${x.id}">Refuser</button></div>` : ''}</div>`;
    }).join('');
    let aside;
    if (u?.role === 'freelance') {
      aside = `<div class="card"><h3>Postuler</h3>${mine
        ? `<p>Votre candidature : <span class="badge ${mine.status}">${PROPOSAL_STATUS[mine.status]}</span></p><p class="meta">${money(mine.price)} proposés</p>`
        : p.status !== 'open' ? '<p class="meta">Cette mission n\'accepte plus de candidatures.</p>'
        : `<form id="applyForm"><label for="price">Votre prix (TND)</label><input id="price" type="number" min="1" value="${p.budget}" required>
            <label for="message">Votre message</label><textarea id="message" rows="5" required></textarea><p class="hint">Présentez votre approche en quelques lignes.</p>
            <button class="btn" type="submit" style="width:100%;margin-top:14px">Envoyer ma candidature</button></form>`}</div>`;
    } else if (!u) {
      aside = '<div class="card"><h3>Postuler</h3><p class="meta">Connectez-vous avec un compte freelance pour envoyer une candidature.</p><a class="btn" href="#/login">Connexion</a></div>';
    } else aside = '';
    return `<div class="layout"><div><div class="card"><div class="row"><span class="badge">${CATEGORIES[p.category]}</span><span class="badge ${p.status}">${PROJECT_STATUS[p.status]}</span></div>
      <h1 style="margin-top:14px;font-size:1.7rem">${esc(p.title)}</h1>
      <p class="meta">Publiée par ${esc(clientOf(p)?.name)} le ${date(p.createdAt)}${p.deadline ? ` · Échéance ${date(p.deadline)}` : ''}</p>
      <p style="white-space:pre-line">${esc(p.description)}</p><p class="price">Budget : ${money(p.budget)}</p>
      ${isOwner && p.status === 'in_progress' ? '<button class="btn good" data-action="complete">Marquer comme terminée</button>' : ''}
      ${isOwner || u?.role === 'admin' ? '<div style="margin-top:10px"><button class="btn ghost small" data-action="delete">Supprimer la mission</button></div>' : ''}</div>
      ${isOwner || u?.role === 'admin' ? `<div class="section-title"><h2>Candidatures (${list.length})</h2></div>${proposals || '<p>Aucune candidature pour le moment.</p>'}` : ''}</div>
      <aside>${aside}</aside></div>`;
  },

  create() {
    return `<div class="form" style="max-width:640px"><div class="card"><h2>Publier une mission</h2><form id="createForm">
      <label for="title">Titre</label><input id="title" required>
      <label for="category">Catégorie</label><select id="category">${Object.entries(CATEGORIES).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}</select>
      <label for="description">Description</label><textarea id="description" rows="6" required></textarea>
      <div class="two"><div><label for="budget">Budget (TND)</label><input id="budget" type="number" min="50" required></div><div><label for="deadline">Échéance</label><input id="deadline" type="date"></div></div>
      <button class="btn" type="submit">Publier la mission</button></form></div></div>`;
  },

  login() {
    const quick = [['admin@freelancepro.test', 'Administrateur'], ['salma@freelancepro.test', 'Client (Salma)'], ['yassine@freelancepro.test', 'Freelance (Yassine)']];
    return `<div class="form"><div class="card"><h2>Connexion</h2><form id="loginForm"><label for="email">E-mail</label><input id="email" type="email" required>
      <label for="password">Mot de passe</label><input id="password" type="password" required><button class="btn" type="submit">Se connecter</button></form>
      <p class="hint" style="margin-top:16px">Pas encore de compte ? <a href="#/register">S'inscrire</a></p>
      <hr style="border:0;border-top:1px solid var(--line);margin:18px 0"><p class="hint"><strong>Démo :</strong> connectez-vous en un clic avec un compte de test.</p>
      <div style="display:flex;gap:8px;flex-wrap:wrap">${quick.map(([e, l]) => `<button class="btn ghost small" data-action="quick" data-email="${e}">${l}</button>`).join('')}</div></div></div>`;
  },

  register() {
    return `<div class="form"><div class="card"><h2>Créer un compte</h2><form id="registerForm">
      <label for="role">Je suis</label><select id="role"><option value="client">Client : je publie des missions</option><option value="freelance">Freelance : je propose mes services</option></select>
      <label for="name">Nom</label><input id="name" required><label for="email">E-mail</label><input id="email" type="email" required>
      <div class="two"><div><label for="password">Mot de passe</label><input id="password" type="password" required></div><div><label for="password2">Confirmation</label><input id="password2" type="password" required></div></div>
      <p class="hint">8 caractères minimum.</p><button class="btn" type="submit">Créer mon compte</button></form></div></div>`;
  },

  dashboard() {
    const u = user();
    if (!u) { go('#/login'); return ''; }
    if (u.role === 'client') {
      const mine = state.projects.filter((p) => p.clientId === u.id).slice().reverse();
      return `<div class="section-title"><h2>Mes missions</h2><a class="btn" href="#/mission/new">Publier une mission</a></div>
        <div class="stats"><div class="stat"><b>${mine.length}</b><span>missions</span></div><div class="stat"><b>${mine.filter((p) => p.status === 'open').length}</b><span>ouvertes</span></div>
        <div class="stat"><b>${mine.reduce((s, p) => s + proposalsCount(p.id), 0)}</b><span>candidatures reçues</span></div></div>
        <table class="table" style="margin-top:20px"><thead><tr><th>Mission</th><th>Budget</th><th>Candidatures</th><th>Statut</th></tr></thead><tbody>
        ${mine.length ? mine.map((p) => `<tr><td><a href="#/mission/${p.id}">${esc(p.title)}</a></td><td>${money(p.budget)}</td><td>${proposalsCount(p.id)}</td><td><span class="badge ${p.status}">${PROJECT_STATUS[p.status]}</span></td></tr>`).join('') : '<tr><td colspan="4">Vous n\'avez pas encore publié de mission.</td></tr>'}</tbody></table>`;
    }
    if (u.role === 'freelance') {
      const mine = state.proposals.filter((p) => p.freelancerId === u.id).slice().reverse();
      const more = state.projects.filter((p) => p.status === 'open').slice(-4).reverse();
      return `<div class="section-title"><h2>Mes candidatures</h2><a class="btn" href="#/missions">Trouver une mission</a></div>
        <div class="card" style="margin-bottom:20px"><strong>${esc(u.name)}</strong><p class="meta" style="margin:0">${esc(u.title || 'Freelance')}${u.rate ? ` · ${u.rate} TND/h` : ''}</p>
        ${u.skills ? `<div class="skills" style="margin-top:10px">${u.skills.split(',').map((s) => `<span class="badge">${esc(s.trim())}</span>`).join('')}</div>` : ''}</div>
        <table class="table"><thead><tr><th>Mission</th><th>Client</th><th>Mon prix</th><th>Statut</th></tr></thead><tbody>
        ${mine.length ? mine.map((x) => { const p = projectOf(x.projectId); return `<tr><td><a href="#/mission/${p.id}">${esc(p.title)}</a></td><td>${esc(clientOf(p)?.name)}</td><td>${money(x.price)}</td><td><span class="badge ${x.status}">${PROPOSAL_STATUS[x.status]}</span></td></tr>`; }).join('') : '<tr><td colspan="4">Aucune candidature pour le moment.</td></tr>'}</tbody></table>
        <div class="section-title"><h2>Missions à découvrir</h2></div><div class="grid">${more.map(card).join('')}</div>`;
    }
    const projects = state.projects.slice().reverse().slice(0, 10);
    return `<div class="section-title"><h2>Administration</h2></div>
      <div class="stats"><div class="stat"><b>${state.users.length}</b><span>utilisateurs</span></div><div class="stat"><b>${state.projects.length}</b><span>missions</span></div>
      <div class="stat"><b>${state.projects.filter((p) => p.status === 'open').length}</b><span>missions ouvertes</span></div><div class="stat"><b>${state.proposals.length}</b><span>candidatures</span></div></div>
      <div class="section-title"><h2>Utilisateurs</h2></div>
      <table class="table"><thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th></th></tr></thead><tbody>
      ${state.users.slice(0, 12).map((x) => `<tr><td>${esc(x.name)}</td><td>${esc(x.email)}</td><td><span class="badge">${ROLE_LABEL[x.role]}</span></td><td style="text-align:right">${x.role === 'admin' ? '' : `<button class="btn ghost small" data-action="deleteUser" data-id="${x.id}">Supprimer</button>`}</td></tr>`).join('')}</tbody></table>
      <div class="section-title"><h2>Missions récentes</h2></div>
      <table class="table"><thead><tr><th>Mission</th><th>Client</th><th>Candidatures</th><th>Statut</th></tr></thead><tbody>
      ${projects.map((p) => `<tr><td><a href="#/mission/${p.id}">${esc(p.title)}</a></td><td>${esc(clientOf(p)?.name)}</td><td>${proposalsCount(p.id)}</td><td><span class="badge ${p.status}">${PROJECT_STATUS[p.status]}</span></td></tr>`).join('')}</tbody></table>`;
  },
};

let currentProject = null;

function render() {
  const [path, query = ''] = location.hash.slice(1).split('?');
  const parts = path.split('/').filter(Boolean);
  const params = new URLSearchParams(query);
  let html;
  currentProject = null;
  if (!parts.length) html = pages.home();
  else if (parts[0] === 'missions') html = pages.missions(params);
  else if (parts[0] === 'mission' && parts[1] === 'new') html = user()?.role === 'client' ? pages.create() : (notify('Accès réservé aux clients.', 'errors'), pages.home());
  else if (parts[0] === 'mission') { currentProject = Number(parts[1]); html = pages.mission(currentProject); }
  else if (parts[0] === 'login') html = pages.login();
  else if (parts[0] === 'register') html = pages.register();
  else if (parts[0] === 'dashboard') html = pages.dashboard();
  else html = pages.home();
  app.innerHTML = layout(html);
  window.scrollTo(0, 0);
  bind();
}

function run(fn, success, then) {
  try { fn(); saveStore(); if (success) notify(success); then ? then() : render(); }
  catch (err) { notify(err.message, 'errors'); render(); }
}

function bind() {
  const f = (id) => document.getElementById(id);
  f('filters')?.addEventListener('submit', (e) => { e.preventDefault(); go(`#/missions?${new URLSearchParams(Object.fromEntries(new FormData(e.target)))}`); });
  f('loginForm')?.addEventListener('submit', (e) => { e.preventDefault(); run(() => { session = login(state, f('email').value, f('password').value).id; }, null, () => go('#/dashboard')); });
  f('registerForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    if (f('password').value !== f('password2').value) { notify('La confirmation du mot de passe ne correspond pas.', 'errors'); render(); return; }
    run(() => { session = register(state, { name: f('name').value, email: f('email').value, password: f('password').value, role: f('role').value }).id; }, 'Bienvenue sur FreelancePro !', () => go('#/dashboard'));
  });
  f('createForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    let created;
    run(() => { created = createProject(state, user(), { title: f('title').value.trim(), category: f('category').value, description: f('description').value.trim(), budget: Number(f('budget').value), deadline: f('deadline').value || null }); }, 'Votre mission est publiée.', () => go(`#/mission/${created.id}`));
  });
  f('applyForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    run(() => apply(state, user(), currentProject, { message: f('message').value.trim(), price: Number(f('price').value) }), 'Votre candidature a été envoyée.');
  });
  app.querySelectorAll('[data-action]').forEach((el) => el.addEventListener('click', () => {
    const id = Number(el.dataset.id);
    switch (el.dataset.action) {
      case 'logout': session = null; saveStore(); go('#/'); render(); break;
      case 'reset': state = seedState(); session = null; saveStore(); go('#/'); render(); break;
      case 'quick': run(() => { session = login(state, el.dataset.email, 'password').id; }, null, () => go('#/dashboard')); break;
      case 'accept': run(() => accept(state, user(), id), 'Candidature acceptée, la mission est en cours.'); break;
      case 'reject': run(() => reject(state, user(), id), 'Candidature refusée.'); break;
      case 'complete': run(() => complete(state, user(), currentProject), 'Mission marquée comme terminée.'); break;
      case 'delete': run(() => deleteProject(state, user(), currentProject), 'Mission supprimée.', () => go('#/dashboard')); break;
      case 'deleteUser': run(() => deleteUser(state, user(), id), 'Compte supprimé.'); break;
      default: break;
    }
  }));
}

window.addEventListener('hashchange', render);
render();
