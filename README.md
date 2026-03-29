# Divinne Accountancy

SaaS practice management for UK accounting firms — Laravel 10, Inertia.js, and React 18.

## Requirements

- **PHP 8.1+** (8.2 recommended). The Laravel 10 stack is not compatible with PHP 8.0.
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.x

> If you use XAMPP with PHP 8.0 only, install [PHP 8.2+ for Windows](https://windows.php.net/download/) or switch XAMPP/Laragon to a newer PHP build, then point your terminal to that `php.exe` before running Artisan and Composer.

## Phase 1 (current)

- Laravel 10 + **Inertia.js v2** + **React 18** + **Tailwind CSS** + **Headless UI** + **Heroicons**
- Session authentication (login / register / logout)
- **Multi-tenant SaaS**: each accounting firm is a `tenants` record; users belong to a tenant
- Registration creates a **tenant** + first user as **tenant admin**
- Marketing **Welcome** page, **Dashboard** shell with firm branding (name, colours; logo when you add storage)

## Phase 2 (in progress)

- **Clients** — full company + extended sections (services, pricing, compliance, main contact) per `docs/requirements/client-management.md`
- **Tasks** — task types seeded from services; tasks auto-created when client services are enabled; list + edit + complete/delete (`/tasks`). Next: inline list edits, breakdown checklists, completion → next-period rollover, bulk actions.

## Setup

```bash
cd /path/to/divinne-acc

# PHP 8.1+ required
composer install

cp .env.example .env
php artisan key:generate

# Create database `divinne_acc` (or match .env), then:
php artisan migrate

# Optional: demo tenant + tenant admin (login: see .env.example SEED_ADMIN_*)
php artisan db:seed

npm install
npm run dev
# or: npm run build

php artisan serve
```

Visit `http://127.0.0.1:8000` — Welcome page; use **Register** to create a firm workspace, or **Sign in** after registering.

### Optional: tenant logos later

```bash
php artisan storage:link
```

Upload paths can use `storage/app/public/...` as documented in `docs/`.

## Documentation

Project requirements and schema live under `docs/`:

- `docs/requirements/main.md` — SaaS overview
- `docs/requirements/client-management.md` — clients module
- `docs/requirements/task-management.md` — tasks module
- `docs/database/structure.md` — MySQL schema
- `docs/tech-stack/technology-stack.md` — full stack

## Scripts

| Command        | Purpose              |
|----------------|----------------------|
| `npm run dev`  | Vite dev server + HMR |
| `npm run build`| Production assets     |
| `php artisan serve` | Local HTTP server |

## Next phases

- Task list inline editing, breakdown items UI, task completion → rollover + next task generation
- Spatie Laravel Permission (roles) — `role` column used for now on `users`
- Invoicing, time tracking, reporting (see `docs/requirements/main.md`)
