# FreelancePro

Web platform that connects clients and freelancers, built with Laravel 12 and PHP 8.3 as an academic
project at Esprit School of Business.

Clients publish missions, freelancers send proposals, the client accepts one, and an administrator
supervises accounts and missions.

## Features

| Role | What they can do |
|------|------------------|
| Visitor | Browse and search open missions, filter by category |
| Client | Register, publish a mission, review proposals, accept or reject, mark a mission as completed, delete own missions |
| Freelance | Register, apply once per mission with a price and a message, follow proposal status on a dashboard |
| Admin | Statistics, list and delete users, list and delete any mission |

Business rules enforced on the server:

- Roles are checked by a `role` middleware (a freelancer cannot publish a mission, a client cannot use admin actions).
- A freelancer can apply only once to a mission (unique constraint plus a friendly error).
- Accepting a proposal runs in a transaction: the other proposals are rejected and the mission moves to `in_progress`.
- Only the mission owner can accept, reject or complete; closed missions no longer accept proposals.
- Admin accounts cannot be self-registered or deleted from the interface.

## Stack

PHP 8.3, Laravel 12, Eloquent ORM, Blade templates, plain CSS. SQLite by default for a zero-setup demo;
switch to MySQL by editing the `DB_*` values in `.env`.

## Data model

```
users      (id, name, email, password, role, title, bio, skills, hourly_rate)
projects   (id, client_id -> users, title, description, category, budget, deadline, status)
proposals  (id, project_id -> projects, freelancer_id -> users, message, price, status, UNIQUE(project_id, freelancer_id))
```

## Run locally

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000. Demo accounts created by the seeder (password `password`, development only):

| Role | E-mail |
|------|--------|
| Admin | admin@freelancepro.test |
| Client | salma@freelancepro.test |
| Freelance | yassine@freelancepro.test |

## Screenshots

Screens captured from the running application are in `docs/screenshots/`:
home, missions list, proposal form, freelancer dashboard, proposals review, mission creation,
client dashboard and administration.

## Live demo (no PHP needed)

https://rania-dridi.github.io/demo/freelancepro/

`demo/` is a browser version of the application: the same business rules ported to JavaScript
(`demo/rules.js`, checked by `node --test demo/rules.test.js`), the same pages and CSS, state in `localStorage`.
Build it with `node scripts/build-demo.mjs` (output in `dist-demo/`).
