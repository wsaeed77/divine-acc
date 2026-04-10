# Self Assessment — Bright Manager Reference Spec

> **Status:** ✅ IMPLEMENTED — all sections aligned with Bright Manager reference.  
> **Source:** BrightManager Docs (4).pdf + screenshots provided by user.

---

## Confirmation Statement

**WHOLE FORM IS REMOVED IN SELF ASSESSMENT.** ✅ Confirmed by screenshot.

The "Confirmation Statement" accordion/section does not appear at all when Client Type is Self Assessment.

---

## Section Layout (Dashboard)

### Standard (non-SA) client — sections shown

| Left column | Right column |
|---|---|
| Required Information | Services Required |
| Internal | Accounts and Returns Details |
| Company Details | Confirmation Statement |
| Main Contact | VAT Details |
| Secondary Contact | PAYE Details |
| Income Details | Registration |
| Previous Accountant | |
| Other Details | |

### Self Assessment selected — sections shown

| Left column | Right column |
|---|---|
| Required Information | Services Required |
| Internal | Accounts and Returns Details |
| **Business Details** *(replaces Company Details)* | VAT Details |
| Main Contact | PAYE Details |
| Secondary Contact | Registration |
| Income Details | |
| Previous Accountant | |
| Other Details | |

**Removed for SA:** Confirmation Statement (entire section gone)

---

## 1. Required Information

### Standard (non-SA)

| Field | Type | Notes |
|---|---|---|
| Autofill with Companies House | Button | Teal/full-width |
| Name | Text input | Required (*) |
| Client Type | Dropdown | |
| Complete Credit Check | Toggle/checkbox | |
| Partner | Dropdown | |
| Manager | Dropdown | |

### Self Assessment selected

| Field | Type | Notes |
|---|---|---|
| Client Type | Dropdown | Shows "Self Assessment" |
| Partner | Dropdown | |
| Manager | Dropdown | |

**Removed for SA:**
- Autofill with Companies House button
- Name field
- Complete Credit Check toggle

---

## 2. Company Details → Business Details

### Standard — Company Details fields

| Field | Type | Notes |
|---|---|---|
| Company Number | Text input | |
| Company Status | Dropdown | |
| Incorporation Date | Date picker | dd/mm/yyyy |
| Company Trading As | Text input | ⓘ icon |
| Registered Address | Textarea | |
| Company Postal Address | Textarea | |
| Invoice Address | Dropdown | Default "Company Postal Address" |
| Primary Company Email | Text input | ⓘ icon |
| Company Email Domain | Text input | ⓘ icon |
| Company Telephone | Text input | |
| Turnover | Currency input | £ prefix |
| Date of Trading | Date picker | dd/mm/yyyy |
| SIC Code | Dropdown | |
| Nature of Business | Text input | |
| Corporation Tax Office | Text input | |
| Company UTR | Text input | |
| Companies House Authentication Code | Text input | |

### Self Assessment — Business Details fields *(completely replaces Company Details)*

| Field | Type | Notes |
|---|---|---|
| Trading As | Text input | |
| Trading Address | Textarea | |
| Commenced Trading | Date picker | dd/mm/yyyy |
| Ceased Trading | Date picker | dd/mm/yyyy |
| Registered for SA | Date picker | dd/mm/yyyy |
| Turnover | Currency input | £ prefix |
| Nature of Business | Text input | |
| MTD Qualifying Year | Dropdown | |

**Summary:** The entire Company Details section is replaced with Business Details for SA. Section header changes from "Company Details" to "Business Details". All company-specific fields (number, status, incorporation, addresses, email domain, SIC code, corp tax office, CH auth code) are removed and replaced with the 8 SA-specific fields above.

---

## 3. Main Contact

*(Awaiting screenshot)*

Known from PDF: All fields same as standard **except** "Create Self Assessment Client" toggle is **removed**.

---

## 4. Secondary Contact

*(Awaiting screenshot)*

