# Divinne Accountancy — Technology Stack Document

**Version:** 1.0
**Date:** 26 March 2026

---

## Table of Contents

1. [Stack Overview](#1-stack-overview)
2. [Architecture Diagram](#2-architecture-diagram)
3. [Backend — Laravel (PHP)](#3-backend--laravel-php)
4. [Frontend — React + Inertia.js](#4-frontend--react--inertiajs)
5. [Database — MySQL](#5-database--mysql)
6. [Authentication & Authorisation](#6-authentication--authorisation)
7. [Build & Development Tooling](#7-build--development-tooling)
8. [Key Libraries & Their Roles](#8-key-libraries--their-roles)
9. [UI & Styling](#9-ui--styling)
10. [File Storage & Cloud Services](#10-file-storage--cloud-services)
11. [Data Import/Export](#11-data-importexport)
12. [Charts & Data Visualisation](#12-charts--data-visualisation)
13. [Maps (if applicable)](#13-maps-if-applicable)
14. [Testing](#14-testing)
15. [Development Environment](#15-development-environment)
16. [Deployment & Infrastructure](#16-deployment--infrastructure)
17. [Folder Structure Convention](#17-folder-structure-convention)
18. [Version Summary](#18-version-summary)

---

## 1. Stack Overview

| Layer | Technology | Version |
|-------|-----------|---------|
| **Language** | PHP | ^8.1 |
| **Backend Framework** | Laravel | ^10.10 |
| **Frontend Framework** | React | ^18.2 |
| **SPA Bridge** | Inertia.js (React adapter) | ^2.0 (server) / ^1.0.14 (client) |
| **Database** | MySQL | 8.x |
| **CSS Framework** | Tailwind CSS | ^3.3.6 |
| **Build Tool** | Vite | ^5.0 |
| **API Auth** | Laravel Sanctum | ^3.3 |
| **Permissions** | Spatie Laravel Permission | ^6.21 |
| **Routing (client-side)** | Ziggy | ^2.6 |

**Architecture pattern:** Monolithic Laravel application with Inertia.js serving React SPA pages. No separate API + SPA — Inertia acts as the bridge between server-side Laravel controllers and client-side React components.

---

## 2. Architecture Diagram

```
┌─────────────────────────────────────────────────────────┐
│                      BROWSER                             │
│                                                         │
│   React 18  ─── Inertia.js Client ─── Tailwind CSS     │
│       │              │                                   │
│   Headless UI    Chart.js                               │
│   Heroicons      date-fns                               │
│                                                         │
└──────────────────────┬──────────────────────────────────┘
                       │  Inertia Protocol
                       │  (XHR / JSON page responses)
┌──────────────────────▼──────────────────────────────────┐
│                    LARAVEL 10                             │
│                                                         │
│   Routes ──> Middleware ──> Controllers ──> Inertia      │
│                               │                         │
│                          Services / Actions              │
│                               │                         │
│                    Eloquent ORM (Models)                 │
│                               │                         │
│   ┌───────────┬───────────┬───┴───────┬──────────┐     │
│   │ Sanctum   │ Spatie    │ Maatwebsite│ AWS SDK  │     │
│   │ (Auth)    │ (Roles)   │ (Excel)    │ (S3)     │     │
│   └───────────┴───────────┴───────────┴──────────┘     │
│                                                         │
└──────────────────────┬──────────────────────────────────┘
                       │
              ┌────────▼────────┐
              │   MySQL 8.x     │
              │  (Multi-tenant  │
              │   tenant_id)    │
              └────────┬────────┘
                       │
              ┌────────▼────────┐
              │  AWS S3 / Cloud │
              │  (File Storage) │
              └─────────────────┘
```

---

## 3. Backend — Laravel (PHP)

### 3.1 Framework

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^10.10 | Core framework: routing, ORM, middleware, queues, events, caching |
| `laravel/tinker` | ^2.8 | REPL for debugging and testing via artisan |
| `laravel/ui` | ^4.6 | Auth scaffolding (login, register, password reset views) |

### 3.2 How Inertia.js Fits In

Inertia.js replaces the traditional Blade view layer. Instead of returning Blade templates, controllers return Inertia responses that render React components:

```php
// Traditional Laravel:
return view('clients.index', ['clients' => $clients]);

// With Inertia:
return Inertia::render('Clients/Index', ['clients' => $clients]);
```

This means:
- **No separate API** — Laravel controllers serve both the initial page and subsequent XHR requests
- **No client-side routing** — Laravel handles all routing; Ziggy exposes named routes to React
- **Full server-side logic** — Validation, authorisation, and business logic stay in Laravel
- **React handles UI** — Each page is a React component receiving props from the controller

### 3.3 Multi-Tenancy Implementation

Multi-tenancy will be implemented at the application level using a `tenant_id` column:

| Approach | Details |
|----------|---------|
| Strategy | Shared database, `tenant_id` column on tenant-scoped tables |
| Middleware | A `TenantMiddleware` resolves the current tenant from the authenticated user's `tenant_id` |
| Global Scope | An Eloquent Global Scope automatically applies `WHERE tenant_id = ?` to all tenant-scoped models |
| Model Trait | A `BelongsToTenant` trait adds the global scope and auto-sets `tenant_id` on create |

---

## 4. Frontend — React + Inertia.js

### 4.1 Core

| Package | Version | Purpose |
|---------|---------|---------|
| `react` | ^18.2 | UI component library |
| `react-dom` | ^18.2 | React DOM rendering |
| `@inertiajs/react` | ^1.0.14 | Inertia.js React adapter — handles page visits, forms, shared data |
| `tightenco/ziggy` (server) | ^2.6 | Exposes Laravel named routes to JavaScript (`route('clients.index')`) |

### 4.2 How Pages Work

```
resources/js/
├── Pages/
│   ├── Dashboard/
│   │   └── Index.jsx
│   ├── Clients/
│   │   ├── Index.jsx        (client list)
│   │   ├── Create.jsx       (new client form)
│   │   └── Edit.jsx         (edit client form)
│   ├── Tasks/
│   │   ├── Index.jsx        (task list)
│   │   └── Edit.jsx         (task edit form)
│   └── Auth/
│       ├── Login.jsx
│       └── Register.jsx
├── Components/
│   ├── Layout/
│   │   ├── AppLayout.jsx
│   │   ├── Navbar.jsx
│   │   └── Sidebar.jsx
│   ├── Forms/
│   │   ├── TextInput.jsx
│   │   ├── SelectInput.jsx
│   │   ├── DatePicker.jsx
│   │   ├── Toggle.jsx
│   │   └── CurrencyInput.jsx
│   ├── Tables/
│   │   ├── DataTable.jsx
│   │   └── Pagination.jsx
│   └── UI/
│       ├── Modal.jsx
│       ├── Dropdown.jsx
│       └── Badge.jsx
├── Hooks/
│   └── usePermissions.js
├── Layouts/
│   ├── AuthenticatedLayout.jsx
│   └── GuestLayout.jsx
└── app.jsx                   (Inertia app entry point)
```

### 4.3 Inertia.js Key Features Used

| Feature | Usage |
|---------|-------|
| `useForm()` | Form handling with validation errors from Laravel |
| `usePage()` | Access shared data (auth user, tenant, flash messages) |
| `router.visit()` | Programmatic navigation without full page reload |
| `Link` component | SPA-style navigation links |
| Shared data | Pass auth user, permissions, tenant info on every request via `HandleInertiaRequests` middleware |
| Persistent layouts | Keep sidebar/navbar mounted across page navigations |

---

## 5. Database — MySQL

| Attribute | Value |
|-----------|-------|
| Engine | MySQL 8.x |
| ORM | Eloquent (Laravel) |
| Schema Tool | `doctrine/dbal` ^3.10 (enables column modifications in migrations) |
| Migrations | Laravel migrations (version-controlled schema) |
| Seeders | Laravel seeders for lookup/reference data |
| UUIDs | `ramsey/uuid` ^4.9 available for any tables needing UUID primary keys |

### 5.1 Database Configuration

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=divinne_acc
DB_USERNAME=root
DB_PASSWORD=
```

### 5.2 Key Eloquent Patterns

| Pattern | Usage |
|---------|-------|
| Global Scopes | `TenantScope` — auto-filter by `tenant_id` |
| Model Traits | `BelongsToTenant`, `HasAuditTimestamps` |
| Relationships | `hasOne`, `hasMany`, `belongsTo`, `belongsToMany` for all table relationships |
| Soft Deletes | On `clients` (via `is_active` flag or Laravel's `SoftDeletes` trait) |
| Observers | `TaskObserver` — auto-generate tasks when services toggled, roll forward dates on completion |
| Factories | For generating test data via Faker |

---

## 6. Authentication & Authorisation

### 6.1 Authentication — Laravel Sanctum

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/sanctum` | ^3.3 | SPA cookie-based authentication (no API tokens needed for Inertia) |

Sanctum provides:
- Session-based authentication for the SPA (via cookies, CSRF token)
- No need for JWT or Passport — Inertia apps use standard Laravel sessions
- API token capability available if needed later (mobile app, external integrations)

### 6.2 Authorisation — Spatie Permission

| Package | Version | Purpose |
|---------|---------|---------|
| `spatie/laravel-permission` | ^6.21 | Role and permission management |

Implementation:

| Concept | Details |
|---------|---------|
| Roles | `tenant_admin`, `partner`, `manager`, `staff` — scoped per tenant |
| Permissions | Granular permissions: `clients.create`, `clients.edit`, `clients.delete`, `tasks.create`, `tasks.complete`, `settings.manage`, etc. |
| Middleware | `role:tenant_admin`, `permission:clients.delete` on routes |
| Blade/React | Permissions shared via Inertia for conditional UI rendering |

Permissions passed to frontend via `HandleInertiaRequests`:

```php
'auth' => [
    'user' => $request->user(),
    'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
    'roles' => $request->user()?->getRoleNames(),
],
```

---

## 7. Build & Development Tooling

### 7.1 Build Tool — Vite

| Package | Version | Purpose |
|---------|---------|---------|
| `vite` | ^5.0 | Fast build tool with HMR (Hot Module Replacement) |
| `laravel-vite-plugin` | ^1.0 | Laravel integration for Vite |
| `@vitejs/plugin-react` | ^4.2.1 | React JSX/TSX support for Vite |

### 7.2 NPM Scripts

| Script | Command | Purpose |
|--------|---------|---------|
| `dev` | `vite` | Start development server with HMR |
| `build` | `vite build` | Production build (minified, code-split) |

### 7.3 CSS Processing

| Package | Version | Purpose |
|---------|---------|---------|
| `tailwindcss` | ^3.3.6 | Utility-first CSS framework |
| `@tailwindcss/forms` | ^0.5.7 | Form element styling plugin for Tailwind |
| `autoprefixer` | ^10.4.16 | Vendor prefix automation |
| `postcss` | ^8.4.32 | CSS processing pipeline |

---

## 8. Key Libraries & Their Roles

### 8.1 Backend Libraries

| Package | Version | Role in Divinne |
|---------|---------|-----------------|
| `laravel/framework` | ^10.10 | Core application framework |
| `inertiajs/inertia-laravel` | ^2.0 | Server-side Inertia adapter — renders React pages from controllers |
| `laravel/sanctum` | ^3.3 | SPA session authentication |
| `spatie/laravel-permission` | ^6.21 | Role-based access control (Tenant Admin, Partner, Manager, Staff) |
| `tightenco/ziggy` | ^2.6 | Share Laravel named routes with React (`route('clients.show', id)`) |
| `maatwebsite/excel` | ^3.1 | Excel/CSV import and export (client bulk import, report export) |
| `phpoffice/phpspreadsheet` | ^1.30 | Underlying spreadsheet engine for Maatwebsite |
| `aws/aws-sdk-php` | ^3.356 | AWS S3 integration for file storage (logos, documents) |
| `google/apiclient` | 2.15 | Google API integration (future: Google Drive, Gmail, Calendar) |
| `guzzlehttp/guzzle` | ^7.2 | HTTP client for external API calls (Companies House, HMRC) |
| `doctrine/dbal` | ^3.10 | Database abstraction — enables column modifications in migrations |
| `ramsey/uuid` | ^4.9 | UUID generation (for tenant IDs, public-facing references) |
| `laravel/ui` | ^4.6 | Auth scaffolding base (login, register, password reset) |

### 8.2 Frontend Libraries

| Package | Version | Role in Divinne |
|---------|---------|-----------------|
| `react` | ^18.2 | UI component framework |
| `react-dom` | ^18.2 | DOM rendering |
| `@inertiajs/react` | ^1.0.14 | Inertia client — page visits, forms, shared data |
| `@headlessui/react` | ^1.7.17 | Accessible unstyled UI primitives (modals, dropdowns, toggles, tabs, comboboxes) |
| `@heroicons/react` | ^2.0.18 | SVG icon library (outline + solid) — used throughout navigation and buttons |
| `axios` | ^1.6.4 | HTTP client for any direct API calls (file uploads, async operations) |
| `chart.js` | ^4.5 | Charting engine for dashboard and reports |
| `react-chartjs-2` | ^5.3 | React wrapper for Chart.js |
| `date-fns` | ^2.30 | Date manipulation and formatting (UK date format dd/MM/yyyy) |
| `leaflet` | ^1.9.4 | Map rendering (if location features are needed) |
| `react-leaflet` | ^4.2.1 | React wrapper for Leaflet maps |
| `leaflet.markercluster` | ^1.5.3 | Map marker clustering |
| `react-leaflet-cluster` | ^3.1.1 | React cluster layer for Leaflet |

---

## 9. UI & Styling

### 9.1 Tailwind CSS Configuration

| Aspect | Approach |
|--------|----------|
| Theme | Custom colour palette matching Divinne branding (dark teal header like Bright Manager) |
| Forms | `@tailwindcss/forms` plugin for consistent form element styling |
| Components | Headless UI for accessible modals, dropdowns, toggles, comboboxes, disclosure panels |
| Icons | Heroicons (24x24 outline and solid variants) |
| Dark Mode | Optional — Tailwind `dark:` variant support available |
| Responsive | Desktop-first with responsive breakpoints for tablet |

### 9.2 Component Architecture

| Component Type | Library | Examples |
|---------------|---------|---------|
| Layout | Custom React + Tailwind | AppLayout, Navbar, Sidebar |
| Modals / Dialogs | Headless UI `Dialog` | Delete confirmation, task edit form |
| Dropdowns | Headless UI `Menu`, `Listbox` | Action status selector, user assignment |
| Toggles | Headless UI `Switch` | Service on/off, boolean fields |
| Combobox / Search | Headless UI `Combobox` | Client search, contact search, SIC code select |
| Disclosure / Accordion | Headless UI `Disclosure` | Collapsible client form sections |
| Tabs | Headless UI `Tab` | Settings page, client detail tabs |
| Data Tables | Custom React | Client list, task list with sorting/filtering/pagination |
| Charts | Chart.js + react-chartjs-2 | Dashboard widgets, reports |
| Dates | date-fns | Format, parse, add months/days for UK date format |

---

## 10. File Storage & Cloud Services

### 10.1 AWS S3

| Package | Version | Purpose |
|---------|---------|---------|
| `aws/aws-sdk-php` | ^3.356 | AWS S3 integration |

| Use Case | Storage Path Pattern |
|----------|---------------------|
| Company logos | `tenants/{tenant_id}/logo/` |
| Client documents | `tenants/{tenant_id}/clients/{client_id}/documents/` |
| Invoice PDFs | `tenants/{tenant_id}/invoices/` |
| Report exports | `tenants/{tenant_id}/exports/` |
| User avatars | `tenants/{tenant_id}/users/{user_id}/avatar/` |

Laravel filesystem configuration:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=eu-west-2
AWS_BUCKET=divinne-storage
```

### 10.2 Google API (Future)

| Package | Version | Purpose |
|---------|---------|---------|
| `google/apiclient` | 2.15 | Google integration |

Planned uses:
- Google Drive for client document sync
- Gmail integration for email tracking
- Google Calendar for deadline sync

---

## 11. Data Import/Export

### 11.1 Excel/CSV Processing

| Package | Version | Purpose |
|---------|---------|---------|
| `maatwebsite/excel` | ^3.1 | Import and export Excel/CSV files |
| `phpoffice/phpspreadsheet` | ^1.30 | Underlying spreadsheet engine |

| Feature | Implementation |
|---------|---------------|
| Client bulk import | CSV upload → preview → validate → import via `ClientImport` class |
| Client export | Export filtered client list to XLSX/CSV |
| Task export | Export filtered task list to XLSX/CSV |
| Report export | Export any report to XLSX/CSV/PDF |
| Breakdown template import | Import checklist templates from CSV |

---

## 12. Charts & Data Visualisation

| Package | Version | Purpose |
|---------|---------|---------|
| `chart.js` | ^4.5 | Charting engine |
| `react-chartjs-2` | ^5.3 | React bindings for Chart.js |

| Dashboard Widget | Chart Type |
|-----------------|------------|
| Tasks by status | Doughnut / Pie chart |
| Tasks due by month | Bar chart |
| Revenue trend | Line chart |
| Staff workload | Horizontal bar chart |
| Client growth | Area chart |

---

## 13. Maps (if applicable)

| Package | Version | Purpose |
|---------|---------|---------|
| `leaflet` | ^1.9.4 | Map rendering |
| `react-leaflet` | ^4.2.1 | React wrapper |
| `leaflet.markercluster` | ^1.5.3 | Marker clustering |
| `react-leaflet-cluster` | ^3.1.1 | React cluster component |

Potential uses:
- Client location map (plot client addresses on UK map)
- Staff location / coverage visualisation

---

## 14. Testing

### 14.1 Backend Testing

| Package | Version | Purpose |
|---------|---------|---------|
| `phpunit/phpunit` | ^10.1 | PHP unit and feature testing framework |
| `mockery/mockery` | ^1.4.4 | Mocking library for unit tests |
| `fakerphp/faker` | ^1.9.1 | Fake data generation for factories and seeders |
| `nunomaduro/collision` | ^7.0 | Beautiful error reporting in CLI |
| `spatie/laravel-ignition` | ^2.0 | Enhanced error page in browser during development |
| `laravel/pint` | ^1.0 | PHP code style fixer (PSR-12 / Laravel preset) |

### 14.2 Testing Strategy

| Test Type | Tool | Scope |
|-----------|------|-------|
| Unit tests | PHPUnit | Models, services, helper functions |
| Feature tests | PHPUnit | HTTP requests, controller responses, middleware |
| Database tests | PHPUnit + RefreshDatabase | Migration, seeder, query verification |
| Factory tests | Faker + Factories | Generate realistic test data |
| Code style | Laravel Pint | Enforce consistent PHP code formatting |
| Frontend tests | (To be added) | Jest / Vitest for React component testing |

---

## 15. Development Environment

### 15.1 Laravel Sail (Docker)

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/sail` | ^1.18 | Docker-based local development environment |

Sail provides:
- PHP 8.1+ container
- MySQL 8.x container
- Redis container (for caching/queues)
- Mailpit (local email testing)
- MinIO (local S3-compatible storage)

### 15.2 Local Development Commands

| Command | Purpose |
|---------|---------|
| `sail up -d` | Start Docker containers |
| `sail artisan migrate` | Run database migrations |
| `sail artisan db:seed` | Seed lookup/reference data |
| `sail npm run dev` | Start Vite dev server with HMR |
| `sail artisan tinker` | Open Laravel REPL |
| `sail test` | Run PHPUnit test suite |
| `sail artisan pint` | Fix code style |

### 15.3 Alternative (without Docker)

| Requirement | Version |
|-------------|---------|
| PHP | ^8.1 with extensions: mbstring, xml, curl, mysql, gd, zip |
| Composer | ^2.x |
| Node.js | ^18.x or ^20.x |
| npm | ^9.x or ^10.x |
| MySQL | 8.x |

---

## 16. Deployment & Infrastructure

### 16.1 Recommended Production Setup

| Component | Service | Notes |
|-----------|---------|-------|
| Hosting | AWS EC2 / Laravel Forge / Laravel Vapor | UK/EU region (eu-west-2) |
| Database | AWS RDS (MySQL 8.x) | Multi-AZ for high availability |
| File Storage | AWS S3 | Tenant-isolated bucket paths |
| CDN | CloudFront | Static assets (CSS, JS, images) |
| Email | Amazon SES / SendGrid | Transactional emails (invitations, notifications, invoices) |
| Queue Worker | Laravel Queue (Redis driver) | Background jobs: task generation, email sending, exports |
| Cache | Redis (ElastiCache) | Session storage, query caching, rate limiting |
| SSL | AWS ACM / Let's Encrypt | HTTPS enforced |
| Domain | `app.divinne.com` | Single domain, tenant resolved from auth |

### 16.2 Build & Deploy Pipeline

```
Git Push (main branch)
    │
    ▼
CI/CD (GitHub Actions / Laravel Forge)
    │
    ├─→ Run PHPUnit tests
    ├─→ Run Laravel Pint (code style)
    ├─→ npm run build (Vite production build)
    │
    ▼
Deploy to Server
    │
    ├─→ composer install --optimize-autoloader --no-dev
    ├─→ php artisan migrate --force
    ├─→ php artisan config:cache
    ├─→ php artisan route:cache
    ├─→ php artisan view:cache
    └─→ php artisan queue:restart
```

---

## 17. Folder Structure Convention

```
divinne-acc/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ClientController.php
│   │   │   ├── TaskController.php
│   │   │   ├── Auth/
│   │   │   └── Settings/
│   │   ├── Middleware/
│   │   │   ├── TenantMiddleware.php
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/
│   │       ├── StoreClientRequest.php
│   │       └── StoreTaskRequest.php
│   ├── Models/
│   │   ├── Tenant.php
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Task.php
│   │   ├── Contact.php
│   │   ├── CompanyDetail.php
│   │   └── ... (all models)
│   ├── Services/
│   │   ├── TaskGenerationService.php
│   │   ├── TaskCompletionService.php
│   │   └── ClientImportService.php
│   ├── Observers/
│   │   ├── ClientServiceObserver.php
│   │   └── TaskObserver.php
│   └── Traits/
│       └── BelongsToTenant.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│       ├── LookupSeeder.php
│       ├── TaskTypeSeeder.php
│       └── ServiceSeeder.php
├── resources/
│   └── js/
│       ├── Pages/          (Inertia page components)
│       ├── Components/     (Reusable React components)
│       ├── Hooks/          (Custom React hooks)
│       ├── Layouts/        (Layout wrappers)
│       └── app.jsx         (Entry point)
├── routes/
│   ├── web.php             (All Inertia routes)
│   └── auth.php            (Authentication routes)
├── docs/                   (This documentation)
├── composer.json
├── package.json
├── vite.config.js
├── tailwind.config.js
└── postcss.config.js
```

---

## 18. Version Summary

### Backend (composer.json)

| Package | Version | Category |
|---------|---------|----------|
| php | ^8.1 | Runtime |
| laravel/framework | ^10.10 | Framework |
| inertiajs/inertia-laravel | ^2.0 | SPA Bridge |
| laravel/sanctum | ^3.3 | Auth |
| laravel/ui | ^4.6 | Auth Scaffolding |
| spatie/laravel-permission | ^6.21 | Roles & Permissions |
| tightenco/ziggy | ^2.6 | Route Sharing |
| aws/aws-sdk-php | ^3.356 | Cloud Storage |
| google/apiclient | 2.15 | Google Integration |
| guzzlehttp/guzzle | ^7.2 | HTTP Client |
| maatwebsite/excel | ^3.1 | Import/Export |
| phpoffice/phpspreadsheet | ^1.30 | Spreadsheet Engine |
| doctrine/dbal | ^3.10 | DB Abstraction |
| ramsey/uuid | ^4.9 | UUID Generation |
| laravel/tinker | ^2.8 | REPL |

### Backend Dev (composer.json require-dev)

| Package | Version | Category |
|---------|---------|----------|
| phpunit/phpunit | ^10.1 | Testing |
| mockery/mockery | ^1.4.4 | Mocking |
| fakerphp/faker | ^1.9.1 | Fake Data |
| laravel/pint | ^1.0 | Code Style |
| laravel/sail | ^1.18 | Docker Dev |
| nunomaduro/collision | ^7.0 | CLI Errors |
| spatie/laravel-ignition | ^2.0 | Error Page |

### Frontend (package.json)

| Package | Version | Category |
|---------|---------|----------|
| react | ^18.2 | UI Framework |
| react-dom | ^18.2 | DOM Rendering |
| @inertiajs/react | ^1.0.14 | SPA Bridge |
| @headlessui/react | ^1.7.17 | UI Primitives |
| @heroicons/react | ^2.0.18 | Icons |
| axios | ^1.6.4 | HTTP Client |
| chart.js | ^4.5 | Charts |
| react-chartjs-2 | ^5.3 | Chart Components |
| date-fns | ^2.30 | Date Utilities |
| leaflet | ^1.9.4 | Maps |
| react-leaflet | ^4.2.1 | Map Components |
| leaflet.markercluster | ^1.5.3 | Map Clusters |
| react-leaflet-cluster | ^3.1.1 | Cluster Component |

### Frontend Dev (package.json devDependencies)

| Package | Version | Category |
|---------|---------|----------|
| vite | ^5.0 | Build Tool |
| laravel-vite-plugin | ^1.0 | Laravel Integration |
| @vitejs/plugin-react | ^4.2.1 | React Plugin |
| tailwindcss | ^3.3.6 | CSS Framework |
| @tailwindcss/forms | ^0.5.7 | Form Plugin |
| autoprefixer | ^10.4.16 | CSS Prefixes |
| postcss | ^8.4.32 | CSS Pipeline |
| @types/react | ^18.2.43 | TypeScript Defs |
| @types/react-dom | ^18.2.17 | TypeScript Defs |

---

## Document Index

| Document | Path | Description |
|----------|------|-------------|
| **Main Requirements** | `docs/requirements/main.md` | SaaS platform, auth, onboarding, all modules overview |
| **Client Management** | `docs/requirements/client-management.md` | Detailed client module (167 fields, 29 DB tables) |
| **Task Management** | `docs/requirements/task-management.md` | Detailed task module (14 task types, edit form) |
| **Database Structure** | `docs/database/structure.md` | MySQL schema (35 tables — client + task management) |
| **Technology Stack** (this doc) | `docs/tech-stack/technology-stack.md` | Full tech stack, architecture, libraries, deployment |
| **Pending Questions** | `docs/pending/questions.md` | Open questions awaiting answers |

---

*End of Document*
