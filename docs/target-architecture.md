# Target Architecture

The structure of the full application — where it lands after the refurbish, not where it is today.

Read this before writing any migration. It exists so the whole shape is agreed up front, rather than
discovered halfway through.

- Current state, field by field: [`admin-modules-and-fields.md`](admin-modules-and-fields.md)
- Current routes, middleware and authorization: [`backend-map.md`](backend-map.md)
- Training in detail — custom fields, import/export, certificates: [`training-module-spec.md`](training-module-spec.md)

**Scope of this document.** Website CMS for hrt-bd.com, office management (income, expense, approvals,
assets), student training for the ASSET project, role and permission management, and a JSON API for the
Android app.

---

## 1. System context

Three clients, one Laravel application, one database.

```mermaid
flowchart TB
    subgraph clients["CLIENTS"]
        W["Admin Web<br/>Blade + Livewire + Flux"]
        P["Public Website<br/>hrt-bd.com"]
        A["Android App<br/>PLANNED"]
    end

    L["Laravel Application"]

    subgraph stores["STORAGE"]
        DB[("MySQL")]
        FS[("File storage<br/>invoices, photos, documents")]
    end

    W -->|"session cookie"| L
    P -->|"no auth"| L
    A -->|"Bearer token"| L
    L --> DB
    L --> FS
```

The Android app is the reason several decisions below go the way they do. A second client means business
logic can no longer live inside a web controller, because two entry points now need the same rules.

---

## 2. Layered architecture

### 2.1 The problem to solve

Today every controller does its own validation inline and talks straight to Eloquent. That works with one
client. With two, every rule would have to be written twice — once for the web form and once for the API —
and they would drift.

### 2.2 Target

```mermaid
flowchart TB
    W["Web Controller<br/>returns Blade"]
    A["API Controller<br/>returns JSON"]

    FR["Form Request<br/>validation rules"]
    SV["Service<br/>business logic, file handling, transitions"]
    M["Eloquent Model<br/>relations, casts, scopes"]
    R["API Resource<br/>JSON shape"]
    DB[("MySQL + storage")]

    W --> FR
    A --> FR
    FR --> SV
    W --> SV
    A --> SV
    SV --> M
    M --> DB
    A --> R
    M --> R
```

Three shared pieces, written once and used by both lanes:

| Layer | Holds | Example |
| --- | --- | --- |
| **Form Request** | Validation rules only | `StoreExpenseRequest` — same rules for the web form and the app |
| **Service** | Business logic, file handling, state transitions | `ExpenseService::create()`, `ApprovalService::act()` |
| **API Resource** | JSON shape for the app | `ExpenseResource` — one definition of what an expense looks like on the wire |

The web controller renders Blade; the API controller returns a Resource. Neither contains rules.

### 2.3 What this replaces

| Today | Target |
| --- | --- |
| `$request->validate([...])` inline in each action | `StoreExpenseRequest` / `UpdateExpenseRequest` |
| `approve()` copy-pasted in two controllers | `ApprovalService`, plus an `Approvable` trait on the models |
| Status constants duplicated across `IncomeEntry` and `ExpenseEntry` | One `Approvable` trait |
| `/data` endpoints returning pre-rendered HTML strings | Stay for DataTables; the app uses the JSON API instead |

The `/data` DataTables endpoints return HTML `<button>` markup inside JSON. They are fine for the admin
tables but unusable from Android, which is why the API is a separate surface rather than a reuse of them.

---

## 3. Module map

