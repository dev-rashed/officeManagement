# Implementation Checklist

Use this checklist while implementing the frontend CMS, course CMS, services CMS, office asset management, employee management, and salary management features.

## Discovery

- [x] Review `layout/` design files and identify reusable header, footer, navigation, and section patterns.
- [x] Review existing admin Blade layout and form/table patterns.
- [x] Review existing route organization in `routes/web.php`.
- [x] Review existing controller and model naming conventions.
- [x] Review existing income module files: `IncomeController`, `IncomeEntry`, `IncomeCategory`, income migrations, and income Blade views.
- [x] Review existing expense module files: `ExpenseController`, `ExpenseEntry`, expense migration, and expense Blade views.
- [x] Review existing approval flow in `Approval`, `IncomeEntry`, and `ExpenseEntry`.
- [x] Review finance permissions in `config/permissions.php` and `App\Providers\AppServiceProvider`.
- [x] Confirm how file uploads are currently handled, if any.
- [x] Confirm whether roles or permissions already exist.

## Existing Finance Module Guardrails

- [x] Do not recreate income CRUD; use the existing `income.*` routes and `IncomeEntry` model.
- [x] Do not recreate expense CRUD; use the existing `expense.*` routes and `ExpenseEntry` model.
- [x] Keep existing income category management intact.
- [x] Keep existing approval status constants compatible for income and expense.
- [x] Keep the approval stage order: Managing Director, Director, Chairman.
- [x] Keep finance editor checks aligned with `finance.manage`.
- [x] Preserve current attachment storage paths: `uploads/income` and `uploads/expense`.
- [x] Preserve current sidebar links for income, income categories, and expense.
- [x] If adding expense categories later, avoid breaking existing `expense_category` string data.
- [x] If adding finance dashboards or reports, aggregate from existing `income_entries` and `expense_entries` tables.

## Frontend Conversion

- [x] Create public frontend route group.
- [x] Convert `layout/index.html` into a Blade home page.
- [x] Convert `layout/about.html` into a Blade about page.
- [x] Convert `layout/courses.html` into a Blade course listing page.
- [x] Convert `layout/course-details.html` into a Blade course details page.
- [x] Convert `layout/services.html` into a Blade services page.
- [x] Convert service detail designs from `layout/services/` where needed.
- [x] Convert `layout/contact.html` into a Blade contact page.
- [x] Convert mission, vision, team, and portfolio pages as needed.
- [x] Extract shared frontend partials/components.
- [x] Move or reference design assets from `layout/css`, `layout/js`, and `layout/images` safely.
- [ ] Verify frontend pages match the provided design closely.

## CMS Foundation

- [x] Decide CMS table structure for pages, sections, media fields, and repeatable content.
- [x] Create migrations for CMS tables.
- [x] Create models and relationships.
- [x] Create admin controllers.
- [x] Create admin routes protected by authentication.
- [x] Create admin sidebar/menu links.
- [x] Create Blade views for CMS list, create, edit, show if needed, and delete flows.
- [x] Add request validation.
- [x] Add publish/unpublish or visibility status handling.
- [x] Add sort order handling where content order matters.
- [x] Add visible admin sidebar options for Website CMS, Course CMS, Service CMS, Portfolio, Contact Settings, and Assets.
- [x] Seed editable default CMS content for admin preview.

## Course CMS

- [x] Create courses migration.
- [x] Add course model with slug handling.
- [x] Add course admin controller.
- [x] Add authenticated course admin routes.
- [x] Add course list page in admin.
- [x] Add course create and edit forms.
- [x] Add course delete flow.
- [x] Add course publish/unpublish status.
- [x] Add featured image upload support.
- [x] Add course category support if required.
- [x] Show only published courses on the frontend course listing page.
- [x] Show course detail pages by slug.
- [x] Make course content clearly describe offline course details.
- [x] Avoid online course features such as lesson progress, quizzes, payment, and carts.

## Services CMS

- [x] Create services migration.
- [x] Add service model with slug handling.
- [x] Add service admin controller.
- [x] Add authenticated service admin routes.
- [x] Add service list page in admin.
- [x] Add service create and edit forms.
- [x] Add service delete flow.
- [x] Add service publish/unpublish status.
- [x] Add frontend visibility controls.
- [x] Add service image or icon support.
- [x] Add sort order support.
- [ ] Show visible published services on the frontend services page.
- [x] Show service detail pages if supported by the selected design.

## Office Asset Management

