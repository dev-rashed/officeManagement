# Project Checklist

Where everything stands as of **8 September 2026**.

| Status | Meaning |
| --- | --- |
| ✅ | Built, tested and working |
| ⚠️ | Exists but needs changing — it works, but something about it is wrong or incomplete |
| ☐ | Not built yet |

Related: [`backend-map.md`](backend-map.md) · [`admin-modules-and-fields.md`](admin-modules-and-fields.md) ·
[`target-architecture.md`](target-architecture.md) · [`training-module-spec.md`](training-module-spec.md)

---

## Summary

| Area | ✅ Done | ⚠️ Needs change | ☐ To build |
| --- | :-: | :-: | :-: |
| Access control and users | 6 | 0 | 1 |
| Finance | 4 | 7 | 1 |
| Website CMS and SEO | 6 | 1 | 0 |
| Analytics | 2 | 1 | 0 |
| Notifications | 5 | 2 | 1 |
| Training (ASSET) | 1 | 3 | 10 |
| API / Android | 0 | 1 | 5 |
| Operations | 3 | 3 | 0 |
| **Total** | **27** | **18** | **18** |

**The single most blocking gap:** there is no way to create a user. No Users screen, and registration is
disabled. New staff can only be added by database seeding, which means the approval chain cannot be
extended without a developer.

**Security posture, verified 8 Sep 2026:** CSRF on every state-changing route with no exemptions, a
permission gate on every controller, rate limiting on all authenticated writes, and a captcha on the three
forms an unauthenticated visitor can reach. 38 assertions passing.

---

## 1. Access control and users

- [x] ✅ **Permission system** — 12 permissions, 6 roles, gates registered in `AppServiceProvider`, `Gate::before` wildcard for superadmin
- [x] ✅ **Every controller is now gated** — the five previously unguarded ones (projects, project categories, trainees, activity log, field builder) use declarative `HasMiddleware` with `can:` checks. New permissions: `projects.view`, `projects.manage`, `students.manage`, `activity.view`
- [x] ✅ **CSRF verified on every state-changing route** — no exemptions; a POST without a token returns 419
- [x] ✅ **Rate limiting** — `throttle:admin-write` (90/min per user) on every authenticated group, plus Fortify's login and two-factor limiters
- [x] ✅ **Captcha on the auth forms** — login, forgot-password, reset-password, via `mews/captcha` (self-hosted, no external service)
- [x] ✅ **Roles & permissions admin** — `roles`, `permissions` and `permission_role` tables seeded from the old config, a permission matrix screen, custom roles, and cached lookups that clear on save. **Superadmin only**
- [ ] ☐ **User management screen** — **no `UserController`, no `users` routes, no way to create or edit a user in the admin.** `users.manage` is defined and granted to admin/superadmin but nothing consumes it. This is also what makes role assignment possible — the Roles screen can define a role, but nobody can be *put* into it yet

**Who can now do what**

| | Projects | Students | Audit log | Finance | CMS | Roles |
| --- | :-: | :-: | :-: | :-: | :-: | :-: |
| `superadmin` | manage | manage | read | manage | manage | **manage** |
| `admin` | manage | manage | read | manage | manage | — |
| `accountant` | manage | manage | — | manage | manage | — |
| `chairman` / `managing_director` / `director` | view only | — | — | view + approve | — | — |

These are now the *starting* values, editable at **Administration → Roles & Permissions**. Superadmin's
column is fixed: it holds everything implicitly and cannot be edited or deleted.

**`roles.manage` is deliberately not a permission.** The gate checks the superadmin role directly, so it
cannot be granted from inside the very screen it protects — otherwise an admin could tick one box and then
grant themselves everything. Tested by forging the permission onto the admin role: the screen still
returned 403.

**Do first:** the Users screen. It is the last thing in this section that is genuinely blocking, and it is
what lets you actually assign the roles this screen defines.

---

## 2. Finance

### Done

- [x] ✅ **Income module** — CRUD, DataTables endpoint, attachments, three-stage approval
- [x] ✅ **Expense module** — CRUD, attachments, three-stage approval
- [x] ✅ **Expense categories** — new `expense_categories` table, FK on `expense_entries`, admin screen with in-use protection, `finance.categories.manage` permission, data-preserving backfill migration
- [x] ✅ **Reject reason is mandatory** — rejecting or sending back requires a reason; shown to the submitter in a banner at the top of the entry, approval history colour-coded by outcome

### Needs modification