```mermaid
flowchart TB
    subgraph fin["FINANCE"]
        F1["Incomes<br/>CHANGED"]
        F3["Expenses<br/>CHANGED"]
        F5["Approvals"]
    end

    subgraph cat["CATEGORIES — own screens, own permission"]
        F2["Income Categories<br/>CHANGED — now gated"]
        F4["Expense Categories<br/>NEW"]
    end

    subgraph trn["TRAINING — ASSET"]
        T1["Students<br/>CHANGED from Trainees"]
        T2["Trades / Subjects<br/>NEW"]
        T3["Batches<br/>NEW"]
        T4["Enrolments<br/>NEW"]
        T5["Projects"]
    end

    subgraph ops["OPERATIONS"]
        O1["Assets"]
        O2["Attachments<br/>NEW — shared"]
    end

    subgraph cms["WEBSITE CMS"]
        C1["Page Sections"]
        C2["Team Members"]
        C3["Portfolio"]
        C4["Contact Settings"]
        C5["Courses"]
        C6["Services"]
    end

    subgraph adm["ADMINISTRATION"]
        A1["Users<br/>NEW"]
        A2["Roles and Permissions<br/>NEW"]
        A3["Activity Log"]
    end

    subgraph api["API — ANDROID"]
        P1["Token auth<br/>NEW"]
        P2["Expense endpoints<br/>NEW"]
        P3["Approval endpoints<br/>NEW"]
        P4["Upload endpoints<br/>NEW"]
    end

    F4 --> F3
    F2 --> F1
    F1 --> F5
    F3 --> F5
    O2 --> F3
    O2 --> F1
    T2 --> T3
    T5 --> T3
    T3 --> T4
    T1 --> T4
    A2 --> A1
    P1 --> P2
    P2 --> P3
```

---

## 4. Domain model

Split into three diagrams so each stays readable. `NEW` marks a table that does not exist yet.

### 4.1 Finance

```mermaid
erDiagram
    users              ||--o{ income_entries    : "created_by"
    users              ||--o{ expense_entries   : "created_by"
    users              ||--o{ approvals         : "approved_by"
    income_categories  ||--o{ income_entries    : "income_category_id"
    expense_categories ||--o{ expense_entries   : "expense_category_id"
    income_entries     ||--o{ approvals         : "morph approvable"
    expense_entries    ||--o{ approvals         : "morph approvable"
    income_entries     ||--o{ attachments       : "morph attachable"
    expense_entries    ||--o{ attachments       : "morph attachable"
    users              ||--o{ attachments       : "uploaded_by"

    expense_categories {
        string name UK "NEW"
        string code UK
        string description
        string status
    }
    expense_entries {
        string title
        bigint expense_category_id FK "DONE was free text"
        bigint project_id FK "NEW for project cost reporting"
        bigint batch_id FK "NEW optional"
        decimal amount
        date   date
        string payment_method
        string vendor_name
        string reference_number
        bigint created_by FK "CHANGED now required"
        string status
    }
    income_entries {
        string title
        bigint income_category_id FK "CHANGED was free text"
        bigint project_id FK "NEW for project revenue"
        decimal amount
        date   date
        bigint created_by FK "CHANGED now required"
        string status
    }
    attachments {
        bigint attachable_id FK "NEW"
        string attachable_type
        string kind "invoice, receipt, document, photo"
        string disk
        string path
        string original_name
        string mime_type
        bigint size_bytes
        bigint uploaded_by FK
    }
    approvals {
        bigint approvable_id FK
        string approvable_type
        string stage
        string status
        text   comments
        bigint approved_by FK
        datetime approved_at
    }
```

### 4.2 Training — ASSET

```mermaid
erDiagram
    projects ||--o{ batches    : "project_id"
    trades   ||--o{ batches    : "trade_id"
    courses  |o--o| trades     : "optional course_id"
    batches  ||--o{ enrolments : "batch_id"
    students ||--o{ enrolments : "student_id"
    users    ||--o{ batches    : "instructor_id"

    trades {
        string code UK "NEW"
        string name
        bigint course_id FK
        int    duration_hours
        string competency_level
        int    seats_per_batch
        decimal fee
        string status
    }
    batches {
        bigint project_id FK "NEW"
        bigint trade_id FK
        string code UK
        date   start_date
        date   end_date
        string schedule
        string venue
        bigint instructor_id FK
        int    capacity
        string status
    }
    students {
        string registration_number UK "CHANGED from trainees"
        string first_name
        string nid "now nullable"
        string birth_certificate_no
        string gender
        text   present_address
        string district
        string education_level
        string status
    }
    enrolments {
        bigint batch_id FK "NEW"
        bigint student_id FK
        string roll_number
        date   enrolled_on
        string status
        decimal attendance_percentage
        string result
        string certificate_number
        date   certificate_issued_at
    }
```