Known from PDF: All fields same as standard **except** "Create Self Assessment Client" toggle is **removed**.

---

## 5. Services Required

### Standard — all services

| Service | Toggle + Fee |
|---|---|
| Accounts | ✓ |
| Bookkeeping | ✓ |
| CT600 Return | ✓ |
| Payroll | ✓ |
| Auto-Enrolment | ✓ |
| VAT Returns | ✓ |
| Management Accounts | ✓ |
| Confirmation Statement | ✓ |
| CIS | ✓ |
| P11D | ✓ |
| Fee Protection Service | ✓ |
| Registered Address | ✓ |
| Bill Payment | ✓ |
| Consultation/Advice | ✓ |
| Software | ✓ |
| **Combined Pricing** sub-section | |
| — Annual Charge | toggle + £ |
| — Monthly Charge | toggle + £ |

### Self Assessment — services list

| Service | Toggle + Fee | Change vs Standard |
|---|---|---|
| Accounts | ✓ | Same |
| Bookkeeping | ✓ | Same |
| ~~CT600 Return~~ | — | **REMOVED** |
| Payroll | ✓ | Same |
| Auto-Enrolment | ✓ | Same |
| VAT Returns | ✓ | Same |
| Management Accounts | ✓ | Same |
| ~~Confirmation Statement~~ | — | **REMOVED** |
| **Self Assessment Tax Return** | ✓ | **NEW** |
| **MTD Quarterly Filing** | ✓ | **NEW** |
| **MTD Final Declaration** | ✓ | **NEW** |
| CIS | ✓ | Same |
| P11D | ✓ | Same |
| Fee Protection Service | ✓ | Same |
| Registered Address | ✓ | Same |
| Bill Payment | ✓ | Same |
| Consultation/Advice | ✓ | Same |
| Software | ✓ | Same |
| **Combined Pricing** sub-section | | Same |
| — Annual Charge | toggle + £ | Same |
| — Monthly Charge | toggle + £ | Same |

**Removed for SA:** CT600 Return, Confirmation Statement  
**Added for SA:** Self Assessment Tax Return, MTD Quarterly Filing, MTD Final Declaration  

**Note:** PDF also mentions "Main Contact SA" as a new service (no task/tab generated) — not visible in the services list screenshot; may be handled separately or implicitly via the Main Contact section.

---

## 6. Accounts and Returns Details

### Standard — all fields

| Field | Type | Notes |
|---|---|---|
| Accounts Period End | Date picker | ⓘ icon |
| CH Year End (Companies House) | Date picker | ⓘ icon |
| HMRC Year End | Date picker | ⓘ icon |
| CH Accounts Next Due | Date picker | |
| CT600 Due | Date picker | |
| Corporation Tax Amount Due | Currency input | £ prefix |
| Tax Due HMRC Year End | Date picker | ⓘ icon |
| CT Payment Reference | Text input | ⓘ icon — auto-fills with "[UTR] A001" pattern, has copy button |
| Tax Office | Dropdown | |
| Companies House Email Reminder | Toggle | ⓘ icon |
| Accounts Latest Action | Dropdown | ⓘ icon |
| Accounts Latest Action Date | Date picker | ⓘ icon |
| Accounts Records Received | Date picker | ⓘ icon |
| Accounts Progress Note | Textarea | |

### Self Assessment — completely different field set

| Field | Type | Notes |
|---|---|---|
| Accounts Period End | Date picker | |
| Tax Year | Dropdown | **NEW** — replaces CH/HMRC year end fields |
| Tax Amount Due (31 July 2026) | Currency input | £ prefix — **dynamic label** based on selected Tax Year |
| Tax Amount Due (31 Jan 2027) | Currency input | £ prefix — **dynamic label** |
| Tax Amount Due (31 July 2027) | Currency input | £ prefix — **dynamic label** |
| Tax Office | Dropdown | |
| Accounts Latest Action | Dropdown | ⓘ icon |
| Accounts Missing Records | Textarea | ⓘ icon — **NEW field for SA** |
| Accounts Latest Action Date | Date picker | ⓘ icon |
| Accounts Records Received | Date picker | ⓘ icon |
| Accounts Progress Note | Textarea | |

