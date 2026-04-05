# Divinne Accountancy Software — Database Structure Document

**Modules:** Client Management, Task Management
**Database Engine:** MySQL
**Version:** 2.0
**Date:** 26 March 2026

---

## Table of Contents

1. [Overview](#1-overview)
2. [Entity Relationship Summary](#2-entity-relationship-summary)
3. [Lookup / Reference Tables](#3-lookup--reference-tables)
4. [Core Tables](#4-core-tables)
5. [Service & Pricing Tables](#5-service--pricing-tables)
6. [Compliance & Tax Detail Tables](#6-compliance--tax-detail-tables)
7. [Task Management Tables](#7-task-management-tables)
8. [Indexes & Constraints Summary](#8-indexes--constraints-summary)

---

## 1. Overview

This document defines the MySQL database schema for the **Client Management** and **Task Management** modules of the Divinne Accountancy Software, inspired by Bright Manager. The schema is fully normalised (3NF) and designed for:

- Managing accounting firm clients (companies and individuals)
- Tracking company statutory details (Companies House, HMRC, VAT, PAYE)
- Recording services provided and their pricing
- Maintaining contacts/persons linked to clients
- Tracking compliance deadlines and progress
- Auto-generated tasks driven by enabled client services
- Task assignment, monitoring, notifications, and breakdown checklists
- Task completion with client date rollover and recurring task generation

---

## 2. Entity Relationship Summary

```
users (staff)
  │
  ├──< clients (partner_id, manager_id)
  │       │
  │       │  ── CLIENT DETAIL TABLES ──
  │       ├──── company_details          (1:1)
  │       ├──── client_services          (1:N) ──> services
  │       ├──── client_combined_pricing  (1:1)
  │       ├──── accounts_returns         (1:1)
  │       ├──── confirmation_statements  (1:1)
  │       ├──── vat_details              (1:1)
  │       ├──── paye_details             (1:1)
  │       ├──── cis_details              (1:1)
  │       ├──── auto_enrolment           (1:1)
  │       ├──── p11d_details             (1:1)
  │       ├──── registration             (1:1)
  │       ├──── client_contacts          (M:N) ──> contacts
  │       │
  │       │  ── TASK TABLES ──
  │       └──── tasks                    (1:N)
  │               ├──── task_breakdown_items     (1:N)
  │               ├── assignee_id        ──> users
  │               ├── monitor_id         ──> users
  │               ├── notify_user_id     ──> users
  │               ├── task_type_id       ──> lkp_task_types
  │               ├── latest_action_id   ──> lkp_action_statuses
  │               ├── service_id         ──> services
  │               └── breakdown_template_id ──> breakdown_templates
  │
  │  ── TASK SUPPORT TABLES ──
  │  breakdown_templates
  │       └──── breakdown_template_items  (1:N)
  │
  lookup tables: client_types, titles, sic_codes, tax_offices,
                 nationalities, marital_statuses, languages,
                 action_statuses, vat_frequencies, paye_frequencies,
                 flat_rate_categories, company_statuses,
                 task_types, task_recurrences
```

---

## 3. Lookup / Reference Tables

These small tables store dropdown/select values used throughout the application.

### 3.1 `lkp_client_types`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL, UNIQUE | e.g. Private Limited Company, Sole Trader, Partnership, LLP |
| is_active | TINYINT(1) | DEFAULT 1 | Soft-disable for dropdowns |

### 3.2 `lkp_company_statuses`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL, UNIQUE | e.g. Active, Dormant, Dissolved, In Liquidation |

### 3.3 `lkp_titles`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(20) | NOT NULL, UNIQUE | Mr, Mrs, Ms, Miss, Dr, etc. |

### 3.4 `lkp_sic_codes`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| code | VARCHAR(10) | NOT NULL, UNIQUE | SIC code value |
| description | VARCHAR(255) | NOT NULL | Human-readable description |

### 3.5 `lkp_tax_offices`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(150) | NOT NULL, UNIQUE | HMRC tax office name |

### 3.6 `lkp_nationalities`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL, UNIQUE | e.g. British, Irish, Pakistani |

### 3.7 `lkp_marital_statuses`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(50) | NOT NULL, UNIQUE | Single, Married, Divorced, Widowed, Civil Partnership |

### 3.8 `lkp_languages`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(50) | NOT NULL, UNIQUE | English, Urdu, Punjabi, etc. |

### 3.9 `lkp_action_statuses`

Shared lookup for "Latest Action" dropdowns across all sections.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL, UNIQUE | e.g. Not Started, In Progress, Awaiting Info, Submitted, Completed |
| category | VARCHAR(50) | NULL | Optional grouping: accounts, vat, paye, confirmation, p11d, auto_enrolment |

### 3.10 `lkp_vat_frequencies`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(50) | NOT NULL, UNIQUE | Quarterly, Monthly, Annual |

### 3.11 `lkp_paye_frequencies`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(50) | NOT NULL, UNIQUE | Weekly, Monthly, Fortnightly, Four-Weekly |

### 3.12 `lkp_flat_rate_categories`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(150) | NOT NULL, UNIQUE | HMRC flat rate scheme category |
| rate | DECIMAL(5,2) | NULL | Percentage rate |

### 3.13 `lkp_vat_member_states`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL, UNIQUE | EU/UK member state name |
| code | VARCHAR(5) | NOT NULL, UNIQUE | Country code |

---

## 4. Core Tables

### 4.1 `users`

System staff members (accountants, managers, partners).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| first_name | VARCHAR(100) | NOT NULL | |
| last_name | VARCHAR(100) | NOT NULL | |
| email | VARCHAR(255) | NOT NULL, UNIQUE | |
| password_hash | VARCHAR(255) | NOT NULL | Hashed password |
| role | ENUM('admin','partner','manager','staff') | NOT NULL, DEFAULT 'staff' | |
| is_active | TINYINT(1) | DEFAULT 1 | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 4.2 `clients`

The central entity — one row per client (company or individual).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| internal_reference | VARCHAR(50) | UNIQUE | Custom internal reference code |
| name | VARCHAR(255) | NOT NULL | Client / company display name |
| client_type_id | INT UNSIGNED | FK → lkp_client_types.id, NOT NULL | Private Limited Company, Sole Trader, etc. |
| partner_id | INT UNSIGNED | FK → users.id, NULL | Assigned partner |
| manager_id | INT UNSIGNED | FK → users.id, NULL | Assigned manager |
| credit_check_completed | TINYINT(1) | DEFAULT 0 | Has credit check been done? |
| credit_check_date | DATE | NULL | Date credit check was completed |
| is_active | TINYINT(1) | DEFAULT 1 | Soft delete |
| income_details | TEXT | NULL | Optional onboarding notes (Bright Manager) |
| previous_accountant_name | VARCHAR(255) | NULL | |
| previous_accountant_details | TEXT | NULL | |
| other_details | TEXT | NULL | |
| is_prospect | TINYINT(1) | DEFAULT 0 | If true, automated tasks are not generated (Bright Manager prospect) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 4.3 `company_details`

One-to-one with `clients`. Stores Companies House and statutory company information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| company_number | VARCHAR(20) | NULL | Companies House number |
| company_status_id | INT UNSIGNED | FK → lkp_company_statuses.id, NULL | Active, Dormant, etc. |
| incorporation_date | DATE | NULL | |
| trading_as | VARCHAR(255) | NULL | Trading name if different |
| registered_address | TEXT | NULL | |
| postal_address | TEXT | NULL | |
| invoice_address_type | ENUM('registered','postal','custom') | DEFAULT 'postal' | Which address to use for invoices |
| invoice_address_custom | TEXT | NULL | Only if type = 'custom' |
| primary_email | VARCHAR(255) | NULL | |
| email_domain | VARCHAR(255) | NULL | |
| telephone | VARCHAR(30) | NULL | |
| turnover | DECIMAL(15,2) | NULL | Annual turnover in £ |
| date_of_trading | DATE | NULL | |
| sic_code_id | INT UNSIGNED | FK → lkp_sic_codes.id, NULL | |
| nature_of_business | VARCHAR(255) | NULL | |
| corporation_tax_office | VARCHAR(150) | NULL | |
| company_utr | VARCHAR(20) | NULL | Unique Taxpayer Reference |
| companies_house_auth_code | VARCHAR(20) | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 4.4 `contacts`

A person who can be linked to one or more clients. Reusable across clients.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| title_id | INT UNSIGNED | FK → lkp_titles.id, NULL | |
| first_name | VARCHAR(100) | NOT NULL | |
| middle_name | VARCHAR(100) | NULL | |
| last_name | VARCHAR(100) | NOT NULL | |
| preferred_name | VARCHAR(100) | NULL | |
| date_of_birth | DATE | NULL | |
| deceased_date | DATE | NULL | |
| email | VARCHAR(255) | NULL | |
| portal_login_email | VARCHAR(255) | NULL | |
| postal_address | TEXT | NULL | |
| previous_address | TEXT | NULL | |
| telephone_number | VARCHAR(30) | NULL | |
| mobile_number | VARCHAR(30) | NULL | |
| ni_number | VARCHAR(15) | NULL | National Insurance number |
| personal_utr | VARCHAR(20) | NULL | Personal Unique Taxpayer Reference |
| companies_house_personal_code | VARCHAR(20) | NULL | |
| terms_signed_date | DATE | NULL | |
| photo_id_verified | TINYINT(1) | DEFAULT 0 | |
| address_verified | TINYINT(1) | DEFAULT 0 | |
| marital_status_id | INT UNSIGNED | FK → lkp_marital_statuses.id, NULL | |
| nationality_id | INT UNSIGNED | FK → lkp_nationalities.id, NULL | |
| language_id | INT UNSIGNED | FK → lkp_languages.id, NULL | |
| aml_check_started | TINYINT(1) | DEFAULT 0 | |
| aml_check_date | DATE | NULL | |
| id_check_started | TINYINT(1) | DEFAULT 0 | |
| id_check_date | DATE | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 4.5 `client_contacts`

Many-to-many link between clients and contacts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, NOT NULL | |
| contact_id | INT UNSIGNED | FK → contacts.id, NOT NULL | |
| is_main_contact | TINYINT(1) | DEFAULT 0 | Is this the primary contact? |
| create_self_assessment | TINYINT(1) | DEFAULT 0 | Create SA client for this person? |
| self_assessment_fee | DECIMAL(10,2) | NULL | SA fee in £ |
| client_does_own_sa | TINYINT(1) | DEFAULT 0 | Client does their own Self Assessment |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |

**UNIQUE:** (`client_id`, `contact_id`)

---

## 5. Service & Pricing Tables

### 5.1 `services`

Master list of services the firm offers.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL, UNIQUE | |
| slug | VARCHAR(100) | NOT NULL, UNIQUE | URL/code-friendly key |
| display_order | INT | DEFAULT 0 | Sort order in UI |
| is_active | TINYINT(1) | DEFAULT 1 | |

**Seed data:**

| slug | name |
|------|------|
| accounts | Accounts |
| bookkeeping | Bookkeeping |
| ct600_return | CT600 Return |
| payroll | Payroll |
| auto_enrolment | Auto-Enrolment |
| vat_returns | VAT Returns |
| management_accounts | Management Accounts |
| confirmation_statement | Confirmation Statement |
| cis | CIS |
| p11d | P11D |
| fee_protection | Fee Protection Service |
| registered_address | Registered Address |
| bill_payment | Bill Payment |
| consultation_advice | Consultation/Advice |
| software | Software |

---

### 5.2 `client_services`

Which services each client has, with individual pricing.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, NOT NULL | |
| service_id | INT UNSIGNED | FK → services.id, NOT NULL | |
| is_enabled | TINYINT(1) | DEFAULT 1 | Toggle on/off |
| fee | DECIMAL(10,2) | NULL | Service fee in £ |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

**UNIQUE:** (`client_id`, `service_id`)

---

### 5.3 `client_combined_pricing`

Combined annual/monthly charge for a client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| annual_charge_enabled | TINYINT(1) | DEFAULT 0 | |
| annual_charge | DECIMAL(10,2) | NULL | Total annual fee in £ |
| monthly_charge_enabled | TINYINT(1) | DEFAULT 0 | |
| monthly_charge | DECIMAL(10,2) | NULL | Total monthly fee in £ |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

## 6. Compliance & Tax Detail Tables

### 6.1 `accounts_returns`

Accounts and Returns tracking — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| accounts_period_end | DATE | NULL | |
| ch_year_end | DATE | NULL | Companies House year end |
| hmrc_year_end | DATE | NULL | HMRC year end |
| ch_accounts_next_due | DATE | NULL | |
| ct600_due | DATE | NULL | |
| corporation_tax_amount_due | DECIMAL(12,2) | NULL | £ |
| tax_due_hmrc_year_end | DATE | NULL | |
| ct_payment_reference | VARCHAR(50) | NULL | [UTR] + code format |
| tax_office_id | INT UNSIGNED | FK → lkp_tax_offices.id, NULL | |
| ch_email_reminder | TINYINT(1) | DEFAULT 0 | |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | |
| latest_action_date | DATE | NULL | |
| records_received | DATE | NULL | |
| progress_note | TEXT | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 6.2 `confirmation_statements`

Confirmation Statement details — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| statement_date | DATE | NULL | |
| statement_due | DATE | NULL | |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | |
| latest_action_date | DATE | NULL | |
| records_received | DATE | NULL | |
| progress_note | TEXT | NULL | |
| officers | TEXT | NULL | |
| share_capital | TEXT | NULL | |
| shareholders | TEXT | NULL | |
| people_with_significant_control | TEXT | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 6.3 `vat_details`

VAT registration and compliance details — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| vat_frequency_id | INT UNSIGNED | FK → lkp_vat_frequencies.id, NULL | |
| vat_period_end | DATE | NULL | |
| next_return_due | DATE | NULL | |
| vat_bill_amount | DECIMAL(12,2) | NULL | £ |
| vat_bill_due | DATE | NULL | |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | |
| latest_action_date | DATE | NULL | |
| records_received | DATE | NULL | |
| progress_note | TEXT | NULL | |
| vat_member_state_id | INT UNSIGNED | FK → lkp_vat_member_states.id, NULL | |
| vat_number | VARCHAR(20) | NULL | |
| vat_address | TEXT | NULL | |
| date_of_registration | DATE | NULL | |
| effective_date | DATE | NULL | |
| estimated_turnover | DECIMAL(15,2) | NULL | £ |
| applied_for_mtd | DATE | NULL | Making Tax Digital application date |
| mtd_ready | TINYINT(1) | DEFAULT 0 | |
| transfer_of_going_concern | TINYINT(1) | DEFAULT 0 | |
| involved_in_other_businesses | TINYINT(1) | DEFAULT 0 | |
| direct_debit | TINYINT(1) | DEFAULT 0 | |
| standard_scheme | TINYINT(1) | DEFAULT 0 | |
| cash_accounting_scheme | TINYINT(1) | DEFAULT 0 | |
| retail_scheme | TINYINT(1) | DEFAULT 0 | |
| margin_scheme | TINYINT(1) | DEFAULT 0 | |
| flat_rate | TINYINT(1) | DEFAULT 0 | |
| flat_rate_category_id | INT UNSIGNED | FK → lkp_flat_rate_categories.id, NULL | |
| month_last_quarter_submitted | TINYINT UNSIGNED | NULL | 1-12 |
| box5_last_quarter_submitted | DECIMAL(12,2) | NULL | |
| general_notes | TEXT | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 6.4 `paye_details`

PAYE scheme details — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| employers_reference | VARCHAR(50) | NULL | PAYE employer ref |
| accounts_office_reference | VARCHAR(50) | NULL | |
| years_required | VARCHAR(20) | NULL | e.g. "2025/26" |
| paye_frequency_id | INT UNSIGNED | FK → lkp_paye_frequencies.id, NULL | |
| irregular_monthly_pay | TINYINT(1) | DEFAULT 0 | |
| nil_eps | TINYINT(1) | DEFAULT 0 | |
| no_of_employees | INT UNSIGNED | NULL | |
| salary_details | TEXT | NULL | |
| first_pay_date | DATE | NULL | |
| rti_deadline | DATE | NULL | |
| paye_scheme_ceased | DATE | NULL | |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | |
| latest_action_date | DATE | NULL | |
| records_received | DATE | NULL | |
| progress_note | TEXT | NULL | |
| general_notes | TEXT | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 6.5 `cis_details`

CIS (Construction Industry Scheme) details — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| is_contractor | TINYINT(1) | DEFAULT 0 | CIS Contractor toggle |
| is_subcontractor | TINYINT(1) | DEFAULT 0 | CIS Subcontractor toggle |
| cis_date | DATE | NULL | CIS period date |
| cis_deadline | DATE | NULL | CIS filing deadline |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | |
| latest_action_date | DATE | NULL | |
| records_received | DATE | NULL | |
| progress_note | TEXT | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 6.6 `auto_enrolment`

Auto-Enrolment pension details — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | |
| latest_action_date | DATE | NULL | |
| records_received | DATE | NULL | |
| progress_note | TEXT | NULL | |
| staging_date | DATE | NULL | |
| postponement_date | DATE | NULL | |
| pensions_regulator_opt_out_date | DATE | NULL | |
| re_enrolment_date | DATE | NULL | |
| pension_provider | VARCHAR(150) | NULL | |
| pension_id | VARCHAR(50) | NULL | |
| declaration_of_compliance_due | DATE | NULL | |
| declaration_of_compliance_submission | DATE | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 6.7 `p11d_details`

P11D (Benefits in Kind) tracking — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| next_return_due | DATE | NULL | |
| latest_submitted | DATE | NULL | |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | |
| latest_action_date | DATE | NULL | |
| records_received | DATE | NULL | |
| progress_note | TEXT | NULL | |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 6.8 `registration`

Client registration / onboarding compliance — one per client.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, UNIQUE, NOT NULL | |
| terms_signed_fee_paid | TINYINT(1) | DEFAULT 0 | |
| registration_fee | DECIMAL(10,2) | NULL | £ |
| letter_of_engagement_signed | DATE | NULL | |
| money_laundering_complete | TINYINT(1) | DEFAULT 0 | |
| sixty_four_eight_registration | DATE | NULL | 64-8 agent authorisation date |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

## 7. Task Management Tables

### 7.1 `lkp_task_types`

Lookup table defining all task types that can be auto-generated from services.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL, UNIQUE | Display name (e.g., Accounts Preparation, CT600 Submission) |
| slug | VARCHAR(100) | NOT NULL, UNIQUE | Code-friendly key (e.g., accounts_preparation, ct600_submission) |
| naming_pattern | VARCHAR(255) | NOT NULL | Template pattern (e.g., `Accounts Preparation Year End {date}`) |
| service_id | INT UNSIGNED | FK → services.id, NULL | Which service triggers this task type. NULL for special types. |
| recurrence | ENUM('annual','quarterly','monthly','per_paye_frequency','one_off') | NOT NULL | How often the task recurs |
| deadline_source | VARCHAR(255) | NULL | DB field path for auto-populated deadline (e.g., `accounts_returns.accounts_period_end`). NULL = manually entered. |
| deadline_manual | TINYINT(1) | DEFAULT 0 | 1 = deadline must be manually entered by user |
| display_order | INT | DEFAULT 0 | Sort order in UI |
| is_active | TINYINT(1) | DEFAULT 1 | |

**Seed data:**

| slug | name | service (slug) | recurrence | deadline_manual |
|------|------|---------------|------------|:---:|
| accounts_preparation | Accounts Preparation | accounts | annual | 0 |
| ch_submission | Companies House Submission | accounts | annual | 0 |
| ct600_submission | CT600 Submission | ct600_return | annual | 0 |
| confirmation_statement | Confirmation Statement | confirmation_statement | annual | 0 |
| vat_submission | VAT Submission | vat_returns | quarterly | 1 |
| vat_preparation | VAT Preparation | vat_returns | quarterly | 1 |
| paye | PAYE | payroll | per_paye_frequency | 1 |
| cis | CIS | cis | monthly | 0 |
| auto_enrolment | Auto-Enrolment | auto_enrolment | one_off | 0 |
| p11d | P11D Submission | p11d | annual | 0 |
| bookkeeping | Bookkeeping | bookkeeping | annual | 0 |
| management_accounts | Management Accounts | management_accounts | annual | 0 |
| self_assessment | Self Assessment | NULL | annual | 1 |
| pension | Pension | auto_enrolment | annual | 1 |

---

### 7.2 `tasks`

The main task table. One row per task instance.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| client_id | INT UNSIGNED | FK → clients.id, NOT NULL | The client this task belongs to |
| task_type_id | INT UNSIGNED | FK → lkp_task_types.id, NOT NULL | Type of task |
| service_id | INT UNSIGNED | FK → services.id, NULL | The specific service that triggered this task |
| task_name | VARCHAR(255) | NOT NULL | Auto-generated from pattern; editable by user |
| status | ENUM('active','completed','switched_off','deleted') | NOT NULL, DEFAULT 'active' | Current task lifecycle status |
| assignee_id | INT UNSIGNED | FK → users.id, NULL | User assigned to complete the task |
| monitor_id | INT UNSIGNED | FK → users.id, NULL | User monitoring/overseeing the task |
| notify_user_id | INT UNSIGNED | FK → users.id, NULL | User to notify on progress changes |
| latest_action_id | INT UNSIGNED | FK → lkp_action_statuses.id, NULL | Current action status (e.g., Records Requested) |
| latest_action_date | DATE | NULL | Date the latest action was set |
| target_date | DATE | NULL | Firm's internal goal date |
| target_date_manual | TINYINT(1) | DEFAULT 0 | 0 = auto-calculated, 1 = manually set by user |
| deadline_date | DATE | NULL | Statutory/external filing deadline |
| period_date | DATE | NULL | The compliance period date this task relates to (used for incrementing) |
| time_estimate | DECIMAL(5,2) | NULL | Estimated hours (e.g., 0.50, 2.00) |
| progress_notes | TEXT | NULL | Internal notes about task progress |
| description | TEXT | NULL | Free-text task description |
| breakdown_template_id | INT UNSIGNED | FK → breakdown_templates.id, NULL | Template used for task breakdown checklist |
| is_favourite | TINYINT(1) | DEFAULT 0 | Starred/favourite for quick access |
| completed_at | TIMESTAMP | NULL | When the task was marked complete |
| completed_by | INT UNSIGNED | FK → users.id, NULL | Who marked it complete |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

**Key columns explained:**

- `status`: Controls task lifecycle visibility
  - `active` — normal working task, visible in main list
  - `completed` — archived after completion; hidden from active list
  - `switched_off` — service was toggled OFF; shows with highlighted Delete button
  - `deleted` — soft-deleted (or use hard delete depending on policy)
- `period_date`: The compliance date the task relates to (e.g., year end date 30/06/2025). Used to calculate the next period's date when task is completed.
- `target_date_manual`: When 0, target_date is auto-calculated by the system. When 1, the user has manually overridden the target date.

---

### 7.3 `task_breakdown_items`

Individual checklist items for a task. Each row = one checkbox line.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| task_id | INT UNSIGNED | FK → tasks.id, NOT NULL | Parent task |
| sort_order | INT UNSIGNED | NOT NULL, DEFAULT 0 | Display order of checklist items |
| description | VARCHAR(500) | NOT NULL | The checklist item text |
| is_completed | TINYINT(1) | DEFAULT 0 | Has this item been ticked off? |
| completed_at | TIMESTAMP | NULL | When it was ticked |
| completed_by | INT UNSIGNED | FK → users.id, NULL | Who ticked it |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |

---

### 7.4 `breakdown_templates`

Reusable checklist templates that can be applied to tasks.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(150) | NOT NULL, UNIQUE | Template name (e.g., "Standard Accounts Checklist") |
| description | VARCHAR(500) | NULL | Brief description of the template |
| task_type_id | INT UNSIGNED | FK → lkp_task_types.id, NULL | Optionally linked to a specific task type for filtering. NULL = available for all types. |
| is_active | TINYINT(1) | DEFAULT 1 | |
| created_by | INT UNSIGNED | FK → users.id, NULL | User who created the template |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | |

---

### 7.5 `breakdown_template_items`

Individual checklist items within a template.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| template_id | INT UNSIGNED | FK → breakdown_templates.id, NOT NULL | Parent template |
| sort_order | INT UNSIGNED | NOT NULL, DEFAULT 0 | Display order |
| description | VARCHAR(500) | NOT NULL | The checklist item text |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |

---

### 7.6 `task_history`

Audit log of status changes on tasks (tracks when Latest Action changes, assignments change, etc.).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | |
| task_id | INT UNSIGNED | FK → tasks.id, NOT NULL | |
| field_changed | VARCHAR(50) | NOT NULL | Which field changed (e.g., 'latest_action_id', 'assignee_id', 'status') |
| old_value | VARCHAR(255) | NULL | Previous value (as string) |
| new_value | VARCHAR(255) | NULL | New value (as string) |
| changed_by | INT UNSIGNED | FK → users.id, NULL | User who made the change |
| changed_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | |

---

## 8. Indexes & Constraints Summary

### Primary Keys
Every table uses `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`.

### Foreign Keys

| Child Table | Column | References |
|-------------|--------|------------|
| clients | client_type_id | lkp_client_types.id |
| clients | partner_id | users.id |
| clients | manager_id | users.id |
| company_details | client_id | clients.id |
| company_details | company_status_id | lkp_company_statuses.id |
| company_details | sic_code_id | lkp_sic_codes.id |
| contacts | title_id | lkp_titles.id |
| contacts | marital_status_id | lkp_marital_statuses.id |
| contacts | nationality_id | lkp_nationalities.id |
| contacts | language_id | lkp_languages.id |
| client_contacts | client_id | clients.id |
| client_contacts | contact_id | contacts.id |
| client_services | client_id | clients.id |
| client_services | service_id | services.id |
| client_combined_pricing | client_id | clients.id |
| accounts_returns | client_id | clients.id |
| accounts_returns | tax_office_id | lkp_tax_offices.id |
| accounts_returns | latest_action_id | lkp_action_statuses.id |
| confirmation_statements | client_id | clients.id |
| confirmation_statements | latest_action_id | lkp_action_statuses.id |
| vat_details | client_id | clients.id |
| vat_details | vat_frequency_id | lkp_vat_frequencies.id |
| vat_details | latest_action_id | lkp_action_statuses.id |
| vat_details | vat_member_state_id | lkp_vat_member_states.id |
| vat_details | flat_rate_category_id | lkp_flat_rate_categories.id |
| paye_details | client_id | clients.id |
| paye_details | paye_frequency_id | lkp_paye_frequencies.id |
| paye_details | latest_action_id | lkp_action_statuses.id |
| cis_details | client_id | clients.id |
| cis_details | latest_action_id | lkp_action_statuses.id |
| auto_enrolment | client_id | clients.id |
| auto_enrolment | latest_action_id | lkp_action_statuses.id |
| p11d_details | client_id | clients.id |
| p11d_details | latest_action_id | lkp_action_statuses.id |
| registration | client_id | clients.id |
| lkp_task_types | service_id | services.id |
| tasks | client_id | clients.id |
| tasks | task_type_id | lkp_task_types.id |
| tasks | service_id | services.id |
| tasks | assignee_id | users.id |
| tasks | monitor_id | users.id |
| tasks | notify_user_id | users.id |
| tasks | latest_action_id | lkp_action_statuses.id |
| tasks | breakdown_template_id | breakdown_templates.id |
| tasks | completed_by | users.id |
| task_breakdown_items | task_id | tasks.id |
| task_breakdown_items | completed_by | users.id |
| breakdown_templates | task_type_id | lkp_task_types.id |
| breakdown_templates | created_by | users.id |
| breakdown_template_items | template_id | breakdown_templates.id |
| task_history | task_id | tasks.id |
| task_history | changed_by | users.id |

### Recommended Indexes

#### Client Management Indexes

| Table | Index Columns | Type | Reason |
|-------|---------------|------|--------|
| clients | internal_reference | UNIQUE | Fast lookup by reference |
| clients | name | INDEX | Search by name |
| clients | client_type_id | INDEX | Filter by type |
| clients | partner_id | INDEX | Filter by partner |
| clients | manager_id | INDEX | Filter by manager |
| company_details | company_number | INDEX | Search by company number |
| company_details | company_utr | INDEX | Search by UTR |
| contacts | last_name, first_name | INDEX | Name search |
| contacts | email | INDEX | Email lookup |
| contacts | ni_number | INDEX | NI number lookup |
| contacts | personal_utr | INDEX | UTR lookup |
| client_contacts | client_id, contact_id | UNIQUE | Prevent duplicates |
| client_services | client_id, service_id | UNIQUE | Prevent duplicates |
| vat_details | vat_number | INDEX | VAT number search |
| paye_details | employers_reference | INDEX | PAYE ref search |

#### Task Management Indexes

| Table | Index Columns | Type | Reason |
|-------|---------------|------|--------|
| tasks | client_id | INDEX | All tasks for a client |
| tasks | client_id, task_type_id | INDEX | Filter tasks by type per client |
| tasks | assignee_id | INDEX | "My Tasks" / filter by assignee |
| tasks | monitor_id | INDEX | Filter by monitor |
| tasks | status | INDEX | Filter active/completed/switched_off |
| tasks | deadline_date | INDEX | Sort/filter by deadline |
| tasks | target_date | INDEX | Sort/filter by target |
| tasks | status, deadline_date | INDEX | Overdue tasks query (active + past deadline) |
| tasks | status, assignee_id | INDEX | Active tasks per user |
| tasks | is_favourite | INDEX | Starred tasks filter |
| task_breakdown_items | task_id, sort_order | INDEX | Ordered checklist per task |
| breakdown_template_items | template_id, sort_order | INDEX | Ordered items per template |
| task_history | task_id, changed_at | INDEX | History timeline per task |

### ON DELETE Behaviour

| Relationship | Rule |
|-------------|------|
| clients → child detail tables | CASCADE (delete client removes all related records) |
| clients → tasks | CASCADE (delete client removes all tasks) |
| tasks → task_breakdown_items | CASCADE (delete task removes checklist items) |
| tasks → task_history | CASCADE (delete task removes history) |
| breakdown_templates → breakdown_template_items | CASCADE (delete template removes items) |
| lookup tables → referencing columns | RESTRICT (cannot delete lookup value in use) |
| users → clients.partner_id / manager_id | SET NULL (if user deleted, unassign) |
| users → tasks.assignee_id / monitor_id / notify_user_id | SET NULL (if user deleted, unassign) |

---

## Appendix: Table Count Summary

| Category | Tables | Count |
|----------|--------|-------|
| Lookup/Reference | lkp_client_types, lkp_company_statuses, lkp_titles, lkp_sic_codes, lkp_tax_offices, lkp_nationalities, lkp_marital_statuses, lkp_languages, lkp_action_statuses, lkp_vat_frequencies, lkp_paye_frequencies, lkp_flat_rate_categories, lkp_vat_member_states, lkp_task_types | 14 |
| Core | users, clients, company_details, contacts, client_contacts | 5 |
| Services | services, client_services, client_combined_pricing | 3 |
| Compliance | accounts_returns, confirmation_statements, vat_details, paye_details, cis_details, auto_enrolment, p11d_details, registration | 8 |
| Task Management | tasks, task_breakdown_items, breakdown_templates, breakdown_template_items, task_history | 5 |
| **Total** | | **35** |