`trainees` keeps its table name unless you want the rename; "Students" is the label in the UI either way.
Full field lists are in [`admin-modules-and-fields.md` section 3.4](admin-modules-and-fields.md).

### 4.3 CMS, assets and administration

```mermaid
erDiagram
    course_categories  ||--o{ courses  : "category_id"
    service_categories ||--o{ services : "service_category_id"
    users              ||--o{ assets   : "assigned_user_id"
    users              ||--o{ activity_logs : "user_id"
    roles              ||--o{ users    : "role_id"
    roles              }o--o{ permissions : "permission_role"

    roles {
        string name UK "NEW — option B only"
        string label
        boolean is_system
    }
    permissions {
        string name UK "NEW — option B only"
        string label
        string group
    }
    users {
        string name
        string email UK
        string role "CHANGED to role_id under option B"
        string phone
        string profile_photo_path
        string digital_signature_path
        string status "NEW active or inactive"
    }
```

`page_sections`, `team_members`, `portfolio_items` and `contact_settings` are unchanged and have no
relations — see the field tables in the companion document.

---

## 5. Expenses — the changes you asked for

Three changes, all on the expense module.

### 5.1 Categories

`expense_entries.expense_category` is a free string today, with no table behind it. The same category ends
up spelled three ways and cannot be reported on.

New `expense_categories` table, mirroring `income_categories` but with a code for reporting:

| Field | Type | R | Notes |
| --- | --- | :-: | --- |
| `name` | string(255) unique | ✓ | e.g. "Training Materials" |
| *`code`* | string(50) unique | | Short code for reports, e.g. `TRN-MAT` |
| *`description`* | text | | |
| `status` | string(20) | ✓ | `active` / `inactive`, default `active` |

Then on `expense_entries`: add `expense_category_id` FK, backfill by matching the existing strings, drop
the old `expense_category` column.

Do the same on the income side — `income_entries.source_category` becomes `income_category_id`, pointing at
the `income_categories` table you already have and currently do not use as a FK.

### 5.2 How categories work everywhere — the rule

Every kind of category is **its own table, its own screen, and its own manage permission.** Listing them is
a separate, weaker right than changing them.

This matters because most people who file an expense should be able to *pick* a category from a dropdown
but must not be able to rename or delete one. Renaming a category that a hundred entries already point at
silently rewrites your reports; deleting one breaks them.

```mermaid
flowchart LR
    subgraph sel["SELECT — read only"]
        S1["Staff filing an expense"]
        S2["Anyone with finance.manage"]
    end
    subgraph mng["MANAGE — full CRUD"]
        M1["Admin"]
        M2["Accountant"]
    end

    S1 --> D["GET active categories<br/>dropdown only"]
    S2 --> D
    M1 --> C["Categories screen<br/>create · edit · deactivate"]
    M2 --> C
    D --> T[("expense_categories")]
    C --> T
```

Applied across the application:

| Category table | Used by | Manage permission | Who may select |
| --- | --- | --- | --- |
| `expense_categories` **NEW** | Expenses | `finance.categories.manage` | Anyone with `finance.manage` or `finance.view` |
| `income_categories` | Incomes | `finance.categories.manage` | Anyone with `finance.manage` or `finance.view` |
| `project_categories` | Projects | `projects.manage` | Anyone with `projects.view` |
| `trades` | Batches | `training.manage` | Anyone with `training.view` |
| `course_categories` | Courses | `cms.manage` | Anyone with `cms.manage` |
| `service_categories` | Services | `cms.manage` | Anyone with `cms.manage` |

Three consequences for the build:

1. **`finance.categories.manage` is a new permission**, held by `admin` and `accountant` only. It is
   deliberately *not* implied by `finance.manage` — otherwise everyone who can file an expense could also
   delete the category list.
2. **A list endpoint is not a management endpoint.** `GET /expense-categories` returns active categories
   only and is open to anyone who can create an expense. The create, update and delete actions sit behind
   `finance.categories.manage`.
3. **Never hard-delete a category that is in use.** Deactivate instead — set `status` to `inactive` so it
   disappears from new dropdowns while existing entries keep resolving. Only allow a real delete when the
   usage count is zero.

Right now `Finance\IncomeCategoryController` has **no permission check at all** — any logged-in user can
create and delete income categories. That is one of the five unguarded modules in `backend-map.md`, and this
is the permission that fixes it.

### 5.3 User on the expense

`created_by` exists but is **nullable**, so an expense can have no owner. Target:

- Make `created_by` **required** (NOT NULL). Backfill any orphans first.
- Always set it server-side from the authenticated user — never accept it from the request body. This
  matters most on the API, where the app must not be able to file an expense as someone else.
- Show it in the list and detail screens, and add a "my expenses" filter.

If you reimburse staff for money they spent personally, add a second optional FK `paid_by` — the person
owed the money, who is not always the person entering the record. Left out of the schema above because I
do not know whether you do reimbursements. See the open questions.

### 5.4 Invoice photos

Today an expense holds **one** file in `attachment_path`, and uploading a replacement deletes the old one.
An expense normally has several invoice pages, and from a phone you will photograph them one at a time.

Use one polymorphic `attachments` table for the whole application rather than adding more `*_path` columns:

| Field | Type | R | Notes |
| --- | --- | :-: | --- |
| `attachable_id` + `attachable_type` | morphs | ✓ | Expense, income, student, asset — anything |
| `kind` | string(30) | ✓ | `invoice`, `receipt`, `document`, `photo` |
| `disk` | string(30) | ✓ | Default `public` |
| `path` | string(255) | ✓ | |
| `original_name` | string(255) | ✓ | What the user's file was called |
| `mime_type` | string(100) | ✓ | Lets the app decide image vs PDF |
| `size_bytes` | unsigned bigint | ✓ | |
| *`uploaded_by`* | FK → users | | |
| `sort_order` | int | ✓ | Page order for multi-page invoices |

This is the same pattern as `approvals`, which is already polymorphic — so it is a shape the codebase
already uses.

Migration path: create the table, copy every non-null `attachment_path` from `income_entries` and
`expense_entries` into it as `kind = 'invoice'`, then drop the two columns.

Practical limits worth setting now: images and PDFs only, roughly 10 MB per file, maybe 10 files per
expense. Phone cameras produce large files, so resize on upload rather than storing 12 MP originals.

### 5.5 Expense lifecycle, including the app

```mermaid
sequenceDiagram
    actor S as Staff (Android)
    participant API as API
    participant SV as ExpenseService
    participant DB as Database
    actor MD as Managing Director

    S->>API: POST /api/v1/expenses (title, category, amount, date)
    API->>SV: create(user, data)
    SV->>DB: expense_entries (status pending, created_by = user)
    API-->>S: 201 expense id

    S->>API: POST /api/v1/expenses/{id}/attachments (photo)
    API->>SV: attach(expense, file, kind=invoice)
    SV->>DB: attachments row
    API-->>S: 201 attachment

    Note over S,API: repeat per invoice page

    MD->>API: GET /api/v1/approvals/pending
    API-->>MD: expenses awaiting this role
    MD->>API: POST /api/v1/expenses/{id}/approve
    API->>SV: ApprovalService::act(approve)
    SV->>DB: approvals row + status to pending_director
    API-->>MD: 200 updated expense
```

Create first, attach after. That way a photo upload that fails on a poor connection can be retried without
losing the typed fields.

---

## 6. API layer for Android

### 6.1 What exists today

