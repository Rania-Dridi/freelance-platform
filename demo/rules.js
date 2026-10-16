// Browser port of the Laravel business rules (ProjectController, ProposalController, DashboardController, RoleMiddleware).
// The demo runs without PHP: same rules, same messages, state kept by the caller.

export const CATEGORIES = {
  web: 'Développement web', mobile: 'Application mobile', design: 'Design et UX',
  data: 'Data et BI', marketing: 'Marketing digital', redaction: 'Rédaction et traduction',
};
export const PROJECT_STATUS = { open: 'Ouvert', in_progress: 'En cours', completed: 'Terminé' };
export const PROPOSAL_STATUS = { pending: 'En attente', accepted: 'Acceptée', rejected: 'Refusée' };
export const ROLE_LABEL = { admin: 'Administrateur', client: 'Client', freelance: 'Freelance' };

export class Forbidden extends Error {}
export class Invalid extends Error {}

const nextId = (list) => Math.max(0, ...list.map((x) => x.id)) + 1;
const requireRole = (user, ...roles) => {
  if (!user) throw new Forbidden('Connexion requise.');
  if (!roles.includes(user.role)) throw new Forbidden('Accès réservé.');
};

export function register(state, { name, email, password, role }) {
  if (!['client', 'freelance'].includes(role)) throw new Invalid('Rôle invalide.'); // admin accounts cannot be self-registered
  if (!name || name.trim().length < 2) throw new Invalid('Le nom est obligatoire.');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw new Invalid('Adresse e-mail invalide.');
  if (state.users.some((u) => u.email === email.toLowerCase())) throw new Invalid('Cette adresse e-mail est déjà utilisée.');
  if ((password ?? '').length < 8) throw new Invalid('Le mot de passe doit contenir au moins 8 caractères.');
  const user = { id: nextId(state.users), name: name.trim(), email: email.toLowerCase(), role, password };
  state.users.push(user);
  return user;
}

export function login(state, email, password) {
  const user = state.users.find((u) => u.email === (email ?? '').toLowerCase() && u.password === password);
  if (!user) throw new Invalid('Identifiants incorrects.');
  return user;
}

export function createProject(state, user, data) {
  requireRole(user, 'client');
  if (!data.title || data.title.length < 3) throw new Invalid('Le titre est obligatoire.');
  if (!CATEGORIES[data.category]) throw new Invalid('Catégorie invalide.');
  if (!data.description || data.description.length < 30) throw new Invalid('La description doit contenir au moins 30 caractères.');
  if (!Number.isInteger(data.budget) || data.budget < 50) throw new Invalid('Le budget minimum est de 50 TND.');
  const project = { id: nextId(state.projects), clientId: user.id, ...data, status: 'open', createdAt: state.today };
  state.projects.push(project);
  return project;
}

export function apply(state, user, projectId, { message, price }) {
  requireRole(user, 'freelance');
  const project = state.projects.find((p) => p.id === projectId);
  if (!project) throw new Invalid('Mission introuvable.');
  if (project.status !== 'open') throw new Invalid("Cette mission n'accepte plus de candidatures.");
  if (!message || message.length < 20) throw new Invalid('Votre message doit contenir au moins 20 caractères.');
  if (!Number.isInteger(price) || price < 1) throw new Invalid('Le prix doit être un nombre positif.');
  if (state.proposals.some((p) => p.projectId === projectId && p.freelancerId === user.id)) {
    throw new Invalid('Vous avez déjà postulé à cette mission.'); // one proposal per freelancer and mission
  }
  const proposal = { id: nextId(state.proposals), projectId, freelancerId: user.id, message, price, status: 'pending' };
  state.proposals.push(proposal);
  return proposal;
}

export function accept(state, user, proposalId) {
  requireRole(user, 'client');
  const proposal = state.proposals.find((p) => p.id === proposalId);
  const project = state.projects.find((p) => p.id === proposal?.projectId);
  if (!proposal || project.clientId !== user.id) throw new Forbidden('Accès réservé.');
  if (project.status !== 'open') throw new Invalid("Cette mission n'est plus ouverte.");
  // Same transaction as the Laravel version: the others are rejected, the mission starts.
  for (const other of state.proposals) if (other.projectId === project.id && other.id !== proposal.id) other.status = 'rejected';
  proposal.status = 'accepted';
  project.status = 'in_progress';
}

export function reject(state, user, proposalId) {
  requireRole(user, 'client');
  const proposal = state.proposals.find((p) => p.id === proposalId);
  const project = state.projects.find((p) => p.id === proposal?.projectId);
  if (!proposal || project.clientId !== user.id) throw new Forbidden('Accès réservé.');
  proposal.status = 'rejected';
}

export function complete(state, user, projectId) {
  requireRole(user, 'client');
  const project = state.projects.find((p) => p.id === projectId);
  if (!project || project.clientId !== user.id) throw new Forbidden('Accès réservé.');
  if (project.status !== 'in_progress') throw new Invalid("La mission n'est pas en cours.");
  project.status = 'completed';
}

export function deleteProject(state, user, projectId) {
  requireRole(user, 'client', 'admin');
  const project = state.projects.find((p) => p.id === projectId);
  if (!project || (user.role !== 'admin' && project.clientId !== user.id)) throw new Forbidden('Accès réservé.');
  state.projects = state.projects.filter((p) => p.id !== projectId);
  state.proposals = state.proposals.filter((p) => p.projectId !== projectId);
}

export function deleteUser(state, user, userId) {
  requireRole(user, 'admin');
  const target = state.users.find((u) => u.id === userId);
  if (!target) return;
  if (target.role === 'admin') throw new Forbidden('Un administrateur ne peut pas être supprimé.');
  const owned = new Set(state.projects.filter((p) => p.clientId === userId).map((p) => p.id));
  state.projects = state.projects.filter((p) => p.clientId !== userId);
  state.proposals = state.proposals.filter((p) => !owned.has(p.projectId) && p.freelancerId !== userId);
  state.users = state.users.filter((u) => u.id !== userId);
}
