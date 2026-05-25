# Implementation Checklist

Use this checklist while implementing the frontend CMS, course CMS, services CMS, office asset management, employee management, and salary management features.

## Discovery

- [ ] Review `layout/` design files and identify reusable header, footer, navigation, and section patterns.
- [ ] Review existing admin Blade layout and form/table patterns.
- [ ] Review existing route organization in `routes/web.php`.
- [ ] Review existing controller and model naming conventions.
- [ ] Review existing income module files: `IncomeController`, `IncomeEntry`, `IncomeCategory`, income migrations, and income Blade views.
- [ ] Review existing expense module files: `ExpenseController`, `ExpenseEntry`, expense migration, and expense Blade views.
- [ ] Review existing approval flow in `Approval`, `IncomeEntry`, and `ExpenseEntry`.
- [ ] Review finance permissions in `config/permissions.php` and `App\Providers\AppServiceProvider`.
- [ ] Confirm how file uploads are currently handled, if any.
- [ ] Confirm whether roles or permissions already exist.

## Existing Finance Module Guardrails

- [ ] Do not recreate income CRUD; use the existing `income.*` routes and `IncomeEntry` model.
- [ ] Do not recreate expense CRUD; use the existing `expense.*` routes and `ExpenseEntry` model.
- [ ] Keep existing income category management intact.
- [ ] Keep existing approval status constants compatible for income and expense.
- [ ] Keep the approval stage order: Managing Director, Director, Chairman.
- [ ] Keep finance editor checks aligned with `finance.manage`.
- [ ] Preserve current attachment storage paths: `uploads/income` and `uploads/expense`.
- [ ] Preserve current sidebar links for income, income categories, and expense.
- [ ] If adding expense categories later, avoid breaking existing `expense_category` string data.
- [ ] If adding finance dashboards or reports, aggregate from existing `income_entries` and `expense_entries` tables.

## Frontend Conversion

- [ ] Create public frontend route group.
- [ ] Convert `layout/index.html` into a Blade home page.
- [ ] Convert `layout/about.html` into a Blade about page.
- [ ] Convert `layout/courses.html` into a Blade course listing page.
- [ ] Convert `layout/course-details.html` into a Blade course details page.
- [ ] Convert `layout/services.html` into a Blade services page.
- [ ] Convert service detail designs from `layout/services/` where needed.
- [ ] Convert `layout/contact.html` into a Blade contact page.
- [ ] Convert mission, vision, team, and work pages as needed.
- [ ] Extract shared frontend partials/components.
- [ ] Move or reference design assets from `layout/css`, `layout/js`, and `layout/images` safely.
- [ ] Verify frontend pages match the provided design closely.

## CMS Foundation

- [ ] Decide CMS table structure for pages, sections, media fields, and repeatable content.
- [ ] Create migrations for CMS tables.
- [ ] Create models and relationships.
- [ ] Create admin controllers.
- [ ] Create admin routes protected by authentication.
- [ ] Create admin sidebar/menu links.
- [ ] Create Blade views for CMS list, create, edit, show if needed, and delete flows.
- [ ] Add request validation.
- [ ] Add publish/unpublish or visibility status handling.
- [ ] Add sort order handling where content order matters.

## Course CMS

- [ ] Create courses migration.
- [ ] Add course model with slug handling.
- [ ] Add course admin controller.
- [ ] Add authenticated course admin routes.
- [ ] Add course list page in admin.
- [ ] Add course create and edit forms.
- [ ] Add course delete flow.
- [ ] Add course publish/unpublish status.
- [ ] Add featured image upload support.
- [ ] Add course category support if required.
- [ ] Show only published courses on the frontend course listing page.
- [ ] Show course detail pages by slug.
- [ ] Make course content clearly describe offline course details.
- [ ] Avoid online course features such as lesson progress, quizzes, payment, and carts.

## Services CMS

- [ ] Create services migration.
- [ ] Add service model with slug handling.
- [ ] Add service admin controller.
- [ ] Add authenticated service admin routes.
- [ ] Add service list page in admin.
- [ ] Add service create and edit forms.
- [ ] Add service delete flow.
- [ ] Add service publish/unpublish status.
- [ ] Add frontend visibility controls.
- [ ] Add service image or icon support.
- [ ] Add sort order support.
- [ ] Show visible published services on the frontend services page.
- [ ] Show service detail pages if supported by the selected design.

## Office Asset Management

- [ ] Create asset categories table if categories need to be managed.
- [ ] Create office assets migration.
- [ ] Add office asset model.
- [ ] Add asset admin controller.
- [ ] Add authenticated asset routes.
- [ ] Add asset list page with search.
- [ ] Add filters for category, status, location, assigned user, and date range where practical.
- [ ] Add asset create and edit forms.
- [ ] Add asset detail page.
- [ ] Add asset delete flow.
- [ ] Add status values such as active, in repair, retired, lost, and disposed.
- [ ] Add assignment fields for user, department, room, or project if supported.
- [ ] Add image or attachment support if needed.
- [ ] Keep assets separate from income and expense records unless a clear accounting link is required.
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

- [ ] Validate all create and update requests.
- [ ] Ensure public pages only show published or visible records.
- [ ] Ensure CMS, services, courses, assets, employees, and salaries admin routes require login.
- [ ] Add authorization checks if role or permission support exists.
- [ ] Sanitize or safely render rich text content.
- [ ] Validate uploaded files by type and size.

## Testing

- [ ] Test existing income list, create, edit, show, delete, and approval flows still work.
- [ ] Test existing income category list, create, edit, delete, and detail analytics still work.
- [ ] Test existing expense list, create, edit, show, delete, and approval flows still work.
- [ ] Test public home page renders.
- [ ] Test public course listing renders published courses only.
- [ ] Test public course detail page resolves by slug.
- [ ] Test unpublished courses are hidden.
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
- [ ] Confirm routes are named consistently.
- [ ] Confirm database migrations run from a fresh database.
- [ ] Confirm uploaded assets display correctly.
- [ ] Document any remaining assumptions or follow-up work.