One route — `GET /api/income/categories` — unauthenticated, returning raw model JSON. There is no `api`
guard in `config/auth.php`, and neither Sanctum nor Passport is installed. The `AddApiKeyHeader` middleware
puts `X-API-KEY` on the **response** and validates nothing.

So the API is effectively a blank page. That is good news for the app — nothing to unpick.

### 6.2 Target

```mermaid
flowchart LR
    APP["Android App"]
    APP -->|"POST /api/v1/auth/login"| T["Sanctum<br/>issue token"]
    T -->|"Bearer token"| G["auth:sanctum<br/>+ permission gates"]
    G --> C["Api\\V1\\* Controllers"]
    C --> SV["Services<br/>shared with web"]
    C --> RES["API Resources<br/>JSON shape"]
```

Decisions:

| Question | Answer | Why |
| --- | --- | --- |
| Auth | **Laravel Sanctum**, personal access tokens | Built for exactly this; simpler than Passport, no OAuth flows needed for a first-party app |
| Versioning | `/api/v1/...` | The app ships to phones and cannot be force-updated |
| Shape | API Resources, never raw models | Raw models leak columns and break the app when the schema changes |
| Auth on writes | `created_by` from the token, never from the body | Otherwise the app can file records as another user |
| `AddApiKeyHeader` | **Remove it** | It emits a secret and checks nothing |
| Errors | Consistent JSON envelope with a code and a message | The app needs to branch on failures, not parse prose |

### 6.3 Endpoints for phase one

Enough for a staff expense-filing app plus an approver inbox.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/v1/auth/login` | Email + password, returns a token. Must handle the 2FA challenge |
| POST | `/api/v1/auth/logout` | Revoke the current token |
| GET | `/api/v1/auth/me` | Current user, role and permission list |
| GET | `/api/v1/expense-categories` | Active categories, for the picker. Read-only — see below |
| GET | `/api/v1/expenses` | Paginated, filterable by status, date range, mine |
| POST | `/api/v1/expenses` | Create |
| GET | `/api/v1/expenses/{id}` | Detail with attachments and approval history |
| PUT | `/api/v1/expenses/{id}` | Update while still editable |
| POST | `/api/v1/expenses/{id}/attachments` | Upload one invoice image |
| DELETE | `/api/v1/attachments/{id}` | Remove an attachment |
| GET | `/api/v1/approvals/pending` | The approver's inbox for their stage |
| POST | `/api/v1/expenses/{id}/approve` | `action` = approve, reject or send_back |
| GET | `/api/v1/incomes` … | Same set for income, once expense is proven |

**The app gets no category management endpoints.** `GET /expense-categories` returns active categories for
the dropdown and is available to anyone who can file an expense. Creating, editing and deleting categories
stays on the web admin behind `finance.categories.manage`. The phone is for filing expenses, not for
restructuring the chart of accounts.

Two things the app forces that the web admin has been able to avoid:

1. **Pagination must be real.** The `/data` endpoints page through DataTables parameters. The API needs
   ordinary `page` and `per_page` with a meta block.
2. **The 2FA challenge needs an API path.** Fortify's challenge is a web redirect. Logging in from the app
   with 2FA enabled needs a token-based two-step login, or an app-specific token issued from the web admin.
   Decide this before building the login screen.

---

## 7. Permissions

Current permissions stay. New ones for the modules being added:

| Permission | Covers | Held by |
| --- | --- | --- |
| `finance.categories.manage` | CRUD on income and expense categories | `admin`, `accountant` |
| `training.view` | See students, batches, enrolments | Training staff and above |
| `training.manage` | Create and edit them | `admin`, training staff |
| `students.manage` | Register and edit student records | `admin`, training staff |
| `projects.view` / `projects.manage` | Projects and project categories — currently unguarded | `admin`, project staff |
| `users.manage` | Already defined, currently unused — wire it to the new Users screen | `admin`, `superadmin` |

### 7.1 Manage versus select

The category permissions above follow the rule in [section 5.2](#52-how-categories-work-everywhere--the-rule):
**listing is not managing.** Two roles that both file expenses can differ on whether they may edit the
category list.

| Role | File an expense | Pick a category | Edit the category list |
| --- | :-: | :-: | :-: |
| `admin` | ✓ | ✓ | ✓ |
| `accountant` | ✓ | ✓ | ✓ |
| `chairman` · `managing_director` · `director` | — | ✓ (read-only views) | — |
| Future staff role | ✓ | ✓ | — |

That last row is the case that matters. Right now you have no role that can file an expense without also
being able to delete the category list, because `Finance\IncomeCategoryController` checks nothing at all
and expense categories are free text. Once categories are a table with their own permission, you can add a
plain staff role for the Android app that files expenses and picks categories and nothing more.

### 7.2 Fixes that belong in this work

Two, both from `backend-map.md`:

- Five modules currently have **no permission check at all** — projects, project categories, trainees,
  income categories and the activity log. Anything the refurbish touches should get a gate.
- `canBeApprovedBy()` checks the role string directly, so `approvals.manage` does nothing and a superadmin
  cannot approve. Move it onto the permission system while the approval code is being extracted into
  `ApprovalService`.

Also note: **there is currently no way to create a user.** No Users screen, and registration is disabled in
`config/fortify.php`. See [`admin-modules-and-fields.md` section 3.7](admin-modules-and-fields.md) — including
the warning about what happens if registration is simply switched back on.

---

## 8. Build order

```mermaid
flowchart TB
    P0["PHASE 0 — Foundation<br/>Users screen · gates on unguarded modules<br/>Approvable trait · ApprovalService"]
    P1["PHASE 1 — Finance cleanup<br/>expense_categories DONE · income category FK<br/>project_id on entries · attachments · required created_by"]
    P2["PHASE 2 — API<br/>Sanctum · v1 routes · Resources<br/>expenses + approvals + uploads"]
    P3["PHASE 3 — Training<br/>trades · batches · enrolments · custom fields<br/>export · import · certificates<br/>see training-module-spec.md"]
    P4["PHASE 4 — Optional<br/>role and permission admin<br/>attendance · assessment detail"]

    P0 --> P1
    P1 --> P2
    P1 --> P3
    P2 --> P4
    P3 --> P4
