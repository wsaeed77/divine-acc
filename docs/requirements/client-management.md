# Divinne Accountancy Software — Requirements Document

**Module:** Client Management
**Version:** 1.1
**Date:** 5 April 2026

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Scope](#2-scope)
3. [Functional Requirements](#3-functional-requirements)
4. [Data Requirements](#4-data-requirements)
5. [Business Rules & Validation](#5-business-rules--validation)
6. [User Roles & Permissions](#6-user-roles--permissions)
7. [UI Sections & Field Mapping](#7-ui-sections--field-mapping)
8. [Bright Manager parity & UI behaviour](#8-bright-manager-parity--ui-behaviour)
9. [Future Modules (Out of Scope)](#9-future-modules-out-of-scope)

---

## 1. Project Overview

Divinne Accountancy Software is a practice management system for accounting firms, inspired by Bright Manager. It enables firms to manage their client base, track compliance deadlines, assign services with pricing, and maintain full contact and company statutory records.

**Technology Stack:**
- **Database:** MySQL
- **Architecture:** To be determined (web-based application)

---

## 2. Scope

This document covers **Client Management** only. The following is in scope:

- Create, read, update, and soft-delete clients
- Manage company statutory details (Companies House, HMRC)
- Manage contacts/persons linked to clients
- Assign and price services per client
- Track compliance details: Accounts & Returns, Confirmation Statements, VAT, PAYE, Auto-Enrolment, P11D
- Record registration/onboarding compliance
- Assign partner and manager to each client
- Internal reference numbering

**Out of scope for this phase:** Task Management, Invoicing, Time Tracking, Reporting, Queue, Tools, Settings, Resource management.

---

## 3. Functional Requirements

### FR-01: Client CRUD Operations

| ID | Requirement |
|----|-------------|
| FR-01.1 | The system shall allow creating a new client with a name, type, and internal reference. |
| FR-01.2 | The system shall auto-generate an internal reference if not provided, following a configurable pattern. |
| FR-01.3 | The system shall allow editing all client fields after creation. |
| FR-01.4 | The system shall support soft-deletion (deactivation) of clients; no hard deletes. |
| FR-01.5 | The system shall list all clients with search, filter (by type, partner, manager, status), and pagination. |
| FR-01.6 | The system shall support searching clients by name, company number, UTR, VAT number, or internal reference. |

### FR-02: Company Details

| ID | Requirement |
|----|-------------|
| FR-02.1 | Each client shall have optional company details including company number, status, incorporation date, and addresses. |
| FR-02.2 | The system shall support an **Autofill with Companies House** control that pre-populates company details from the official Companies House API using a configured API key (`COMPANIES_HOUSE_API_KEY`). |
| FR-02.3 | Invoice address shall be selectable as Registered Address, Postal Address, or a Custom address. |
| FR-02.4 | SIC codes shall be selectable from a pre-loaded lookup table. |

### FR-03: Contact / Person Management

| ID | Requirement |
|----|-------------|
| FR-03.1 | The system shall maintain a global contacts register; a person can be linked to multiple clients. |
| FR-03.2 | When creating a client, the user can select an existing person or create a new one as the main contact. |
| FR-03.3 | Each client-contact link shall record whether the contact is the main contact for that client. |
| FR-03.4 | Contact records shall store personal details: name, DOB, addresses, NI number, personal UTR, etc. |
| FR-03.5 | The system shall support AML Check and ID Check initiation per contact with date tracking. |
| FR-03.6 | Photo ID Verified and Address Verified are toggles on the contact record. |
| FR-03.7 | The self-assessment toggle and fee shall be stored per client-contact relationship (not per contact globally). |

### FR-04: Services & Pricing

| ID | Requirement |
|----|-------------|
| FR-04.1 | The system shall maintain a master list of services (Accounts, Bookkeeping, CT600, Payroll, VAT Returns, etc.). |
| FR-04.2 | Each client can have any combination of services toggled on/off. |
| FR-04.3 | Each enabled service shall have an individual fee amount (£). |
| FR-04.4 | Combined pricing (Annual Charge and Monthly Charge) shall be stored separately, each with a toggle and amount. |
| FR-04.5 | Services list shall be configurable by an admin (add/remove/reorder). |

### FR-05: Accounts & Returns Details

| ID | Requirement |
|----|-------------|
| FR-05.1 | Each client shall have one Accounts & Returns record tracking: period end dates, CT600 due, corporation tax amount, payment reference. |
| FR-05.2 | Tax Office shall be selectable from a lookup. |
| FR-05.3 | The system shall track latest action status, action date, and records received date. |
| FR-05.4 | A free-text progress note shall be available. |
| FR-05.5 | Companies House email reminder is a toggle. |

### FR-06: Confirmation Statement

| ID | Requirement |
|----|-------------|
| FR-06.1 | Each client shall have one Confirmation Statement record. |
| FR-06.2 | Fields: statement date, due date, latest action, officers, share capital, shareholders, PSC (People with Significant Control). |
| FR-06.3 | Officers, Share Capital, Shareholders, and PSC are stored as free text (to be structured in a future phase). |

### FR-07: VAT Details

| ID | Requirement |
|----|-------------|
| FR-07.1 | Each client shall have one VAT details record. |
| FR-07.2 | VAT frequency selectable from lookup (Quarterly, Monthly, Annual). |
| FR-07.3 | The system shall store VAT number with a "Validate" action (future HMRC API integration point). |
| FR-07.4 | VAT scheme toggles: Standard, Cash Accounting, Retail, Margin, Flat Rate. Multiple can be active. |
| FR-07.5 | If Flat Rate is enabled, a Flat Rate Category must be selectable. |
| FR-07.6 | MTD (Making Tax Digital) fields: applied date, MTD ready toggle. |
| FR-07.7 | Track latest action, records received, and progress notes. |

### FR-08: PAYE Details

| ID | Requirement |
|----|-------------|
| FR-08.1 | Each client shall have one PAYE details record. |
| FR-08.2 | Fields: employer reference, accounts office reference, frequency, employee count, salary details. |
| FR-08.3 | Toggles for: Irregular Monthly Pay, Nil EPS. |
| FR-08.4 | Track key dates: first pay date, RTI deadline, scheme ceased date. |
| FR-08.5 | Track latest action, records received, and progress/general notes. |

### FR-09: CIS (Construction Industry Scheme)

| ID | Requirement |
|----|-------------|
| FR-09.1 | Each client shall have one CIS details record. |
| FR-09.2 | CIS Contractor and CIS Subcontractor are independent toggles (a client can be both). |
| FR-09.3 | Fields: CIS Date, CIS Deadline, Latest Action, Latest Action Date, Records Received, Progress Note. |
| FR-09.4 | Track latest action status and progress notes. |

### FR-10: Auto-Enrolment

| ID | Requirement |
|----|-------------|
| FR-10.1 | Each client shall have one Auto-Enrolment record. |
| FR-10.2 | Track key dates: staging, postponement, opt-out, re-enrolment, compliance due/submission. |
| FR-10.3 | Store pension provider name and pension ID. |
| FR-10.4 | Track latest action, records received, and progress notes. |

### FR-11: P11D Details

| ID | Requirement |
|----|-------------|
| FR-11.1 | Each client shall have one P11D record tracking next return due, latest submitted, and action status. |
| FR-11.2 | Track records received and progress notes. |

### FR-12: Registration / Onboarding

| ID | Requirement |
|----|-------------|
| FR-12.1 | Each client shall have one Registration record. |
| FR-12.2 | Fields: terms signed / registration fee paid (toggle + £), letter of engagement signed (date), money laundering complete (toggle), 64-8 registration date. |

### FR-13: Partner & Manager Assignment

| ID | Requirement |
|----|-------------|
| FR-13.1 | Each client shall be assignable to one Partner and one Manager from the users table. |
| FR-13.2 | Partner and Manager dropdowns shall show only users with the appropriate role. |

### FR-14: Supplementary onboarding fields (Bright Manager sections)

| ID | Requirement |
|----|-------------|
| FR-14.1 | The system shall store **income details**, **previous accountant** (name and notes), and **other details** on the client record for onboarding context. |
| FR-14.2 | The system shall support a **secondary contact** as a second person linked to the client (non–main contact row on `client_contacts`). |

### FR-15: Companies House autofill

| ID | Requirement |
|----|-------------|
| FR-15.1 | Where configured, the user shall trigger a lookup by company number to populate company fields from the Companies House API. |

### FR-16: Create client — onboarding continuation

| ID | Requirement |
|----|-------------|
| FR-16.1 | On create, the user shall choose between saving only or **saving and continuing into onboarding** (e.g. redirect to client view or tasks with context). |

### FR-17: Prospect vs confirmed client (task generation)

| ID | Requirement |
|----|-------------|
| FR-17.1 | New clients shall default to **prospect** (`is_prospect = true`) until the user confirms the engagement. |
| FR-17.2 | While **prospect**, the system shall **not** run automated task generation for enabled services (Bright Manager behaviour). |
| FR-17.3 | The user shall be able to mark the client as **confirmed** (not a prospect), after which task sync may create tasks from enabled services. |

---

## 4. Data Requirements

### DR-01: Data Types & Precision

| Data Category | Type | Precision |
|---------------|------|-----------|
| Monetary amounts | DECIMAL | (12,2) or (15,2) for turnover |
| Dates | DATE | dd/mm/yyyy display format |
| Toggles/Booleans | TINYINT(1) | 0 = off, 1 = on |
| Free text notes | TEXT | Unlimited |
| Phone numbers | VARCHAR(30) | Supports international format |
| Email addresses | VARCHAR(255) | Standard max |
| Reference numbers (UTR, NI, VAT) | VARCHAR(20) | Alphanumeric |

### DR-02: Required Fields (Minimum for creation)

- Client: `name`, `client_type_id`
- Contact: `first_name`, `last_name`
- All other fields are optional at creation and can be populated progressively.

### DR-03: Audit Fields

Every table shall include:
- `created_at` (TIMESTAMP, auto-set on insert)
- `updated_at` (TIMESTAMP, auto-updated on modification)

---

## 5. Business Rules & Validation

| Rule ID | Rule |
|---------|------|
| BR-01 | Internal Reference must be unique across all clients. |
| BR-02 | A client can have only one main contact at a time. |
| BR-03 | Company Number, if provided, should be validated for format (8-digit alphanumeric for UK). |
| BR-04 | NI Number format: 2 letters + 6 digits + 1 letter (e.g., AB123456C). |
| BR-05 | UTR format: 10-digit number. |
| BR-06 | VAT Number format: GB + 9 digits or 12 digits. |
| BR-07 | Email fields must be valid email format. |
| BR-08 | Deceased date, if set, must be after date of birth. |
| BR-09 | Flat Rate Category is required only when Flat Rate toggle is enabled. |
| BR-10 | Monetary fields must be >= 0 (no negative fees). |
| BR-11 | Lookup values cannot be deleted if they are referenced by any client record (RESTRICT). |
| BR-12 | Deleting a client soft-deletes (sets `is_active = 0`); associated detail records are preserved. |

---

## 6. User Roles & Permissions

| Role | Create Client | Edit Client | Delete Client | Assign Partner/Manager | Manage Lookups |
|------|:---:|:---:|:---:|:---:|:---:|
| Admin | Yes | Yes | Yes | Yes | Yes |
| Partner | Yes | Yes | No | Self only | No |
| Manager | Yes | Yes | No | No | No |
| Staff | Yes | Own clients | No | No | No |

---

## 7. UI Sections — Detailed Field Specifications

The New Client form is organised into **13 collapsible sections**. Each section below lists every field, its input control type, whether it is required or optional, and the database column it maps to.

**Input type legend:**
- **Text** — Free-text input
- **Textarea** — Multi-line free-text
- **Dropdown** — Select from lookup table
- **Date** — Date picker (dd/mm/yyyy)
- **Toggle** — On/Off switch (boolean)
- **Currency** — £ amount input (decimal)
- **Button** — Action trigger (not stored directly)
- **Search Select** — Searchable dropdown / autocomplete

---

### 7.1 Required Information

> **Database Table:** `clients`
> **Collapsible:** Yes (default expanded)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Name | Text | Yes | `clients.name` | Client / company display name |
| 2 | Client Type | Dropdown | Yes | `clients.client_type_id` → `lkp_client_types` | e.g. Private Limited Company, Sole Trader, Partnership, LLP |
| 3 | Complete Credit Check | Button | No | `clients.credit_check_completed`, `clients.credit_check_date` | Sets flag and date when completed |
| 4 | Partner | Dropdown | No | `clients.partner_id` → `users` | Filtered to users with role = partner |
| 5 | Manager | Dropdown | No | `clients.manager_id` → `users` | Filtered to users with role = manager |

---

### 7.2 Internal

> **Database Table:** `clients`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Internal Reference | Text | No | `clients.internal_reference` | UNIQUE. Auto-generated if blank. Custom internal code for the client. |

---

### 7.3 Company Details

> **Database Table:** `company_details`
> **Collapsible:** Yes (default collapsed)
> **Note:** "Autofill with Companies House" button at top populates fields from Companies House API.

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Company Number | Text | No | `company_details.company_number` | 8-char alphanumeric (UK format) |
| 2 | Company Status | Dropdown | No | `company_details.company_status_id` → `lkp_company_statuses` | Active, Dormant, Dissolved, etc. |
| 3 | Incorporation Date | Date | No | `company_details.incorporation_date` | dd/mm/yyyy |
| 4 | Company Trading As | Text | No | `company_details.trading_as` | Trading name if different from registered name |
| 5 | Registered Address | Textarea | No | `company_details.registered_address` | Full registered office address |
| 6 | Company Postal Address | Textarea | No | `company_details.postal_address` | Correspondence address |
| 7 | Invoice Address | Dropdown | No | `company_details.invoice_address_type` | Options: Company Postal Address, Registered Address, Custom |
| 8 | Invoice Address (Custom) | Textarea | No | `company_details.invoice_address_custom` | Only shown if Invoice Address = Custom |
| 9 | Primary Company Email | Text | No | `company_details.primary_email` | Valid email format |
| 10 | Company Email Domain | Text | No | `company_details.email_domain` | e.g. example.co.uk |
| 11 | Company Telephone | Text | No | `company_details.telephone` | |
| 12 | Turnover | Currency (£) | No | `company_details.turnover` | Annual turnover |
| 13 | Date of Trading | Date | No | `company_details.date_of_trading` | |
| 14 | SIC Code | Dropdown | No | `company_details.sic_code_id` → `lkp_sic_codes` | Standard Industrial Classification |
| 15 | Nature of Business | Text | No | `company_details.nature_of_business` | Free text description |
| 16 | Corporation Tax Office | Text | No | `company_details.corporation_tax_office` | |
| 17 | Company UTR | Text | No | `company_details.company_utr` | 10-digit Unique Taxpayer Reference |
| 18 | Companies House Authentication Code | Text | No | `company_details.companies_house_auth_code` | Used for electronic filings |

---

### 7.4 Main Contact

> **Database Tables:** `contacts` + `client_contacts`
> **Collapsible:** Yes (default expanded)
> **Note:** User can "Select Existing Person" or create new. One contact is marked as main contact.

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Person | Search Select | No | `client_contacts.contact_id` → `contacts` | Select existing person or create new |
| 2 | Title | Dropdown | Yes* | `contacts.title_id` → `lkp_titles` | Mr, Mrs, Ms, Dr, etc. (*required if creating new) |
| 3 | First Name | Text | Yes* | `contacts.first_name` | *Required if creating new |
| 4 | Middle Name | Text | No | `contacts.middle_name` | |
| 5 | Last Name | Text | Yes* | `contacts.last_name` | *Required if creating new |
| 6 | Start AML Check | Button | No | `contacts.aml_check_started`, `contacts.aml_check_date` | Action button — sets flag and date |
| 7 | Start ID Check | Button | No | `contacts.id_check_started`, `contacts.id_check_date` | Action button — sets flag and date |
| 8 | Create Self Assessment Client | Toggle + Currency (£) | No | `client_contacts.create_self_assessment`, `client_contacts.self_assessment_fee` | Toggle enables SA; currency sets fee |
| 9 | Client Does Their Own SA | Toggle | No | `client_contacts.client_does_own_sa` | |
| 10 | Preferred Name | Text | No | `contacts.preferred_name` | |
| 11 | Date of Birth | Date | No | `contacts.date_of_birth` | dd/mm/yyyy |
| 12 | Deceased | Date | No | `contacts.deceased_date` | dd/mm/yyyy. Must be after DOB. |
| 13 | Email | Text | No | `contacts.email` | Valid email format |
| 14 | Portal Login Email | Text | No | `contacts.portal_login_email` | Email used for client portal access |
| 15 | Postal Address | Textarea | No | `contacts.postal_address` | Current address |
| 16 | Previous Address | Textarea | No | `contacts.previous_address` | |
| 17 | Telephone Number | Text | No | `contacts.telephone_number` | Landline |
| 18 | Mobile Number | Text | No | `contacts.mobile_number` | |
| 19 | NI Number | Text | No | `contacts.ni_number` | Format: 2 letters + 6 digits + 1 letter |
| 20 | Personal UTR Number | Text | No | `contacts.personal_utr` | 10-digit number |
| 21 | Companies House Personal Code | Text | No | `contacts.companies_house_personal_code` | |
| 22 | Terms Signed | Date | No | `contacts.terms_signed_date` | dd/mm/yyyy |
| 23 | Photo ID Verified | Toggle | No | `contacts.photo_id_verified` | |
| 24 | Address Verified | Toggle | No | `contacts.address_verified` | |
| 25 | Marital Status | Dropdown | No | `contacts.marital_status_id` → `lkp_marital_statuses` | Single, Married, Divorced, etc. |
| 26 | Nationality | Dropdown | No | `contacts.nationality_id` → `lkp_nationalities` | |
| 27 | Preferred Language | Dropdown | No | `contacts.language_id` → `lkp_languages` | |

---

### 7.5 Services Required

> **Database Tables:** `client_services` + `services`
> **Collapsible:** Yes (default collapsed)
> **Note:** Each service has a toggle (on/off) and a fee field (£). Services listed below are seed data.

| # | Service Name | Input Type | DB Column (toggle) | DB Column (fee) |
|---|-------------|------------|-----------|----------|
| 1 | Accounts | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 2 | Bookkeeping | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 3 | CT600 Return | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 4 | Payroll | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 5 | Auto-Enrolment | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 6 | VAT Returns | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 7 | Management Accounts | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 8 | Confirmation Statement | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 9 | CIS | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 10 | P11D | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 11 | Fee Protection Service | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 12 | Registered Address | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 13 | Bill Payment | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 14 | Consultation/Advice | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |
| 15 | Software | Toggle + Currency (£) | `client_services.is_enabled` | `client_services.fee` |

#### Combined Pricing

> **Database Table:** `client_combined_pricing`

| # | Field Label | Input Type | DB Column (toggle) | DB Column (amount) |
|---|-------------|------------|-----------|----------|
| 1 | Annual Charge | Toggle + Currency (£) | `client_combined_pricing.annual_charge_enabled` | `client_combined_pricing.annual_charge` |
| 2 | Monthly Charge | Toggle + Currency (£) | `client_combined_pricing.monthly_charge_enabled` | `client_combined_pricing.monthly_charge` |

---

### 7.6 Accounts and Returns Details

> **Database Table:** `accounts_returns`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Accounts Period End | Date | No | `accounts_returns.accounts_period_end` | |
| 2 | CH Year End (Companies House) | Date | No | `accounts_returns.ch_year_end` | |
| 3 | HMRC Year End | Date | No | `accounts_returns.hmrc_year_end` | |
| 4 | CH Accounts Next Due | Date | No | `accounts_returns.ch_accounts_next_due` | |
| 5 | CT600 Due | Date | No | `accounts_returns.ct600_due` | |
| 6 | Corporation Tax Amount Due | Currency (£) | No | `accounts_returns.corporation_tax_amount_due` | |
| 7 | Tax Due HMRC Year End | Date | No | `accounts_returns.tax_due_hmrc_year_end` | |
| 8 | CT Payment Reference | Text | No | `accounts_returns.ct_payment_reference` | Format: [UTR] + code (e.g. [UTR] A001 A) |
| 9 | Tax Office | Dropdown | No | `accounts_returns.tax_office_id` → `lkp_tax_offices` | |
| 10 | Companies House Email Reminder | Toggle | No | `accounts_returns.ch_email_reminder` | |
| 11 | Accounts Latest Action | Dropdown | No | `accounts_returns.latest_action_id` → `lkp_action_statuses` | Filtered to category = 'accounts' |
| 12 | Accounts Latest Action Date | Date | No | `accounts_returns.latest_action_date` | |
| 13 | Accounts Records Received | Date | No | `accounts_returns.records_received` | |
| 14 | Accounts Progress Note | Textarea | No | `accounts_returns.progress_note` | |

---

### 7.7 Confirmation Statement

> **Database Table:** `confirmation_statements`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Confirmation Statement Date | Date | No | `confirmation_statements.statement_date` | |
| 2 | Confirmation Statement Due | Date | No | `confirmation_statements.statement_due` | |
| 3 | Latest Action | Dropdown | No | `confirmation_statements.latest_action_id` → `lkp_action_statuses` | Filtered to category = 'confirmation' |
| 4 | Latest Action Date | Date | No | `confirmation_statements.latest_action_date` | |
| 5 | Records Received | Date | No | `confirmation_statements.records_received` | |
| 6 | Progress Note | Textarea | No | `confirmation_statements.progress_note` | |
| 7 | Officers | Textarea | No | `confirmation_statements.officers` | Free text (structured in future phase) |
| 8 | Share Capital | Textarea | No | `confirmation_statements.share_capital` | Free text |
| 9 | Shareholders | Textarea | No | `confirmation_statements.shareholders` | Free text |
| 10 | People with Significant Control | Textarea | No | `confirmation_statements.people_with_significant_control` | Free text (PSC) |

---

### 7.8 VAT Details

> **Database Table:** `vat_details`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | VAT Frequency | Dropdown | No | `vat_details.vat_frequency_id` → `lkp_vat_frequencies` | Quarterly, Monthly, Annual |
| 2 | VAT Period End | Date | No | `vat_details.vat_period_end` | |
| 3 | Next Return Due | Date | No | `vat_details.next_return_due` | |
| 4 | VAT Bill Amount | Currency (£) | No | `vat_details.vat_bill_amount` | |
| 5 | VAT Bill Due | Date | No | `vat_details.vat_bill_due` | |
| 6 | Latest Action | Dropdown | No | `vat_details.latest_action_id` → `lkp_action_statuses` | Filtered to category = 'vat' |
| 7 | Latest Action Date | Date | No | `vat_details.latest_action_date` | |
| 8 | Records Received | Date | No | `vat_details.records_received` | |
| 9 | Progress Note | Textarea | No | `vat_details.progress_note` | |
| 10 | VAT Member State | Dropdown | No | `vat_details.vat_member_state_id` → `lkp_vat_member_states` | |
| 11 | VAT Number | Text + Button (Validate) | No | `vat_details.vat_number` | Format: GB + 9 or 12 digits. Validate button is future HMRC API integration. |
| 12 | VAT Address | Textarea | No | `vat_details.vat_address` | |
| 13 | Date of Registration | Date | No | `vat_details.date_of_registration` | |
| 14 | Effective Date | Date | No | `vat_details.effective_date` | |
| 15 | Estimated Turnover | Currency (£) | No | `vat_details.estimated_turnover` | |
| 16 | Applied for MTD | Date | No | `vat_details.applied_for_mtd` | Making Tax Digital application date |
| 17 | MTD Ready | Toggle | No | `vat_details.mtd_ready` | |
| 18 | Transfer of Going Concern | Toggle | No | `vat_details.transfer_of_going_concern` | |
| 19 | Involved in Any Other Businesses | Toggle | No | `vat_details.involved_in_other_businesses` | |
| 20 | Direct Debit | Toggle | No | `vat_details.direct_debit` | |
| 21 | Standard Scheme | Toggle | No | `vat_details.standard_scheme` | |
| 22 | Cash Accounting Scheme | Toggle | No | `vat_details.cash_accounting_scheme` | |
| 23 | Retail Scheme | Toggle | No | `vat_details.retail_scheme` | |
| 24 | Margin Scheme | Toggle | No | `vat_details.margin_scheme` | |
| 25 | Flat Rate | Toggle | No | `vat_details.flat_rate` | Enables Flat Rate Category field |
| 26 | Flat Rate Category | Dropdown | Conditional | `vat_details.flat_rate_category_id` → `lkp_flat_rate_categories` | Required if Flat Rate = ON |
| 27 | Month of Last Quarter Submitted | Dropdown | No | `vat_details.month_last_quarter_submitted` | Months 1–12 |
| 28 | Box 5 of Last Quarter Submitted | Currency (£) | No | `vat_details.box5_last_quarter_submitted` | |
| 29 | General Notes | Textarea | No | `vat_details.general_notes` | |

---

### 7.9 PAYE Details

> **Database Table:** `paye_details`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Employers Reference | Text | No | `paye_details.employers_reference` | PAYE employer reference number |
| 2 | Accounts Office Reference | Text | No | `paye_details.accounts_office_reference` | |
| 3 | Years Required | Dropdown | No | `paye_details.years_required` | e.g. 2025/26, 2026/27 |
| 4 | PAYE Frequency | Dropdown | No | `paye_details.paye_frequency_id` → `lkp_paye_frequencies` | Weekly, Monthly, etc. |
| 5 | Irregular Monthly Pay | Toggle | No | `paye_details.irregular_monthly_pay` | |
| 6 | Nil EPS | Toggle | No | `paye_details.nil_eps` | |
| 7 | No. of Employees | Text (numeric) | No | `paye_details.no_of_employees` | Integer, with "Employee(s)" label |
| 8 | Salary Details | Textarea | No | `paye_details.salary_details` | Free text breakdown |
| 9 | First Pay Date | Date | No | `paye_details.first_pay_date` | |
| 10 | RTI Deadline | Date | No | `paye_details.rti_deadline` | Real Time Information deadline |
| 11 | PAYE Scheme Ceased | Date | No | `paye_details.paye_scheme_ceased` | |
| 12 | PAYE Latest Action | Dropdown | No | `paye_details.latest_action_id` → `lkp_action_statuses` | Filtered to category = 'paye' |
| 13 | PAYE Latest Action Date | Date | No | `paye_details.latest_action_date` | |
| 14 | PAYE Records Received | Date | No | `paye_details.records_received` | |
| 15 | PAYE Progress Note | Textarea | No | `paye_details.progress_note` | |
| 16 | General Notes | Textarea | No | `paye_details.general_notes` | |

---

### 7.10 CIS (Construction Industry Scheme)

> **Database Table:** `cis_details`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | CIS Contractor | Toggle | No | `cis_details.is_contractor` | |
| 2 | CIS Subcontractor | Toggle | No | `cis_details.is_subcontractor` | Client can be both |
| 3 | CIS Date | Date | No | `cis_details.cis_date` | CIS period date |
| 4 | CIS Deadline | Date | No | `cis_details.cis_deadline` | Filing deadline |
| 5 | CIS Latest Action | Dropdown | No | `cis_details.latest_action_id` → `lkp_action_statuses` | Filtered to category = 'cis' |
| 6 | CIS Latest Action Date | Date | No | `cis_details.latest_action_date` | |
| 7 | CIS Records Received | Date | No | `cis_details.records_received` | |
| 8 | CIS Progress Note | Textarea | No | `cis_details.progress_note` | |

---

### 7.11 Auto-Enrolment

> **Database Table:** `auto_enrolment`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Auto-Enrolment Latest Action | Dropdown | No | `auto_enrolment.latest_action_id` → `lkp_action_statuses` | Filtered to category = 'auto_enrolment' |
| 2 | Auto-Enrolment Latest Action Date | Date | No | `auto_enrolment.latest_action_date` | |
| 3 | Auto-Enrolment Records Received | Date | No | `auto_enrolment.records_received` | |
| 4 | Auto-Enrolment Progress Note | Textarea | No | `auto_enrolment.progress_note` | |
| 5 | Auto-Enrolment Staging | Date | No | `auto_enrolment.staging_date` | Staging date for workplace pension |
| 6 | Postponement Date | Date | No | `auto_enrolment.postponement_date` | |
| 7 | The Pensions Regulator Opt Out Date | Date | No | `auto_enrolment.pensions_regulator_opt_out_date` | |
| 8 | Re-Enrolment Date | Date | No | `auto_enrolment.re_enrolment_date` | |
| 9 | Pension Provider | Text | No | `auto_enrolment.pension_provider` | e.g. NEST, People's Pension |
| 10 | Pension ID | Text | No | `auto_enrolment.pension_id` | Provider-issued ID |
| 11 | Declaration of Compliance Due | Date | No | `auto_enrolment.declaration_of_compliance_due` | |
| 12 | Declaration of Compliance Submission | Date | No | `auto_enrolment.declaration_of_compliance_submission` | |

---

### 7.12 P11D

> **Database Table:** `p11d_details`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Next P11D Return Due | Date | No | `p11d_details.next_return_due` | |
| 2 | Latest P11D Submitted | Date | No | `p11d_details.latest_submitted` | |
| 3 | P11D Latest Action | Dropdown | No | `p11d_details.latest_action_id` → `lkp_action_statuses` | Filtered to category = 'p11d' |
| 4 | P11D Latest Action Date | Date | No | `p11d_details.latest_action_date` | |
| 5 | P11D Records Received | Date | No | `p11d_details.records_received` | |
| 6 | P11D Progress Note | Textarea | No | `p11d_details.progress_note` | |

---

### 7.13 Registration

> **Database Table:** `registration`
> **Collapsible:** Yes (default collapsed)

| # | Field Label | Input Type | Required | DB Column | Notes |
|---|-------------|------------|----------|-----------|-------|
| 1 | Terms Signed/Registration Fee Paid | Toggle + Currency (£) | No | `registration.terms_signed_fee_paid`, `registration.registration_fee` | Toggle enables; currency stores fee amount |
| 2 | Letter of Engagement Signed | Date | No | `registration.letter_of_engagement_signed` | |
| 3 | Money Laundering Complete | Toggle | No | `registration.money_laundering_complete` | |
| 4 | 64-8 Registration | Date | No | `registration.sixty_four_eight_registration` | Agent authorisation form date |

---

### 7.14 Field Count Summary

| Section | Total Fields |
|---------|:---:|
| Required Information | 5 |
| Internal | 1 |
| Company Details | 18 |
| Main Contact | 27 |
| Services Required | 15 services + 2 combined = 17 |
| Accounts and Returns Details | 14 |
| Confirmation Statement | 10 |
| VAT Details | 29 |
| PAYE Details | 16 |
| CIS | 8 |
| Auto-Enrolment | 12 |
| P11D | 6 |
| Registration | 4 |
| **Total** | **167** |

---

## 8. Bright Manager parity & UI behaviour

This section records **parity expectations** against Bright Manager, informed by the product comparison document (BrightManager Docs). It supplements Sections 3 and 7: where the specification already listed a field, **implementation** may have lagged the spec; where Bright Manager uses **dynamic tabs** or **nested forms**, we describe the target behaviour.

### 8.1 Required information & company

| Topic | Target behaviour |
|-------|------------------|
| **Companies House autofill** | A control beside company number calls the Companies House REST API and fills registered address, incorporation date, company status (where mappable), SIC where possible, and suggests the registered company name. Requires `COMPANIES_HOUSE_API_KEY`. |
| **Internal reference** | Single field: unique internal code with optional auto-generation on create (Section 7.2). |

### 8.2 Contacts

| Topic | Target behaviour |
|-------|------------------|
| **Main contact** | Full AML/ID actions, Photo ID verified, Address verified, marital status, nationality, preferred language, previous address, deceased date, terms signed, Companies House personal code — all stored on `contacts` / pivot per Section 7.4. |
| **Secondary contact** | A second person linked to the client (`client_contacts` with `is_main_contact = 0`), same field set as main where applicable (without main-only pivot flags such as self-assessment fee on the secondary row unless product later requires it). |

### 8.3 Income, previous accountant, other details

| Section | Purpose |
|---------|---------|
| **Income details** | Free-text / structured capture of client income context for the practice (stored on `clients` for this phase — see migration). |
| **Previous accountant** | Name and notes for the prior adviser. |
| **Other details** | Catch-all notes for onboarding context. |

### 8.4 Services, pricing, and service-driven UI

| Topic | Target behaviour |
|-------|------------------|
| **Services & fees** | Master list with per-service toggle and fee; drives task templates via existing task sync (see `task-management.md`). |
| **Combined pricing** | Annual and monthly charges with toggles; should be editable **below** the service list (or in the same logical block). Optional **sum of selected service fees** can be applied to annual charge to mirror Bright Manager’s derived totals. |
| **Service-linked panels** | In Bright Manager, selecting certain services exposes additional **tabs** (e.g. **Staff tasks** / **Staff task monitor** — task nature; **Agent authorisation** when CT600 / VAT / Payroll / CIS are selected; **Management accounts** when that service is selected). **Target:** surface the same concepts in-app: link to tasks where relevant, show agent-authorisation / 64-8 context near registration, and label Management Accounts when the service is on. Full tab-for-tab cloning may be phased; task workflows are covered in the Task Management module. |

### 8.5 Compliance sections & visibility

| Topic | Target behaviour |
|-------|------------------|
| **Accounts & returns / CT600** | All `accounts_returns` fields including CT600 due, tax amounts, payment reference, and progress fields (Section 7.6). CT600-related fields should appear **grouped** under a clear CT600 / corporation tax label in the UI. |
| **PAYE vs Auto-enrolment / P11D** | In Bright Manager, AE and P11D sit **inside** the PAYE area as subordinate forms. **Target:** present Auto-enrolment and P11D **nested under** the PAYE section (may remain separate scroll areas but visually grouped). |
| **CIS** | CIS detail form should be **emphasised when the CIS service** is selected (Bright Manager creates CIS context with that service). |

### 8.6 Create client actions

| Action | Target behaviour |
|--------|------------------|
| **Add client** | Standard save to client list or show. |
| **Add and create onboarding workflow** | Save the client then take the user to the **next step for onboarding** (e.g. client show with onboarding hint, or tasks view filtered for that client) so practice staff can continue setup immediately. |

---

## 9. Future Modules (Out of Scope)

The following modules are planned for future phases and will extend the database schema:

| Module | Description |
|--------|-------------|
| **Task Management** | Next phase — task assignment, deadlines, workflow tracking |
| **Invoicing** | Generate and track invoices per client |
| **Time Tracking** | Log time spent on client work |
| **Queue** | Work queue and priority management |
| **Resources** | Document and file management per client |
| **Tools** | Utility tools (calculators, templates) |
| **Settings** | System configuration, user management |
| **Reporting** | Compliance dashboards, financial reports |

---

*End of Document*