- [ ] Create asset categories table if categories need to be managed.
- [x] Create office assets migration.
- [x] Add office asset model.
- [x] Add asset admin controller.
- [x] Add authenticated asset routes.
- [x] Add asset list page with search.
- [x] Add filters for category, status, location, assigned user, and date range where practical.
- [x] Add asset create and edit forms.
- [x] Add asset detail page.
- [x] Add asset delete flow.
- [x] Add status values such as active, in repair, retired, lost, and disposed.
- [x] Add assignment fields for user, department, room, or project if supported.
- [x] Add image or attachment support if needed.
- [x] Keep assets separate from income and expense records unless a clear accounting link is required.
- [ ] If assets need approval, reuse the existing polymorphic `approvals` table pattern.
- [ ] If assets need finance references, link to existing `IncomeEntry` or `ExpenseEntry` records instead of duplicating finance fields.

## Employee Management

- [ ] Decide employee profile fields such as employee ID, designation, department, joining date, employment status, contact details, and emergency contact.
- [ ] Create departments or designations tables if they need to be managed separately.
- [ ] Create employees migration.
- [ ] Add employee model and relationships.
- [ ] Add employee admin controller.
- [ ] Add authenticated employee routes.
- [ ] Add employee list page with search.
- [ ] Add filters for department, designation, status, and joining date where practical.
- [ ] Add employee create and edit forms.
- [ ] Add employee detail page.
- [ ] Add employee delete or deactivate flow.
- [ ] Add status values such as active, probation, resigned, terminated, and on leave.
- [ ] Add profile photo, documents, or attachment support if needed.
- [ ] Link employees to user accounts only when login access is required.
- [ ] Keep employee records separate from trainees unless a clear relationship is required.
- [ ] If employee changes need approval, reuse the existing polymorphic `approvals` table pattern.

## Salary Management

- [ ] Decide salary structure fields such as basic salary, allowances, deductions, bonuses, net salary, payment date, and payment status.
- [ ] Create salary records migration.
- [ ] Add salary model and relationship to employees.
- [ ] Add salary admin controller.
- [ ] Add authenticated salary routes.
- [ ] Add salary list page with employee search.
- [ ] Add filters for employee, department, month, year, payment status, and date range where practical.
- [ ] Add salary create and edit forms.
- [ ] Add salary detail or payslip page.
- [ ] Add salary delete or correction flow.
- [ ] Add salary status values such as draft, pending approval, approved, paid, and cancelled.
- [ ] Add payslip generation or printable view if required.
- [ ] Add attachment support for payment proof if needed.
- [ ] If salary payments affect accounting, link to existing `ExpenseEntry` records instead of duplicating expense fields.
- [ ] If salary approval is required, reuse the existing polymorphic `approvals` table pattern.

## Validation And Security

- [x] Validate all create and update requests.
- [x] Ensure public pages only show published or visible records.
- [x] Ensure CMS, services, courses, and assets admin routes require login.
- [x] Add authorization checks if role or permission support exists.
- [x] Sanitize or safely render rich text content.
- [x] Validate uploaded files by type and size.

## Testing

- [ ] Test existing income list, create, edit, show, delete, and approval flows still work.
- [ ] Test existing income category list, create, edit, delete, and detail analytics still work.
- [ ] Test existing expense list, create, edit, show, delete, and approval flows still work.
- [x] Test public home page renders.
- [x] Test public course listing renders published courses only.
- [x] Test public course detail page resolves by slug.
- [x] Test unpublished courses are hidden.
- [ ] Test public services page renders visible published services only.
- [ ] Test unpublished or hidden services are hidden.
- [ ] Test CMS CRUD flows.
- [ ] Test course CRUD flows.
- [ ] Test service CRUD flows.
- [ ] Test asset CRUD flows.
- [ ] Test asset search and filters.
- [ ] Test employee CRUD flows.
- [ ] Test employee search and filters.
- [ ] Test salary CRUD flows.
- [ ] Test salary search, filters, and payslip view if added.
- [ ] Run Laravel test suite.
- [ ] Run formatting or linting tools used by the project.

## Final Verification

- [ ] Confirm no existing finance, project, trainee, auth, or settings pages were broken.
- [ ] Confirm frontend design matches `layout/` visually.
- [x] Confirm routes are named consistently.
- [ ] Confirm database migrations run from a fresh database.
- [ ] Confirm uploaded assets display correctly.
- [x] Document any remaining assumptions or follow-up work.

## Analysis Notes

- Finance review completed: existing income, expense, approval, permission, route, and upload-path patterns are intact.
- Public, CMS, course, service, and asset routes are registered; `php artisan route:list` completed successfully and showed 159 routes.
- Employee management and salary management are not implemented yet.
- Asset categories are currently plain strings on the `assets` table; there is no separate managed asset categories table.
- Asset approvals and finance references are not implemented, and are only needed if those workflows are requested.
- Service visibility needs a follow-up decision: services have `show_on_homepage`, but the public service list/detail currently filter by `status = published` only.
- `php artisan test` is blocked in this environment because PHP is missing the SQLite PDO driver. Unit tests that do not touch the database passed before feature tests failed at database connection setup.
- `vendor/bin/pint --test` failed because multiple files need formatting fixes.
