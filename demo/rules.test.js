// Parity tests: the browser port must follow the Laravel rules (ProposalController, ProjectController, RoleMiddleware).
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { seedState } from './data.js';
import { Forbidden, Invalid, accept, apply, complete, createProject, deleteUser, login, register } from './rules.js';

const who = (state, email) => state.users.find((u) => u.email === email);
const msg = 'Bonjour, voici ma proposition détaillée pour cette mission, avec un plan de livraison.';

test('only clients publish missions, freelancers cannot', () => {
  const s = seedState();
  const data = { title: 'Site vitrine', category: 'web', description: 'Un site de cinq pages avec formulaire de contact et galerie.', budget: 800 };
  assert.ok(createProject(s, who(s, 'salma@freelancepro.test'), data).id);
  assert.throws(() => createProject(s, who(s, 'yassine@freelancepro.test'), data), Forbidden);
});

test('a freelancer applies once per mission', () => {
  const s = seedState();
  const f = who(s, 'yassine@freelancepro.test');
  apply(s, f, 6, { message: msg, price: 800 }); // mission 6: Yassine has no proposal yet
  assert.throws(() => apply(s, f, 6, { message: msg, price: 800 }), /déjà postulé/);
  assert.throws(() => apply(s, who(s, 'salma@freelancepro.test'), 6, { message: msg, price: 800 }), Forbidden);
});

test('accepting a proposal rejects the others and starts the mission', () => {
  const s = seedState();
  const salma = who(s, 'salma@freelancepro.test');
  const [first, second] = s.proposals.filter((p) => p.projectId === 1);
  accept(s, salma, first.id);
  assert.equal(first.status, 'accepted');
  assert.equal(second.status, 'rejected');
  assert.equal(s.projects[0].status, 'in_progress');
  assert.throws(() => apply(s, who(s, 'walid@freelancepro.test'), 1, { message: msg, price: 100 }), /n'accepte plus/);
});

test('only the owner accepts, and only an in-progress mission can be completed', () => {
  const s = seedState();
  const [proposal] = s.proposals.filter((p) => p.projectId === 1);
  assert.throws(() => accept(s, who(s, 'karim@freelancepro.test'), proposal.id), Forbidden);
  const salma = who(s, 'salma@freelancepro.test');
  assert.throws(() => complete(s, salma, 1), /pas en cours/);
  accept(s, salma, proposal.id);
  complete(s, salma, 1);
  assert.equal(s.projects[0].status, 'completed');
});

test('registration blocks admin accounts, duplicates and short passwords; login checks credentials', () => {
  const s = seedState();
  assert.throws(() => register(s, { name: 'Eve', email: 'eve@x.co', password: 'password1', role: 'admin' }), Invalid);
  assert.throws(() => register(s, { name: 'Eve', email: 'salma@freelancepro.test', password: 'password1', role: 'client' }), /déjà utilisée/);
  assert.throws(() => register(s, { name: 'Eve', email: 'eve@x.co', password: 'short', role: 'client' }), /8 caractères/);
  assert.ok(register(s, { name: 'Eve', email: 'EVE@x.co', password: 'password1', role: 'freelance' }).id);
  assert.throws(() => login(s, 'admin@freelancepro.test', 'wrong'), Invalid);
});

test('admins can delete users but never other admins', () => {
  const s = seedState();
  const admin = who(s, 'admin@freelancepro.test');
  assert.throws(() => deleteUser(s, admin, admin.id), Forbidden);
  deleteUser(s, admin, who(s, 'salma@freelancepro.test').id);
  assert.ok(!s.projects.some((p) => p.title.startsWith('Boutique')));
  assert.throws(() => deleteUser(s, who(s, 'yassine@freelancepro.test'), 3), Forbidden);
});