- [ ] ⚠️ **Income categories are still free text** — `income_entries.source_category` is a string, while `income_categories` exists as a managed table. Same fix as expenses; the expense migration is the template
- [ ] ⚠️ **`created_by` is nullable on both tables** — an entry can have no owner. Should be NOT NULL and always set server-side
- [ ] ⚠️ **One attachment per entry** — `attachment_path` holds a single file and a replacement deletes the old one. Needs the polymorphic `attachments` table so an expense can carry several invoice photos
- [ ] ⚠️ **No `project_id` on entries** — "what has ASSET cost us" cannot be answered. Both tables need a nullable `project_id`
- [ ] ⚠️ **`sent_back` is a dead end** — `update()` never resets the status and no route moves an entry out of it. A correction can never re-enter the approval chain
- [ ] ⚠️ **~200 duplicated lines** between `IncomeEntry`/`ExpenseEntry` and their controllers — constants, label maps, `canBeApprovedBy()`, `approve()`. Needs an `Approvable` trait and an `ApprovalService`
- [ ] ⚠️ **Approval checks the role, not the permission** — `canBeApprovedBy()` calls `isManagingDirector()` etc. directly, so `approvals.manage` does nothing and **a superadmin cannot approve anything**
- [ ] ☐ **Expense has no `/data` endpoint** — income uses server-side DataTables, expense uses plain pagination. Inconsistent

---

## 3. Website CMS and SEO

- [x] ✅ **Page sections, team members, portfolio, contact settings** — all gated on `cms.manage`
- [x] ✅ **Courses and services CMS** — with categories
- [x] ✅ **SEO settings screen** — site name, title format, default description and share image, canonical base, Twitter handle, organisation schema, custom head/body snippets
- [x] ✅ **Per-page SEO** — every static route plus every course, service and portfolio item, with a Google-result preview, character counters, OG overrides, noindex/nofollow
- [x] ✅ **Google tools** — GA4, Tag Manager (head + noscript), Search Console and Bing verification. Analytics never load on a noindex page
- [x] ✅ **`robots.txt` and `sitemap.xml`** — served from the database, editable, sitemap built from published content and excludes noindexed pages
- [ ] ⚠️ **Custom head/body snippets render raw HTML** — deliberate, so a Meta Pixel or Hotjar tag can be pasted in, but anyone with `cms.manage` can inject script into every public page. Consider restricting to `settings.manage`

---

## 4. Analytics

- [x] ✅ **Self-hosted page view tracking** — no raw IP stored, bots recorded but never counted, logged-in staff excluded, per course and per service attribution
- [x] ✅ **Analytics dashboard** — KPIs with period-on-period change, traffic chart, top pages/courses/services, referrers, UTM campaigns, device/browser/platform, time of day, latest views, range presets and custom dates
- [ ] ⚠️ **Retention depends on the scheduler** — `analytics:prune` is scheduled daily but **nothing confirms `php artisan schedule:run` is set up on the server.** Without it `page_views` grows forever

---

## 5. Notifications

- [x] ✅ **Rule engine** — an event notifies a role or a named user, per-rule channel choice, role rules resolve at send time so new staff are covered automatically
- [x] ✅ **`income.created` and `expense.created`** wired and seeded for admin, accountant and managing director
- [x] ✅ **Bell in the sidebar** — unread badge, dropdown, click-to-read, mark all read, polls every 60s
- [x] ✅ **Notifications page** — paginated history, dismiss, deep links
- [x] ✅ **Admin rules screen** — grouped by event, inline enable/disable, gated on `settings.manage`
- [ ] ⚠️ **Email is not actually delivered** — `MAIL_MAILER=log`, so mail goes to `storage/logs`. In-app works. Needs SMTP configured before the email channel is usable
- [ ] ⚠️ **Notifications are sent synchronously** — deliberate, because `QUEUE_CONNECTION=database` with no worker running would leave them queued and the bell empty. If a worker is added, add `ShouldQueue` to `FinanceEntryCreated`
- [ ] ☐ **Approval events** — rejected, sent back, fully approved. The engine is generic; each is a config entry plus one `dispatch()` call

---

## 6. Training — the ASSET work

