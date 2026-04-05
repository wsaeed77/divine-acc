# Divinne Accountancy — Master Requirements Document

**Application Type:** SaaS (Software as a Service)
**Version:** 1.0
**Date:** 26 March 2026

---

## Table of Contents

1. [Product Vision](#1-product-vision)
2. [Architecture Overview](#2-architecture-overview)
3. [Multi-Tenancy](#3-multi-tenancy)
4. [Platform Registration & Onboarding](#4-platform-registration--onboarding)
5. [Authentication & Security](#5-authentication--security)
6. [User Management & Roles](#6-user-management--roles)
7. [Company Profile & Settings](#7-company-profile--settings)
8. [Dashboard / Overview](#8-dashboard--overview)
9. [Navigation & Layout](#9-navigation--layout)
10. [Module Summary](#10-module-summary)
11. [Module: Client Management](#11-module-client-management)
12. [Module: Task Management](#12-module-task-management)
13. [Module: Invoicing](#13-module-invoicing)
14. [Module: Time Tracking](#14-module-time-tracking)
15. [Module: Queue](#15-module-queue)
16. [Module: Resources / Documents](#16-module-resources--documents)
17. [Module: Tools](#17-module-tools)
18. [Module: Reporting](#18-module-reporting)
19. [Notifications System](#19-notifications-system)
20. [Global Search](#20-global-search)
21. [Audit Trail & Activity Log](#21-audit-trail--activity-log)
22. [Subscription & Billing](#22-subscription--billing)
23. [Data Management](#23-data-management)
24. [Non-Functional Requirements](#24-non-functional-requirements)
25. [Technology Stack](#25-technology-stack)

---

## 1. Product Vision

Divinne Accountancy is a **SaaS practice management platform** for accounting firms, inspired by Bright Manager. It enables accountancy practices to manage their full client lifecycle — from onboarding through to compliance tracking, task management, invoicing, and reporting — all within a single cloud-based application.

**Target Users:** UK-based accounting firms, bookkeepers, and tax advisors.

**Key Value Propositions:**
- Multi-tenant cloud platform — no installation, accessible from anywhere
- Complete client management with UK statutory compliance tracking (Companies House, HMRC, VAT, PAYE, CIS, P11D)
- Automated task generation driven by client services and compliance deadlines
- Integrated invoicing, time tracking, and reporting
- Team collaboration with role-based access, task assignment, and notifications

---

## 2. Architecture Overview

### 2.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────┐
│                    INTERNET                          │
│                                                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐          │
│  │ Firm A   │  │ Firm B   │  │ Firm C   │  ...     │
│  │ (Tenant) │  │ (Tenant) │  │ (Tenant) │          │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘          │
│       └──────────────┼──────────────┘               │
│                      │                              │
│              ┌───────▼────────┐                     │
│              │  Load Balancer │                     │
│              └───────┬────────┘                     │
│                      │                              │
│              ┌───────▼────────┐                     │
│              │  Web Server    │                     │
│              │  (Application) │                     │
│              └───────┬────────┘                     │
│                      │                              │
│              ┌───────▼────────┐                     │
│              │  MySQL Database│                     │
│              │  (Multi-tenant)│                     │
│              └────────────────┘                     │
└─────────────────────────────────────────────────────┘
```

### 2.2 Application Type

| Attribute | Value |
|-----------|-------|
| Deployment | Cloud-hosted SaaS |
| Access | Web browser (responsive, desktop-first) |
| Database | MySQL |
| Multi-tenancy | Shared database with `tenant_id` on every table |
| Authentication | Session-based or JWT |
| API | RESTful API backend |

---

## 3. Multi-Tenancy

Each accounting firm that signs up is a **tenant**. All tenants share the same application instance and database, isolated by a `tenant_id` column.

### 3.1 Tenant Isolation Rules

| Rule | Description |
|------|-------------|
| MT-01 | Every data table (except platform-level tables) shall include a `tenant_id` column. |
| MT-02 | All queries shall be scoped to the authenticated user's `tenant_id`. A user shall never see data from another tenant. |
| MT-03 | Lookup tables (e.g., `lkp_action_statuses`) can be **global** (shared across tenants) or **tenant-specific** (custom values per firm). |
| MT-04 | File uploads (logos, documents) shall be stored in tenant-isolated storage paths. |
| MT-05 | Subdomain or URL-based tenant identification (e.g., `firmname.divinne.com` or `app.divinne.com/firmname`). |

### 3.2 Platform-Level vs Tenant-Level Tables

| Level | Tables | Description |
|-------|--------|-------------|
| **Platform** | `tenants`, `subscription_plans`, `tenant_subscriptions`, `platform_admins` | Shared across all tenants; managed by Divinne platform admins |
| **Tenant** | All other tables (`users`, `clients`, `tasks`, etc.) | Scoped by `tenant_id`; each firm sees only their own data |

---

## 4. Platform Registration & Onboarding

### 4.1 Registration Flow

```
Landing Page
    │
    ▼
"Start Free Trial" / "Sign Up"
    │
    ▼
Step 1: Account Owner Details
    - Full Name
    - Email Address
    - Password (+ confirm)
    - Phone Number
    │
    ▼
Step 2: Company Details
    - Company/Firm Name
    - Company Logo (upload)
    - Business Address
    - Company Phone
    - Company Email
    - Website (optional)
    - Number of Staff (range selector)
    │
    ▼
Step 3: Subscription Plan Selection
    - Choose plan (Free Trial / Starter / Professional / Enterprise)
    - Payment details (skip for free trial)
    │
    ▼
Email Verification
    │
    ▼
First Login → Onboarding Wizard
    │
    ▼
Dashboard
```

### 4.2 Registration Fields

#### Step 1: Account Owner

| # | Field | Type | Required | Notes |
|---|-------|------|----------|-------|
| 1 | Full Name | Text | Yes | Becomes the first admin user |
| 2 | Email Address | Email | Yes | Used for login; must be unique across platform |
| 3 | Password | Password | Yes | Min 8 chars, 1 uppercase, 1 number, 1 special char |
| 4 | Confirm Password | Password | Yes | Must match Password |
| 5 | Phone Number | Text | Yes | With country code |

#### Step 2: Company Details

| # | Field | Type | Required | Notes |
|---|-------|------|----------|-------|
| 1 | Company/Firm Name | Text | Yes | Displayed throughout the app and on invoices |
| 2 | Company Logo | File Upload | No | Accepted: PNG, JPG, SVG. Max 2MB. Used in header, invoices, reports. |
| 3 | Business Address | Textarea | Yes | Full postal address |
| 4 | Company Phone | Text | No | |
| 5 | Company Email | Email | Yes | General contact email (can differ from owner email) |
| 6 | Website | URL | No | |
| 7 | Number of Staff | Dropdown | Yes | 1-5, 6-15, 16-50, 50+ (helps with plan recommendation) |
| 8 | Country | Dropdown | Yes | Default: United Kingdom |

#### Step 3: Subscription Plan

| # | Field | Type | Required | Notes |
|---|-------|------|----------|-------|
| 1 | Plan | Radio / Card Select | Yes | See Section 22 for plan details |
| 2 | Payment Method | Card input / Direct Debit | Conditional | Not required for Free Trial |
| 3 | Billing Cycle | Radio | Conditional | Monthly / Annual (annual = discount) |

### 4.3 Post-Registration Onboarding Wizard

After first login, a guided wizard helps set up the firm:

| Step | Title | Actions |
|------|-------|---------|
| 1 | **Welcome** | Welcome message, overview of features |
| 2 | **Invite Team** | Enter email addresses of team members to invite; assign roles (Partner, Manager, Staff) |
| 3 | **Customise Services** | Review and customise the master services list (add/remove/rename services the firm offers) |
| 4 | **Set Up Lookup Data** | Pre-loaded UK defaults for: tax offices, SIC codes, action statuses. Option to add custom values. |
| 5 | **Import Clients** (optional) | CSV upload to bulk-import existing clients |
| 6 | **You're Ready!** | Link to dashboard, link to create first client |

---

## 5. Authentication & Security

### 5.1 Login

| ID | Requirement |
|----|-------------|
| AUTH-01 | Users shall log in with email and password. |
| AUTH-02 | "Remember Me" checkbox to extend session duration. |
| AUTH-03 | After 3 failed login attempts, show CAPTCHA. After 10 failed attempts, temporarily lock the account (15 min). |
| AUTH-04 | Sessions shall expire after configurable inactivity period (default: 30 minutes). |

### 5.2 Password Management

| ID | Requirement |
|----|-------------|
| AUTH-05 | "Forgot Password" flow: enter email → receive reset link (expires in 1 hour) → set new password. |
| AUTH-06 | Password change available in user profile settings. |
| AUTH-07 | Password requirements: minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number, 1 special character. |
| AUTH-08 | Passwords stored as salted hashes (bcrypt or Argon2). |

### 5.3 Two-Factor Authentication (2FA)

| ID | Requirement |
|----|-------------|
| AUTH-09 | Optional 2FA via authenticator app (TOTP — e.g., Google Authenticator). |
| AUTH-10 | 2FA can be enforced at the tenant level (admin can require all users to enable 2FA). |
| AUTH-11 | Backup/recovery codes provided when 2FA is enabled (one-time use). |

### 5.4 Security Standards

| ID | Requirement |
|----|-------------|
| SEC-01 | All traffic over HTTPS (TLS 1.2+). |
| SEC-02 | CSRF protection on all forms. |
| SEC-03 | XSS prevention (output encoding, Content Security Policy). |
| SEC-04 | SQL injection prevention (parameterised queries / ORM). |
| SEC-05 | Rate limiting on authentication endpoints. |
| SEC-06 | Sensitive data (UTR, NI numbers, VAT numbers) encrypted at rest. |
| SEC-07 | GDPR compliance: data export, right to deletion, data processing agreements. |

---

## 6. User Management & Roles

### 6.1 User Roles

| Role | Scope | Description |
|------|-------|-------------|
| **Platform Admin** | Entire platform | Divinne staff; manages tenants, subscriptions, platform settings. Not visible to tenants. |
| **Tenant Admin** | Single tenant | The firm's administrator. Full access to all features + settings + user management. Typically the account owner. |
| **Partner** | Single tenant | Senior partner. Can manage clients, tasks, view all data. Can be assigned as client partner. |
| **Manager** | Single tenant | Manages a portfolio of clients and team. Can be assigned as client manager. |
| **Staff** | Single tenant | Standard user. Works on assigned clients/tasks. Limited settings access. |

### 6.2 Permissions Matrix

| Feature | Tenant Admin | Partner | Manager | Staff |
|---------|:---:|:---:|:---:|:---:|
| View all clients | Yes | Yes | Yes | Own assigned only |
| Create client | Yes | Yes | Yes | Yes |
| Edit any client | Yes | Yes | Yes | Own assigned only |
| Delete (deactivate) client | Yes | Yes | No | No |
| View all tasks | Yes | Yes | Yes | Own assigned only |
| Create task | Yes | Yes | Yes | Yes |
| Edit any task | Yes | Yes | Yes | Own assigned only |
| Delete task | Yes | Yes | Yes | No |
| Complete task | Yes | Yes | Yes | Yes (own) |
| Manage users | Yes | No | No | No |
| Manage company settings | Yes | No | No | No |
| Manage subscription/billing | Yes | No | No | No |
| Manage lookup data | Yes | Yes | No | No |
| Manage services list | Yes | Yes | No | No |
| Manage breakdown templates | Yes | Yes | Yes | No |
| View reports | Yes | Yes | Yes | Limited |
| Export data | Yes | Yes | No | No |
| Invoicing | Yes | Yes | Yes | No |

### 6.3 User Invitation Flow

| ID | Requirement |
|----|-------------|
| USR-01 | Tenant Admin can invite users by entering their email address and assigning a role. |
| USR-02 | Invited user receives an email with a link to set their password and complete their profile. |
| USR-03 | Invitation links expire after 7 days. Admin can resend. |
| USR-04 | Tenant Admin can deactivate a user (soft delete). Deactivated users cannot log in but their data remains. |
| USR-05 | Users can update their own profile: name, email, password, avatar/photo. |

### 6.4 User Profile Fields

| # | Field | Type | Required | Notes |
|---|-------|------|----------|-------|
| 1 | First Name | Text | Yes | |
| 2 | Last Name | Text | Yes | |
| 3 | Email | Email | Yes | Login credential; unique per platform |
| 4 | Phone | Text | No | |
| 5 | Avatar / Photo | File Upload | No | Displayed as initials circle if no photo |
| 6 | Role | Dropdown | Yes | Set by admin; not self-editable |
| 7 | 2FA Enabled | Toggle | No | User can opt-in; admin can enforce |
| 8 | Status | Active / Inactive | — | Managed by admin |

---

## 7. Company Profile & Settings

### 7.1 Company Profile

Accessible by Tenant Admin. Displayed on invoices, reports, and the app header.

| # | Field | Type | Required | Notes |
|---|-------|------|----------|-------|
| 1 | Company/Firm Name | Text | Yes | |
| 2 | Company Logo | File Upload | No | Used in app header, invoices, PDF exports |
| 3 | Favicon | File Upload | No | Browser tab icon |
| 4 | Business Address | Textarea | Yes | |
| 5 | Company Phone | Text | No | |
| 6 | Company Email | Email | Yes | |
| 7 | Website | URL | No | |
| 8 | Company Registration Number | Text | No | The firm's own Companies House number |
| 9 | VAT Number | Text | No | The firm's own VAT registration |
| 10 | UTR Number | Text | No | The firm's own UTR |
| 11 | Primary Colour | Colour Picker | No | For branding (invoices, client portal) |
| 12 | Financial Year Start | Date (month/day) | Yes | Default: 06 April (UK tax year) |

### 7.2 Settings Sections

| Section | Description | Access |
|---------|-------------|--------|
| **Company Profile** | Name, logo, addresses, branding | Tenant Admin |
| **User Management** | Invite, edit, deactivate users | Tenant Admin |
| **Services** | Manage master services list (add/remove/rename/reorder) | Tenant Admin, Partner |
| **Lookup Data** | Manage dropdowns: action statuses, client types, tax offices, SIC codes, titles, etc. | Tenant Admin, Partner |
| **Breakdown Templates** | Create/edit task breakdown checklists | Tenant Admin, Partner, Manager |
| **Notification Preferences** | Email notification settings (which events trigger emails) | Tenant Admin (global), each user (personal) |
| **Subscription & Billing** | View plan, upgrade/downgrade, payment method, invoices | Tenant Admin |
| **Data & Privacy** | Export data, delete account, GDPR settings | Tenant Admin |
| **Integrations** (future) | Companies House API, HMRC API, email, accounting software | Tenant Admin |
| **Appearance** | Dark/light mode, date format preferences | Each user (personal) |

---

## 8. Dashboard / Overview

The first screen after login. Provides a snapshot of the firm's key metrics and upcoming work.

### 8.1 Dashboard Widgets

| # | Widget | Description | Data Source |
|---|--------|-------------|-------------|
| 1 | **Overdue Tasks** | Count + list of tasks past their deadline | `tasks` where `deadline_date < today` |
| 2 | **Tasks Due This Week** | Tasks with deadline in the next 7 days | `tasks` filtered by date |
| 3 | **Tasks Due This Month** | Tasks with deadline in the next 30 days | `tasks` filtered by date |
| 4 | **My Tasks** | Tasks assigned to the logged-in user | `tasks.assignee_id = current_user` |
| 5 | **Unassigned Tasks** | Tasks with no assignee | `tasks.assignee_id IS NULL` |
| 6 | **Client Count** | Total active clients | `clients.is_active = 1` |
| 7 | **Recent Activity** | Latest actions across the firm (task updates, new clients, etc.) | `activity_log` |
| 8 | **Upcoming Deadlines Calendar** | Mini calendar view highlighting deadline dates | `tasks.deadline_date` |
| 9 | **Revenue Summary** (future) | Total invoiced, outstanding, overdue | `invoices` |

### 8.2 Dashboard Personalisation

| ID | Requirement |
|----|-------------|
| DASH-01 | Dashboard content shall be filtered based on user role (Staff sees only their tasks; Admin/Partner/Manager sees all). |
| DASH-02 | Widgets shall be collapsible / configurable in a future release. |

---

## 9. Navigation & Layout

### 9.1 Main Navigation Bar (Top)

Based on Bright Manager's navigation, the top nav bar shall include:

| # | Nav Item | Module | Icon |
|---|----------|--------|------|
| 1 | **Overview** | Dashboard | Home |
| 2 | **Tasks** | Task Management | Check-list |
| 3 | **Clients** | Client Management | Users |
| 4 | **Invoicing** | Invoicing (future) | Receipt |
| 5 | **Time** | Time Tracking (future) | Clock |
| 6 | **Resources** | Document Management (future) | Folder |
| 7 | **Tools** | Utility Tools (future) | Wrench |
| 8 | **Queue** | Work Queue (future) | Layers |
| 9 | **Settings** | Company & System Settings | Gear |

### 9.2 Header Bar

| Element | Position | Description |
|---------|----------|-------------|
| Company Logo | Top-left | Tenant's uploaded logo |
| Search Bar | Top-centre | Global search (see Section 20) |
| Notifications Bell | Top-right | Unread notification count + dropdown |
| User Avatar / Menu | Top-right | Profile, Settings, Logout |

### 9.3 Responsive Behaviour

| ID | Requirement |
|----|-------------|
| NAV-01 | Desktop-first design with responsive support for tablets. |
| NAV-02 | On smaller screens, navigation collapses to a hamburger menu. |
| NAV-03 | Critical actions (task list, client search) remain accessible on all screen sizes. |

---

## 10. Module Summary

| # | Module | Status | Requirements Doc |
|---|--------|--------|-----------------|
| 1 | **Client Management** | Detailed | `requirements-client-management.md` |
| 2 | **Task Management** | Detailed | `requirements-task-management.md` |
| 3 | **Invoicing** | Outlined below | Future detailed doc |
| 4 | **Time Tracking** | Outlined below | Future detailed doc |
| 5 | **Queue** | Outlined below | Future detailed doc |
| 6 | **Resources / Documents** | Outlined below | Future detailed doc |
| 7 | **Tools** | Outlined below | Future detailed doc |
| 8 | **Reporting** | Outlined below | Future detailed doc |

---

## 11. Module: Client Management

> **Full specification:** `docs/requirements/client-management.md`

**Summary:** Manage accounting firm clients (companies and individuals) with full UK statutory compliance tracking.

**Key Features:**
- Create, edit, soft-delete clients
- 14 collapsible detail sections: Required Info, Internal, Company Details, Main Contact, Services Required, Accounts & Returns, Confirmation Statement, VAT Details, PAYE Details, CIS, Auto-Enrolment, P11D, Registration
- 167 total fields across all sections
- Contact management (M:N — one person can be linked to multiple clients)
- Service assignment with per-service pricing and combined annual/monthly charges
- Full compliance tracking: deadlines, action statuses, progress notes
- Partner and Manager assignment per client
- Companies House autofill integration point

**Database:** 29 tables (13 lookup, 5 core, 3 services, 8 compliance)

---

## 12. Module: Task Management

> **Full specification:** `docs/requirements/task-management.md`

**Summary:** Automated task generation driven by client services, with full progress tracking, assignment, and compliance deadline management. Aligns with Bright Manager: **prospect clients** do not receive automated tasks until confirmed (see `task-management.md` §2.1).

**Key Features:**
- **Prospect gate:** no task sync while `clients.is_prospect` is true
- 14 task types auto-generated from enabled client services (non-prospect)
- Recurring task cycle: one active task at a time, auto-increments on completion
- Task edit form: assignee, monitor, notify on progress, time estimate, breakdown checklists, templates, description
- Target date (auto-calculated or manually set) and Deadline date (auto-populated or manual)
- On completion: client's next-due dates rolled forward, next period task generated
- Service toggle-off: task flagged as "Switched Off", manual delete required
- Task list with inline editing, sorting, filtering, bulk actions

---

## 13. Module: Invoicing

> **Status:** Phase 2 — High-level outline only.

### 13.1 Overview
Generate, send, and track invoices for client services.

### 13.2 Key Features (Planned)

| Feature | Description |
|---------|-------------|
| Invoice creation | Generate invoices from client services (annual/monthly charges) |
| Invoice templates | Customisable PDF templates with firm logo, address, branding |
| Line items | Individual service line items with descriptions and amounts |
| VAT calculation | Auto-calculate VAT on invoice totals |
| Invoice numbering | Auto-incrementing, configurable prefix (e.g., INV-001) |
| Invoice status | Draft → Sent → Viewed → Paid → Overdue → Void |
| Email delivery | Send invoices directly via email |
| Payment tracking | Record partial and full payments against invoices |
| Payment reminders | Automated reminders for overdue invoices |
| Recurring invoices | Auto-generate recurring invoices (monthly/quarterly/annual) |
| Credit notes | Issue credit notes against invoices |
| Ageing report | Outstanding invoice ageing analysis |
| Client portal | Clients can view and pay invoices online (future) |

---

## 14. Module: Time Tracking

> **Status:** Phase 2 — High-level outline only.

### 14.1 Overview
Log time spent working on clients and tasks.

### 14.2 Key Features (Planned)

| Feature | Description |
|---------|-------------|
| Time entry | Log time against a client and/or task |
| Timer | Start/stop timer for live tracking |
| Manual entry | Enter hours and description manually |
| Billable / Non-billable | Mark time as billable or internal |
| Hourly rates | Configurable rates per user, per client, or per service |
| Timesheet view | Weekly/daily timesheet grid per user |
| Approvals | Manager approval of timesheets |
| Invoice integration | Convert billable time entries into invoice line items |
| Reports | Time by client, by user, by service, by period |

---

## 15. Module: Queue

> **Status:** Phase 3 — High-level outline only.

### 15.1 Overview
Work queue for prioritising and managing workflow across the team.

### 15.2 Key Features (Planned)

| Feature | Description |
|---------|-------------|
| Priority queue | Ordered list of tasks/clients by urgency |
| Drag-and-drop ordering | Manually reorder queue items |
| Assignment from queue | Assign tasks to team members directly from the queue |
| Filters | Filter by task type, deadline, assignee |
| Capacity view | See each team member's workload at a glance |

---

## 16. Module: Resources / Documents

> **Status:** Phase 3 — High-level outline only.

### 16.1 Overview
Store and manage documents per client.

### 16.2 Key Features (Planned)

| Feature | Description |
|---------|-------------|
| File upload | Upload documents to a client's record |
| Folder structure | Organise files in folders per client (e.g., Tax Returns, Accounts, Correspondence) |
| File types | Support PDF, Word, Excel, images |
| Version history | Track file versions |
| Search | Search documents by name or content |
| Sharing | Share documents with clients via portal link (future) |
| Storage limits | Per-plan storage quotas |

---

## 17. Module: Tools

> **Status:** Phase 3 — High-level outline only.

### 17.1 Overview
Utility tools for common accounting tasks.

### 17.2 Potential Tools (Planned)

| Tool | Description |
|------|-------------|
| Tax calculator | Income tax, corporation tax, dividend tax calculations |
| VAT calculator | VAT forward/reverse calculations |
| Mileage calculator | HMRC-approved mileage rates |
| Deadline calculator | Calculate statutory deadlines from year-end dates |
| Letter/document templates | Generate engagement letters, 64-8 forms, etc. |
| Companies House lookup | Search and autofill company details |
| HMRC tax code lookup | Reference for tax codes |

---

## 18. Module: Reporting

> **Status:** Phase 2 — High-level outline only.

### 18.1 Overview
Dashboards and reports for firm management.

### 18.2 Key Reports (Planned)

| Report | Description |
|--------|-------------|
| Task status summary | Tasks by status, by type, overdue count |
| Deadline calendar | Calendar view of all upcoming deadlines |
| Client summary | Client list with service status, fees, last activity |
| Revenue report | Fees by client, by service, by period |
| Staff workload | Tasks per user, time logged, capacity |
| Compliance report | Filing status across all clients (submitted, pending, overdue) |
| Aged debtors | Outstanding invoices by age band |
| Activity report | Audit trail of actions by user and date |
| Export | All reports exportable to CSV, PDF, Excel |

---

## 19. Notifications System

### 19.1 Notification Types

| # | Event | Channel | Recipients |
|---|-------|---------|------------|
| 1 | Task assigned to you | In-app + Email | Assignee |
| 2 | Task deadline approaching (7 days) | In-app + Email | Assignee |
| 3 | Task overdue | In-app + Email | Assignee + Monitor |
| 4 | Task status changed | In-app + Email | Notify On Progress user |
| 5 | New client created | In-app | Manager + Partner |
| 6 | User invited to platform | Email | Invited user |
| 7 | Password reset requested | Email | User |
| 8 | Invoice sent | Email | Client contact |
| 9 | Invoice overdue | In-app + Email | Tenant Admin + Manager |
| 10 | Subscription expiring | In-app + Email | Tenant Admin |

### 19.2 Notification Preferences

| ID | Requirement |
|----|-------------|
| NOTIF-01 | Each user can configure which notifications they receive (in-app, email, or both). |
| NOTIF-02 | Tenant Admin can set firm-wide defaults for notification preferences. |
| NOTIF-03 | In-app notifications appear via the bell icon in the header with unread count. |
| NOTIF-04 | Notifications list shows: message, timestamp, read/unread status, link to related item. |

---

## 20. Global Search

| ID | Requirement |
|----|-------------|
| SRCH-01 | A search bar in the header shall allow searching across: client names, company numbers, UTR numbers, VAT numbers, contact names, task names. |
| SRCH-02 | Search results shall be grouped by category (Clients, Contacts, Tasks). |
| SRCH-03 | Results are scoped to the user's tenant and their permission level. |
| SRCH-04 | Minimum 2 characters to trigger search. Results appear in a dropdown overlay. |
| SRCH-05 | Clicking a result navigates to the relevant record. |

---

## 21. Audit Trail & Activity Log

| ID | Requirement |
|----|-------------|
| AUDIT-01 | The system shall log all significant actions: create, update, delete of clients, tasks, invoices, users. |
| AUDIT-02 | Each log entry records: timestamp, user, tenant, action type, entity type, entity ID, old value, new value. |
| AUDIT-03 | Audit logs are immutable (cannot be edited or deleted by any user). |
| AUDIT-04 | Tenant Admin can view the audit log, filtered by date range, user, entity type. |
| AUDIT-05 | Audit logs are retained for a minimum of 7 years (UK accounting regulatory requirement). |

---

## 22. Subscription & Billing

### 22.1 Subscription Plans

| Plan | Target | Clients | Users | Storage | Price (indicative) |
|------|--------|---------|-------|---------|-------------------|
| **Free Trial** | New sign-ups | Up to 5 | 1 | 500 MB | Free for 14 days |
| **Starter** | Small firms | Up to 50 | Up to 3 | 5 GB | £XX/month |
| **Professional** | Growing firms | Up to 250 | Up to 10 | 25 GB | £XX/month |
| **Enterprise** | Large firms | Unlimited | Unlimited | 100 GB | £XX/month |

### 22.2 Billing Features

| ID | Requirement |
|----|-------------|
| BILL-01 | Subscription managed via Stripe (or similar payment gateway). |
| BILL-02 | Support monthly and annual billing cycles (annual = discount). |
| BILL-03 | Tenant Admin can upgrade/downgrade plan at any time. Prorated billing. |
| BILL-04 | Automatic renewal with email reminders before charge. |
| BILL-05 | Failed payment retry with grace period (7 days). Account restricted after grace. |
| BILL-06 | Tenant Admin can view billing history and download invoices. |
| BILL-07 | Cancel subscription: data retained for 30 days, then permanently deleted. |

---

## 23. Data Management

### 23.1 Import

| ID | Requirement |
|----|-------------|
| DATA-01 | CSV import for bulk client creation (mapped fields with preview before import). |
| DATA-02 | Import validation: highlight errors (missing required fields, duplicate references) before committing. |

### 23.2 Export

| ID | Requirement |
|----|-------------|
| DATA-03 | Export client list to CSV / Excel. |
| DATA-04 | Export task list to CSV / Excel. |
| DATA-05 | Export reports to CSV / PDF. |
| DATA-06 | Full data export (GDPR compliance) — all tenant data in structured format. |

### 23.3 Backup & Recovery

| ID | Requirement |
|----|-------------|
| DATA-07 | Automated daily database backups with 30-day retention. |
| DATA-08 | Point-in-time recovery capability. |

---

## 24. Non-Functional Requirements

### 24.1 Performance

| ID | Requirement |
|----|-------------|
| NFR-01 | Page load time < 2 seconds for standard views (client list, task list). |
| NFR-02 | Search results returned within 500ms. |
| NFR-03 | Support 100+ concurrent users per tenant without degradation. |
| NFR-04 | Database queries optimised with appropriate indexes. |

### 24.2 Availability

| ID | Requirement |
|----|-------------|
| NFR-05 | 99.9% uptime SLA (excluding planned maintenance). |
| NFR-06 | Planned maintenance windows communicated 48 hours in advance. |

### 24.3 Scalability

| ID | Requirement |
|----|-------------|
| NFR-07 | Horizontal scaling capability for web servers. |
| NFR-08 | Database read replicas for reporting queries. |

### 24.4 Compliance

| ID | Requirement |
|----|-------------|
| NFR-09 | GDPR compliant: data processing agreements, right to access, right to erasure. |
| NFR-10 | Data stored within UK/EU data centres. |
| NFR-11 | ICO registration as data processor. |

### 24.5 Browser Support

| Browser | Minimum Version |
|---------|----------------|
| Chrome | Latest 2 versions |
| Firefox | Latest 2 versions |
| Safari | Latest 2 versions |
| Edge | Latest 2 versions |

---

## 25. Technology Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| **Database** | MySQL 8.x | Multi-tenant with `tenant_id` |
| **Backend** | To be determined | PHP (Laravel), Node.js (Express/NestJS), or similar |
| **Frontend** | To be determined | React, Vue.js, or similar SPA framework |
| **Authentication** | JWT or Session-based | With 2FA support |
| **File Storage** | Cloud storage (S3-compatible) | For logos, documents, uploads |
| **Email** | Transactional email service | SendGrid, Mailgun, or SES |
| **Payments** | Stripe | Subscription billing and card payments |
| **Hosting** | Cloud (AWS, GCP, or Azure) | UK/EU region |
| **CI/CD** | To be determined | Automated testing and deployment pipeline |

---

## Document Index

| Document | Path | Description |
|----------|------|-------------|
| **Main Requirements** (this doc) | `docs/requirements/main.md` | SaaS platform, auth, onboarding, all modules overview |
| **Client Management** | `docs/requirements/client-management.md` | Detailed client module (167 fields, 29 DB tables) |
| **Task Management** | `docs/requirements/task-management.md` | Detailed task module (14 task types, edit form, completion rules) |
| **Database Structure** | `docs/database/structure.md` | MySQL schema (35 tables — client + task management) |
| **Technology Stack** | `docs/tech-stack/technology-stack.md` | Full tech stack, architecture, libraries, deployment |
| **Pending Questions** | `docs/pending/questions.md` | Open questions awaiting answers |

---

*End of Document*