**Removed for SA:** CH Year End, HMRC Year End, CH Accounts Next Due, CT600 Due, Corporation Tax Amount Due, Tax Due HMRC Year End, CT Payment Reference, Companies House Email Reminder

**Added for SA:** Tax Year (dropdown), Tax Amount Due × 3 (dynamic date labels), Accounts Missing Records (textarea)

**Tax Amount Due label logic:** The three payment dates follow the UK SA payment schedule for the selected Tax Year:
- 1st payment on account: **31 July** of the *current* tax year
- Balancing payment: **31 January** of the *following* year
- 2nd payment on account: **31 July** of the *following* year

Example for 2025/26: "Tax Amount Due (31 July 2026)", "Tax Amount Due (31 Jan 2027)", "Tax Amount Due (31 July 2027)"

---

## 7. VAT Details

*(Awaiting screenshot — expected same as standard)*

---

## 8. PAYE Details

### Auto-Enrolment sub-section

**Standard field order:**

| Field | Type | Notes |
|---|---|---|
| Auto-Enrolment Latest Action | Dropdown | ⓘ icon |
| Auto-Enrolment Latest Action Date | Date picker | ⓘ icon |
| Auto-Enrolment Records Received | Date picker | |
| Auto-Enrolment Progress Note | Textarea | |

**Self Assessment field order — one field inserted:**

| Field | Type | Notes |
|---|---|---|
| Auto-Enrolment Latest Action | Dropdown | ⓘ icon |
| **Missing Records** | **Textarea** | ⓘ icon — **NEW, inserted immediately after Latest Action** |
| Auto-Enrolment Latest Action Date | Date picker | ⓘ icon |
| Auto-Enrolment Records Received | Date picker | |
| Auto-Enrolment Progress Note | Textarea | |

---

### P11D sub-section

**Standard field order:**

| Field | Type | Notes |
|---|---|---|
| Next P11D Return Due | Date picker | |
| Latest P11D Submitted | Date picker | |
| P11D Latest Action | Dropdown | ⓘ icon |
| P11D Latest Action Date | Date picker | ⓘ icon |
| P11D Records Received | Date picker | |
| P11D Progress Note | Textarea | |

**Self Assessment field order — one field inserted:**

| Field | Type | Notes |
|---|---|---|
| Next P11D Return Due | Date picker | |
| Latest P11D Submitted | Date picker | |
| P11D Latest Action | Dropdown | ⓘ icon |
| **Missing Records** | **Textarea** | ⓘ icon — **NEW, inserted immediately after P11D Latest Action** |
| P11D Latest Action Date | Date picker | ⓘ icon |
| P11D Records Received | Date picker | |
| P11D Progress Note | Textarea | |

**Summary:** The only change in PAYE Details for SA is the insertion of a **"Missing Records" textarea** (with ⓘ icon) in two places:
1. Immediately after **Auto-Enrolment Latest Action**
2. Immediately after **P11D Latest Action**

All other PAYE fields remain unchanged.

---

## 9. Income Details

*(Awaiting screenshot — expected same as standard)*

---

## 10. Previous Accountant

*(Awaiting screenshot — expected same as standard)*

---

## 11. Other Details

*(Awaiting screenshot — expected same as standard)*

---

## 12. Registration

*(Awaiting screenshot — expected same as standard)*

---

## Task Generation Changes (from PDF)

| Service | Change |
|---|---|
| Accounts service | "Companies House Accounts Filing" task is **NOT created** |
| Main Contact SA (new service) | No task or tab generated |
| Self Assessment Tax Return (new service) | Task AND Tab created |
| MTD Quarterly Filing (new service) | Task AND Tab created |
| MTD Final Declaration (new service) | Only Tasks (no tab) |

---

*Last updated: 2026-04-10 — all sections implemented*