```

Why this order:

- **Phase 0 first** because you cannot currently create a user, which blocks everything staffing-related,
  and because extracting `ApprovalService` before the API is built means it gets written once rather than
  twice.
- **Phase 1 before phase 2** because the app should be built against the final expense shape. Shipping an
  app against `expense_category` as a string and then changing it forces an app release.
- **Phases 2 and 3 are independent** and can run in parallel if there is more than one person.

---

## 9. Open questions

Carried forward, plus new ones from the API and expense work.

**Expenses and the app**

1. **Reimbursements.** Is the person entering an expense always the person who spent the money? If not,
   `expense_entries` needs a `paid_by` alongside `created_by`.
2. **Editing after submission.** Can a staff member edit an expense once it is `pending`, or only while it
   is a draft? There is no draft state today — everything starts at `pending`.
3. **Offline capture.** Should the app queue expenses taken with no signal and sync later? That changes the
   API (client-generated ids, idempotency keys) and is much cheaper to decide now than to retrofit.
4. **2FA on the app.** Every user has 2FA. How should the app log in — a token-based two-step challenge, or
   app tokens generated from the web admin?

**Training**

5. **Competency levels.** NTVQF 1–6, or your own basic/intermediate/advanced scale?
6. **Attendance.** Per-session, or one percentage per enrolment?
7. **Certificates.** Issued from the system with a number and a verification page, or only recorded?
8. **One project or many?** Is ASSET the only training project, or will others run alongside it?

**Administration**

9. **Roles.** Option A (Users screen, roles stay in code) or option B (roles and permissions in the
   database, editable from the admin)? This is the one that most changes how much work phase 4 is.
