# Divinne Accountancy Software — Requirements Document

**Module:** Task Management
**Version:** 1.2
**Date:** 26 March 2026

---

## Table of Contents

1. [Overview](#1-overview)
2. [Scope](#2-scope)
3. [Task Types & Generation Rules](#3-task-types--generation-rules)
4. [Functional Requirements](#4-functional-requirements)
5. [Task List View — Field Specifications](#5-task-list-view--field-specifications)
6. [Task Edit Form — Field Specifications](#6-task-edit-form--field-specifications)
7. [Deadline & Date Rules](#7-deadline--date-rules)
8. [Task Completion Behaviour](#8-task-completion-behaviour)
9. [Service Toggle-Off Behaviour](#9-service-toggle-off-behaviour)
10. [Remaining Questions](#10-remaining-questions)

---

## 1. Overview

Tasks are the core workflow items for the accounting firm. They represent work that needs to be done for a client — driven by the **services enabled** on that client's record. When a service is toggled ON for a client, corresponding tasks are automatically generated.

Tasks track compliance deadlines, are assigned to staff, and progress through action statuses until completion. When a recurring task is completed, the system auto-generates the next period's task and **updates the corresponding next-due dates** on the client's compliance sections.

---

## 2. Scope

- Auto-generation of tasks based on enabled client services
- Task list view with filtering, sorting, and bulk actions
- Task edit form with full detail entry
- Task assignment, monitoring, and notification
- Progress tracking via "Latest Action" status
- Target and Deadline date tracking (auto-calculated or manually set)
- Task breakdown checklists with reusable templates
- Time estimation and progress notes
- Task completion with client date rollover and next-period auto-generation
- Service toggle-off behaviour (soft disable with manual delete)
- Recurring task generation (monthly, quarterly, annual)

**Out of scope (this phase):** Manual/ad-hoc tasks, time tracking against tasks, invoicing from tasks.

---

## 3. Task Types & Generation Rules

Tasks are generated based on the services enabled in the client's **Services Required** section. Each task type has a naming pattern, recurrence cycle, and date source.

### 3.1 Task Type Summary

| # | Task Type | Triggered By Service | Naming Pattern | Recurrence | Deadline Source |
|---|-----------|---------------------|----------------|------------|-----------------|
| 1 | Accounts Preparation | Accounts | `Accounts Preparation Year End {dd/mm/yyyy}` | Annual | From `accounts_returns.accounts_period_end` |
| 2 | Companies House Submission | Accounts | `Companies House Submission Year End {dd/mm/yyyy}` | Annual | From `accounts_returns.ch_accounts_next_due` |
| 3 | CT600 Submission | CT600 Return | `CT600 Submission Year End {dd/mm/yyyy}` | Annual | From `accounts_returns.ct600_due` |
| 4 | Confirmation Statement | Confirmation Statement | `Confirmation Statement Period End {dd/mm/yyyy}` | Annual | From `confirmation_statements.statement_due` |
| 5 | VAT Submission | VAT Returns | `VAT Submission {Frequency} End {dd/mm/yyyy}` | Per VAT frequency | **Manually entered** |
| 6 | VAT Preparation | VAT Returns | `VAT Preparation {Frequency} End {dd/mm/yyyy}` | Per VAT frequency | **Manually entered** |
| 7 | PAYE | Payroll | `PAYE {Month Year}` | Per PAYE frequency | **Manually entered** |
| 8 | CIS | CIS | `CIS Period End {dd/mm/yyyy}` | Monthly | From `cis_details.cis_deadline` |
| 9 | Auto-Enrolment | Auto-Enrolment | `Auto-Enrolment` | One-off (staging date) | From `auto_enrolment.staging_date` |
| 10 | P11D | P11D | `P11D Submission Year End {dd/mm/yyyy}` | Annual | From `p11d_details.next_return_due` |
| 11 | Bookkeeping | Bookkeeping | `Bookkeeping Year End {dd/mm/yyyy}` | Annual | From `accounts_returns.accounts_period_end` |
| 12 | Management Accounts | Management Accounts | `Management Accounts Year End {dd/mm/yyyy}` | Annual | From `accounts_returns.accounts_period_end` |
| 13 | Self Assessment | Self Assessment (per contact) | `Self Assessment {tax year}` | Annual | **Manually entered** |
| 14 | Pension | Auto-Enrolment | `Pension {period}` | Per re-enrolment cycle | **Manually entered** |

### 3.2 Frequency Reference

| Frequency | Period | Tasks Created |
|-----------|--------|---------------|
| Annual | Yearly | 1 task per year, auto-increments on completion |
| Quarterly | Every 3 months | 1 task at a time, next generated on completion |
| Monthly | Every month | 1 task at a time, next generated on completion |
| Per PAYE Frequency | Follows client's PAYE Frequency setting | Weekly/Fortnightly/Four-Weekly/Monthly |

**Key rule:** For all recurring tasks, only **one task is active at a time**. When completed, the system generates the next period's task automatically.

### 3.3 Deadline Classification

| Category | Behaviour | Task Types |
|----------|-----------|------------|
| **Auto-populated from client data** | Deadline derived from dates in the client's compliance sections | Accounts Preparation, Companies House Submission, CT600 Submission, Confirmation Statement, CIS, Auto-Enrolment, P11D, Bookkeeping, Management Accounts |
| **Manually entered** | Deadline must be entered by the user on the task | PAYE, VAT Submission, VAT Preparation, Self Assessment, Pension |

---

## 4. Functional Requirements

### FR-TM-01: Task Auto-Generation

| ID | Requirement |
|----|-------------|
| FR-TM-01.1 | When a service is toggled ON for a client, the system shall automatically create the corresponding task(s) upon saving the client record. |
| FR-TM-01.2 | Task names shall follow the naming pattern defined per task type (see Section 3.1). Task names are editable after creation. |
| FR-TM-01.3 | For tasks with auto-populated deadlines, the deadline shall be derived from the client's compliance detail fields. |
| FR-TM-01.4 | For tasks with manually-entered deadlines (PAYE, VAT, Self Assessment, Pension), the deadline field shall be left blank and must be set by the user. |
| FR-TM-01.5 | Only **one task per type per client** is active at a time for recurring tasks. |
| FR-TM-01.6 | When a recurring task is completed, the system shall automatically generate the next period's task with dates incremented accordingly. |
| FR-TM-01.7 | The default assignee for auto-generated tasks shall be the client's **Manager**. If no manager is set, the task shall be unassigned. |

### FR-TM-02: Task List View

| ID | Requirement |
|----|-------------|
| FR-TM-02.1 | The system shall display tasks in a list/table view with columns: Checkbox, Favourite (star), Task Name, Client, Assignee, Latest Action, Target, Deadline, Action buttons. |
| FR-TM-02.2 | The task list shall support sorting by any column. |
| FR-TM-02.3 | The task list shall support filtering by: task type, client, assignee, status/latest action, date range, active/switched-off. |
| FR-TM-02.4 | The task list shall support pagination. |
| FR-TM-02.5 | Bulk selection via checkboxes for mass actions (assign, delete, change status). |

### FR-TM-03: Task Edit Form

| ID | Requirement |
|----|-------------|
| FR-TM-03.1 | Each task shall have a full edit form accessible from the task list (edit icon button). |
| FR-TM-03.2 | The edit form shall be split into panels: **Client** panel (left), **Details** panel (right), and **Task Status** panel (left below Client). |
| FR-TM-03.3 | All fields listed in Section 6 shall be editable via the edit form. |

### FR-TM-04: Task Assignment & Monitoring

| ID | Requirement |
|----|-------------|
| FR-TM-04.1 | Each task shall have one **Assignee** (Assign To) — the user responsible for completing the task. |
| FR-TM-04.2 | Each task shall optionally have a **Monitor** (Assign Monitor To) — a user who oversees progress. Can be N/A. |
| FR-TM-04.3 | Each task shall optionally have a **Notify On Progress** user — a user who receives notifications when the task status changes. Can be N/A. |
| FR-TM-04.4 | The default assignee for auto-generated tasks shall be the client's **Manager**. |
| FR-TM-04.5 | Assignee, Monitor, and Notify On Progress can be changed at any time. |

### FR-TM-05: Task Progress Tracking

| ID | Requirement |
|----|-------------|
| FR-TM-05.1 | Each task shall have a "Latest Action" status selectable from a dropdown (inline editable in the list view). |
| FR-TM-05.2 | The "Latest Action" statuses shall use the shared `lkp_action_statuses` lookup table. |
| FR-TM-05.3 | The "Latest Action" on a task is **independent** from the "Latest Action" on the client's corresponding compliance section. They are **not auto-synced**. (Confirmed) |

### FR-TM-06: Target & Deadline Dates

| ID | Requirement |
|----|-------------|
| FR-TM-06.1 | Each task shall have a **Target** date and a **Deadline** date. |
| FR-TM-06.2 | **Target** = the firm's internal goal date ("we aim to complete this by"). |
| FR-TM-06.3 | **Target Date** is auto-calculated by default. A **"Manually Set Target Date"** toggle allows the user to override and enter a custom target date. |
| FR-TM-06.4 | **Deadline** = the statutory/external due date (e.g., HMRC filing deadline, Companies House deadline). Auto-populated where possible, otherwise manually entered (see Section 3.3). |
| FR-TM-06.5 | Both dates shall be inline-editable in the task list view. |
| FR-TM-06.6 | Tasks with Deadline in the past shall be visually highlighted (overdue indicator). |

### FR-TM-07: Task Breakdown & Checklists

| ID | Requirement |
|----|-------------|
| FR-TM-07.1 | Each task shall have a **Task Breakdown** field — a multi-line text area where each line becomes a checkbox item. |
| FR-TM-07.2 | The system shall maintain **Breakdown Templates** — pre-defined checklists that can be applied to a task. |
| FR-TM-07.3 | When a Breakdown Template is selected, its items shall populate the Task Breakdown field. |
| FR-TM-07.4 | Individual checklist items can be ticked off as they are completed. |
| FR-TM-07.5 | Breakdown Templates are selectable from a dropdown (or N/A for no template). |

### FR-TM-08: Time Estimation & Progress Notes

| ID | Requirement |
|----|-------------|
| FR-TM-08.1 | Each task shall have a **Time Estimate** field in hours (decimal, e.g., 0.50 = 30 minutes). |
| FR-TM-08.2 | Each task shall have a **Progress Notes** free-text area for tracking internal notes about the task. |

### FR-TM-09: Task Actions

| ID | Requirement |
|----|-------------|
| FR-TM-09.1 | Tasks can be marked as **complete**. On completion: the task is archived, the next period's task is auto-generated for recurring types, and the **client's corresponding next-due dates are updated/rolled forward**. (Confirmed) |
| FR-TM-09.2 | Tasks can be **deleted** (with confirmation). Deleted tasks are permanently removed. |
| FR-TM-09.3 | Tasks can be **favourited/starred** for quick-access filtering. |
| FR-TM-09.4 | Tasks for clients with service toggled OFF shall display as "Switched Off" with a highlighted **Delete Task** button. |
| FR-TM-09.5 | Tasks for clients marked as "No longer a client" (inactive) shall display a visual indicator and a highlighted **Delete Task** button. |
| FR-TM-09.6 | Switched-off tasks remain in the list until the user manually deletes them. |

### FR-TM-10: Service Toggle-Off Behaviour

| ID | Requirement |
|----|-------------|
| FR-TM-10.1 | When a service is toggled OFF on a client, existing tasks for that service shall NOT be auto-deleted. |
| FR-TM-10.2 | Instead, the task shall be flagged as "Switched Off" in the task list. |
| FR-TM-10.3 | The Delete button shall be highlighted/prominent on switched-off tasks. |
| FR-TM-10.4 | The user must manually delete the task if they want to remove it. |
| FR-TM-10.5 | If the service is toggled back ON before the task is deleted, the task shall return to normal active state. |

---

## 5. Task List View — Field Specifications

> Columns displayed in the main task list table.

| # | Column | Input Type | DB Mapping | Notes |
|---|--------|------------|------------|-------|
| 1 | Checkbox | Checkbox | N/A | Bulk selection for mass actions |
| 2 | Favourite | Star icon (toggle) | `tasks.is_favourite` | Quick-access filter |
| 3 | Task Name | Read-only text | `tasks.task_name` | Auto-generated; editable in edit form |
| 4 | Client | Read-only text (link) | `tasks.client_id` → `clients.name` | Clickable to open client record |
| 5 | Assignee | Avatar/initials (clickable) | `tasks.assignee_id` → `users` | Coloured circle with initials |
| 6 | Latest Action | Dropdown (inline edit) | `tasks.latest_action_id` → `lkp_action_statuses` | e.g. Records Requested, No Latest Action |
| 7 | Target | Date (inline edit) | `tasks.target_date` | Firm's internal goal date |
| 8 | Deadline | Date (inline edit) | `tasks.deadline_date` | Statutory/external deadline |
| 9 | Actions | Icon buttons | N/A | Edit, Delete, Complete |

---

## 6. Task Edit Form — Field Specifications

> Based on the Bright Manager task edit form. The form has two main panels.

### 6.1 Client Panel (Left Side)

| # | Field Label | Input Type | Required | DB Mapping | Notes |
|---|-------------|------------|----------|------------|-------|
| 1 | Select Client | Search Select (Dropdown) | Yes | `tasks.client_id` → `clients` | Pre-populated for auto-generated tasks; read-only after creation? |
| 2 | Service Total Annual Value | Currency (£) (Read-only) | No | Calculated from `client_services` + `client_combined_pricing` | Sum of all enabled service fees for this client |

### 6.2 Task Status Panel (Left Side, below Client)

| # | Field Label | Input Type | Required | DB Mapping | Notes |
|---|-------------|------------|----------|------------|-------|
| 1 | Time Estimate | Decimal + "hour(s)" | No | `tasks.time_estimate` | e.g., 0.50 = 30 mins, 2.00 = 2 hours |
| 2 | Progress Notes | Textarea | No | `tasks.progress_notes` | Internal notes about task progress |

### 6.3 Details Panel (Right Side)

| # | Field Label | Input Type | Required | DB Mapping | Notes |
|---|-------------|------------|----------|------------|-------|
| 1 | Task Name | Text | Yes | `tasks.task_name` | Auto-generated on creation; editable |
| 2 | Assign To | Dropdown | No | `tasks.assignee_id` → `users` | Defaults to client's Manager |
| 3 | Notify On Progress | Dropdown | No | `tasks.notify_user_id` → `users` | Can be N/A. User notified on status changes. |
| 4 | Assign Monitor To | Dropdown | No | `tasks.monitor_id` → `users` | Can be N/A. User who oversees the task. |
| 5 | Target Date | Date | No | `tasks.target_date` | Auto-calculated unless manually set |
| 6 | Manually Set Target Date | Toggle | No | `tasks.target_date_manual` | When ON, Target Date becomes user-editable instead of auto-calculated |
| 7 | Deadline | Date | No | `tasks.deadline_date` | Statutory deadline. Auto-populated or manual (see Section 3.3) |
| 8 | Task Breakdown | Textarea (checklist) | No | `task_breakdown_items` (child table) | Each line = one checkbox item |
| 9 | Breakdown Templates | Dropdown | No | `tasks.breakdown_template_id` → `breakdown_templates` | Selects a pre-defined checklist. N/A = no template. |
| 10 | Description | Textarea | No | `tasks.description` | Free-text description of the task |

---

## 7. Deadline & Date Rules

### 7.1 Auto-Populated Deadlines

These tasks derive their deadline from the client's compliance sections:

| Task Type | Deadline Derived From | Date Field |
|-----------|----------------------|------------|
| Accounts Preparation | Accounts & Returns | `accounts_returns.accounts_period_end` |
| Companies House Submission | Accounts & Returns | `accounts_returns.ch_accounts_next_due` |
| CT600 Submission | Accounts & Returns | `accounts_returns.ct600_due` |
| Confirmation Statement | Confirmation Statement | `confirmation_statements.statement_due` |
| CIS | CIS Details | `cis_details.cis_deadline` |
| Auto-Enrolment | Auto-Enrolment | `auto_enrolment.staging_date` |
| P11D | P11D Details | `p11d_details.next_return_due` |
| Bookkeeping | Accounts & Returns | `accounts_returns.accounts_period_end` |
| Management Accounts | Accounts & Returns | `accounts_returns.accounts_period_end` |

### 7.2 Manually-Entered Deadlines

These tasks require the user to manually set the deadline:

| Task Type | Reason |
|-----------|--------|
| PAYE | Pay periods vary; user enters deadline per period |
| VAT Submission | VAT return deadlines vary; user enters per period |
| VAT Preparation | Preparation deadline is internal; user sets it |
| Self Assessment | SA deadlines vary by client; manually entered |
| Pension | Pension compliance deadlines; manually entered |

### 7.3 Target Date Logic

| Scenario | Behaviour |
|----------|-----------|
| "Manually Set Target Date" = OFF | Target Date is **auto-calculated** (system default logic, e.g., X days before deadline) |
| "Manually Set Target Date" = ON | Target Date field becomes editable; user enters a custom date |
| Target Date blank | Field shows as empty; no internal target set |

### 7.4 Recurring Increment Rules

When a recurring task is completed, the next task's dates are calculated:

| Recurrence | Increment Rule |
|------------|---------------|
| Annual | Add 12 months to the current task's period date |
| Quarterly | Add 3 months to the current task's period date |
| Monthly | Add 1 month to the current task's period date |
| Per PAYE Frequency | Add 1 week / 2 weeks / 4 weeks / 1 month depending on frequency |

---

## 8. Task Completion Behaviour

When a task is marked as complete, the following actions occur:

### 8.1 Step-by-Step Completion Flow

```
Task marked as "Complete"
         │
         ├─→ 1. Task status set to "Completed"
         │
         ├─→ 2. Task archived (moved out of active list)
         │
         ├─→ 3. Client's corresponding next-due dates
         │      updated / rolled forward (Confirmed)
         │      e.g. accounts_returns.ch_accounts_next_due += 12 months
         │           confirmation_statements.statement_due += 12 months
         │           vat_details.next_return_due += 3 months (quarterly)
         │
         └─→ 4. Next period's task auto-generated (for recurring types)
                 with incremented dates
```

### 8.2 Client Date Updates on Completion

| Task Type | Client Field(s) Updated |
|-----------|------------------------|
| Accounts Preparation | `accounts_returns.accounts_period_end` rolled forward |
| Companies House Submission | `accounts_returns.ch_accounts_next_due` rolled forward |
| CT600 Submission | `accounts_returns.ct600_due` rolled forward |
| Confirmation Statement | `confirmation_statements.statement_due` rolled forward |
| VAT Submission / Preparation | `vat_details.vat_period_end`, `vat_details.next_return_due` rolled forward |
| PAYE | Next period task generated (dates based on frequency) |
| CIS | `cis_details.cis_date`, `cis_details.cis_deadline` rolled forward |
| P11D | `p11d_details.next_return_due` rolled forward |
| Bookkeeping | Follows Accounts period |
| Management Accounts | Follows Accounts period |

---

## 9. Service Toggle-Off Behaviour

```
Service ON  →  Task created (active)
                    │
              Task in progress...
                    │
         ┌──────────┴──────────┐
         │                     │
   Service toggled OFF    Task completed
         │                     │
   Task marked as         Client dates updated
   "Switched Off"         Next period task
         │                auto-generated
   Delete button
   highlighted
         │
   ┌─────┴─────┐
   │           │
  User       Service
  deletes    toggled
  task       back ON
   │           │
  Task       Task returns
  removed    to active
```

---

## 10. Remaining Questions

> **Minor clarifications to finalise.**

### Q1: Self Assessment Task Generation
Self Assessment is tied to the **contact** (via `client_contacts.create_self_assessment`). When this is toggled on, does a Self Assessment task get created for that client-contact pair? What naming pattern — e.g., `Self Assessment {Contact Name} {Tax Year}`?

### Q2: Target Date Auto-Calculation
When "Manually Set Target Date" is OFF, how is the Target Date calculated? Is it a fixed number of days/weeks before the Deadline? Is this configurable per task type?

### Q3: Breakdown Templates Management
Who can create/edit Breakdown Templates — only admins? Is there a settings screen for managing them?

### Q4: Task Notifications
When "Notify On Progress" is set and the task status changes, how is the notification delivered? Email? In-app notification? Both?

### Q5: Service Total Annual Value
Is this a read-only calculated field showing the total of all service fees for the selected client? Or is it editable?

---

*End of Document — Awaiting answers to remaining minor questions before database structure.*