- [x] ✅ **Custom registration fields** — `field_definitions` + `field_values`, per project, drag-to-reorder builder with live preview under each project, dynamic trainee form, 8 field types, key frozen once answers exist
- [ ] ⚠️ **`trainees.nid` is NOT NULL UNIQUE** — students under 18 have no NID and cannot be registered at all. Must become nullable, with `birth_certificate_no` added
- [ ] ⚠️ **Trainees are not linked to batches** — `project_id` was added this session, but there is still no enrolment record
- [ ] ⚠️ **`projects.completed_trainees` is a typed number** — should be derived from completed enrolments
- [ ] ☐ **`projects.code` and `is_training`** — needed for certificate numbering
- [ ] ☐ **Trades** — the operational training unit, with `competency_level` as a string and a 1–6 dropdown
- [ ] ☐ **Batches** — a cohort of one trade under one project
- [ ] ☐ **Enrolments** — a student in a batch, with derived attendance and result
- [ ] ☐ **Attendance** — `sessions` + `attendances`, daily entry screen, calculated percentage
- [ ] ☐ **Assessment** — `assessment_units` per trade, `assessment_results`, marking grid, derived overall result
- [ ] ☐ **Export** — Excel + PDF on every list. `maatwebsite/excel` and `barryvdh/laravel-dompdf` are **not installed yet**
- [ ] ☐ **Import** — template download, dry run, error report, `import_batches` history
- [ ] ☐ **Certificates** — issue, PDF, public `/verify/{code}` page with a random code. Needs a QR package decision
- [ ] ☐ **Project reporting** — cost by category against training outcomes

Full design: [`training-module-spec.md`](training-module-spec.md). Build order is in section 11 of that document.

---

## 7. API for the Android app

- [ ] ⚠️ **`AddApiKeyHeader` emits a key rather than checking one** — it stamps `X-API-KEY` on the response from config. Nothing validates an inbound key, so `/api/income/categories` is open. **Remove it**
- [ ] ☐ **Sanctum** — not installed. No `api` guard exists in `config/auth.php`
- [ ] ☐ **`/api/v1` routes** — auth, expenses, categories, attachments, approvals
- [ ] ☐ **API Resources** — the `/data` endpoints return HTML for DataTables and cannot be reused
- [ ] ☐ **Form Requests + Services** — validation is inline in controllers; a second client means every rule would be written twice
- [ ] ☐ **2FA path for the app** — every user has 2FA and Fortify's challenge is a web redirect

---

## 8. Operations and housekeeping

- [x] ✅ **Assets** — full CRUD, gated on `assets.manage`
- [x] ✅ **Projects and project categories** — CRUD (but unguarded, see section 1)
- [x] ✅ **Activity log** — automatic, skips `.data` routes and itself
- [ ] ⚠️ **Activity log records visits, not changes** — no old value, no new value. It answers "who opened this page", not "who changed this figure"
- [ ] ⚠️ **`GeneralController.php` is an empty file** — no class, no namespace, no route. Delete it
- [ ] ⚠️ **No queue worker** — `QUEUE_CONNECTION=database` and the `jobs` table exists, but nothing processes it. Fine today because nothing is queued; becomes a problem the moment something is

---

## 9. Questions still open

These block specific pieces of work. Ordered by how soon they matter.

| # | Question | Blocks |
| --- | --- | --- |
| 1 | Users screen only, or full role/permission admin in the database? | Section 1 |
| 2 | Do you reimburse staff for their own spending? If so, expenses need `paid_by` separate from `created_by` | Finance cleanup |
| 3 | Can staff edit an expense once it is `pending`, or is a draft state needed? | Finance + API |
| 4 | Does ASSET mandate a certificate number format? | Certificates — expensive to change later |
| 5 | Approve `endroid/qr-code` as a third package, or print the verification URL as text? | Certificates |
| 6 | Certificates in English, Bangla, or both? | PDF template and font setup |
| 7 | Show the student photo on the public verification page? | Verification page |
| 8 | A separate `certificates.issue` permission, or is `training.manage` enough? | Certificates |
| 9 | Minimum attendance percentage before a student can be certified? | Attendance + certificates |
| 10 | Do trades use numeric marks, competent/not-competent, or does it vary? | Assessment |
| 11 | Should the app queue expenses captured offline and sync later? | API design — very hard to retrofit |

---

## 10. Suggested order

**Now — small, and currently exploitable**

1. Users screen (`users.manage`) — unblocks staffing entirely
2. Gates on the five unguarded controllers
3. Delete `GeneralController.php`

**Next — finish finance before the app is built against it**

4. Income category FK, matching expenses
5. `created_by` required on both tables
6. `attachments` table, replacing single-file uploads
7. `project_id` on entries, for ASSET spending
8. `Approvable` trait + `ApprovalService`, fixing the superadmin approval bug and the `sent_back` dead end at the same time

**Then — the two large tracks, which can run in parallel**

9. Training: trades → batches → enrolments → attendance → assessment → export → import → certificates
10. API: Sanctum → v1 routes → resources → the Android app

**Before go-live**

11. Configure SMTP so email notifications work
12. Confirm `schedule:run` is set up on the server
13. Set a queue worker if anything is moved to the queue
14. Turn off "allow search engines" on any staging copy
