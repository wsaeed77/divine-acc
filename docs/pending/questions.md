# Divinne Accountancy Software — Pending Questions

**Status:** Awaiting Answers
**Date:** 26 March 2026

---

## Instructions

Please answer each question below. You can type your answer directly under each question. Once all are answered, the requirements and database structure documents will be finalised.

---

### Q1: Self Assessment Task Naming

Self Assessment is tied to the contact (via `client_contacts.create_self_assessment`). When this is toggled on, does a task get created for that client-contact pair?

If yes, what is the naming pattern?
- Option A: `Self Assessment {Contact Name} {Tax Year}` (e.g., Self Assessment John Smith 2025/26)
- Option B: `Self Assessment {Tax Year}` (e.g., Self Assessment 2025/26)
- Option C: Something else?

**Your Answer:**

---

### Q2: Target Date Auto-Calculation

When "Manually Set Target Date" is OFF, the Target Date is auto-calculated. How should it be calculated?

- Option A: Fixed number of days before the Deadline (e.g., always 30 days before)
- Option B: Varies per task type (e.g., 2 months before for Accounts, 1 month before for VAT)
- Option C: Configurable per task type by an admin in settings
- Option D: Something else?

If a fixed number, how many days/weeks/months before the Deadline?

**Your Answer:**

---

### Q3: Breakdown Templates Management

Who can create and edit Breakdown Templates?

- Option A: Only admins (via a settings screen)
- Option B: Any user (created on-the-fly while editing a task)
- Option C: Admins create templates in settings; any user can apply them to tasks

Is there a dedicated management screen for templates, or are they managed inline?

**Your Answer:**

---

### Q4: Notifications

When "Notify On Progress" is set to a user and the task status changes, how should the notification be delivered?

- Option A: Email only
- Option B: In-app notification only
- Option C: Both email and in-app
- Option D: Not decided yet (will define later)

**Your Answer:**

---

### Q5: Service Total Annual Value

On the task edit form, "Service Total Annual Value (£)" — is this field:

- Option A: **Read-only** — automatically calculated as the sum of all enabled service fees for the client
- Option B: **Editable** — user can override the value on the task
- Option C: Something else?

**Your Answer:**

---

*Once answered, hand this back and the documents will be updated accordingly.*
