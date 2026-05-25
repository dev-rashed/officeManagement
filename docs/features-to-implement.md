# Features To Implement

This document is the implementation brief for the next agent working on this Laravel office management project.

## Current Project Context

- Backend framework: Laravel.
- Existing admin modules include finance, projects, project categories, trainees, settings, auth, and activity logs.
- Existing static frontend designs are stored in the `layout/` folder.
- The frontend implementation must reuse the provided designs from `layout/` instead of creating a new design direction.

## Existing Finance Module Analysis

Income and expense management already exist. Do not rebuild these modules from scratch.

Existing income files:

- Model: `app/Models/IncomeEntry.php`.
- Controller: `app/Http/Controllers/IncomeController.php`.
- Category model: `app/Models/IncomeCategory.php`.
- Category controller: `app/Http/Controllers/Finance/IncomeCategoryController.php`.
- Views: `resources/views/pages/finance/income/`.
- Category views: `resources/views/pages/finance/income/categories/`.
- Tables: `income_entries` and `income_categories`.
- Routes: `income.*` and `income.categories.*` inside the authenticated route group in `routes/web.php`.

Existing expense files:

- Model: `app/Models/ExpenseEntry.php`.
- Controller: `app/Http/Controllers/ExpenseController.php`.
- Views: `resources/views/pages/finance/expense/`.
- Table: `expense_entries`.
- Routes: `expense.*` inside the authenticated route group in `routes/web.php`.

Existing approval system:

- Shared model: `app/Models/Approval.php`.
- Shared table: `approvals`.
- Income and expense entries both use a polymorphic `approvals()` relation.
- Approval stages are Managing Director, Director, and Chairman.
- Approval statuses include pending, pending director, pending chairman, fully approved, rejected, and sent back.
- Approval permissions are role-based through `User` helpers such as `isManagingDirector()`, `isDirector()`, and `isChairman()`.

Existing finance permissions:

- `finance.view`.
- `finance.manage`.
- `approvals.manage`.
- Superadmin has `*`.
- Admin and accountant can manage finance.
- Chairman, managing director, and director can view finance and manage approvals.

Existing income entry fields:

- Title.
- Source category.
- Amount.
- Date.
- Payment method.
- Reference number.
- Attachment path.
- Description.
- Status.
- Created by.

Existing expense entry fields:

- Title.
- Expense category.
- Amount.
- Date.
- Payment method.
- Vendor name.
- Reference number.
- Attachment path.
- Description.
- Status.
- Created by.

Important implementation notes:

- Income already has category management with active and inactive statuses, AJAX table data, a category detail analytics page, and an API endpoint for categories.
- Expense currently stores `expense_category` as a string and does not have a separate expense category CRUD module.
- Income list uses AJAX/DataTables-style server data through `income/data`.
- Expense list currently uses Laravel pagination from the controller.
- Attachments are stored on the public disk under `uploads/income` and `uploads/expense`.
- Finance editor actions are guarded by `finance.manage`.
- Future finance-related work should reuse the existing approval and permission patterns.
- Do not break or rename existing income and expense routes because current views and sidebar links depend on them.

## Frontend Website

Build the public-facing website from the static design files in `layout/`.

Design sources:

- `layout/index.html` for home page.
- `layout/about.html` for about page.
- `layout/courses.html` for course listing page.
- `layout/course-details.html` for course details page.
- `layout/services.html` and files inside `layout/services/` for services pages.
- `layout/contact.html` for contact page.
- `layout/mission.html` and `layout/vision.html` for mission and vision pages.
- `layout/team.html` for team page.
- `layout/work.html` for work or portfolio page.
- `layout/css`, `layout/js`, and `layout/images` for visual assets.

Implementation expectations:

- Convert static HTML into Laravel Blade views.
- Keep the layout, spacing, typography, colors, and visual behavior close to the provided design.
- Move reusable parts into Blade partials/components where appropriate, such as header, footer, navigation, scripts, and common sections.
- Serve public frontend routes separately from authenticated office management routes.
- Do not hard-code content that should be CMS-managed.

## CMS For Frontend Content

Create CMS management for public website content from the admin panel.

The CMS should support:

- Managing page content for home, about, mission, vision, team, contact, and work pages.
- Managing hero sections, titles, subtitles, body text, images, buttons, and visibility status.
- Uploading and replacing images used on the public website.
- Sorting visible content where order matters.
- Publishing or hiding frontend content.

Suggested model areas:

- Pages or page sections.
- Media or image fields.
- Team members.
- Work or portfolio items.
- Contact information and social links.

Use simple, maintainable CRUD screens consistent with the existing admin UI.

## Course CMS

Courses need to be dynamic on the frontend, but they are offline courses only. The user should be able to view course lists and details, not purchase or complete a full online course.

Course requirements:

- Admin can create, edit, delete, publish, and unpublish courses.
- Frontend can list published courses.
- Frontend can show course detail pages.
- Courses are offline, so do not build video lessons, online class progress, quizzes, carts, payments, or enrollment workflows unless requested later.

Suggested course fields:

- Title.
- Slug.
- Short description.
- Full description.
- Featured image.
- Category.
- Duration.
- Class type, such as offline.
- Location.
- Schedule or batch time.
- Fee or price, if needed.
- Instructor name, if needed.
- Course outline or modules as text/repeater content.
- Status, such as draft or published.
- Sort order.

Frontend behavior:

- Course list page should use the design from `layout/courses.html`.
- Course detail page should use the design from `layout/course-details.html`.
- Only published courses should be visible publicly.
- Details should clearly communicate that courses are offline.

## Services CMS

Create a services module in the CMS with frontend visibility.

Service requirements:

- Admin can create, edit, delete, publish, and unpublish services.
- Admin can control frontend visibility and sorting.
- Frontend can list visible services.
- Frontend can show service details if the design supports detail pages.

Suggested service fields:

- Title.
- Slug.
- Short description.
- Full description.
- Featured image or icon.
- Service category, if needed.
- Status, such as draft or published.
- Show on homepage flag.
- Sort order.

Frontend behavior:

- Service list page should use `layout/services.html`.
- Service detail pages should follow the design files inside `layout/services/` where applicable.
- Only published and visible services should appear on the public website.

## Office Asset Management

Add an office asset management feature to the authenticated office management area.

Asset requirements:

- Admin can create, edit, delete, and view office assets.
- Admin can list all office assets with search and filtering.
- Asset records should support status tracking.
- Asset records should support assignment to a person, department, room, or project if the existing app structure allows it.

Suggested asset fields:

- Asset name.
- Asset code or tag number.
- Category.
- Brand.
- Model.
- Serial number.
- Purchase date.
- Purchase cost.
- Current value, if needed.
- Vendor or supplier.
- Location.
- Assigned user or owner.
- Condition.
- Status, such as active, in repair, retired, lost, or disposed.
- Notes.
- Attachment or image, if needed.

Suggested list filters:

- Category.
- Status.
- Location.
- Assigned user.
- Purchase date range.

Finance integration guidance:

- Office assets should be a new module, not a replacement for income or expense.
- If asset purchasing needs financial tracking later, link or reference an `ExpenseEntry` instead of duplicating the expense workflow.
- If asset disposal or sale creates income later, link or reference an `IncomeEntry` instead of duplicating the income workflow.
- If asset approval is required, reuse the existing polymorphic `Approval` model pattern.
- Protect asset management with a dedicated permission if adding one, such as `assets.manage`; otherwise follow the current role/permission style used by finance.

## Permissions And Access

- Public frontend pages should be accessible without login.
- CMS and office asset management should require authentication.
- Reuse the existing auth and admin layout patterns.
- Add authorization checks if the project already has role or permission support.

## Data And Migration Notes

- Add migrations for all new CMS, course, service, and asset tables.
- Use slugs for public course and service detail URLs.
- Add useful indexes for slug, status, visibility, and sort order fields.
- Add seed data only if helpful for local testing and frontend preview.

## Quality Expectations

- Follow existing Laravel naming, controller, model, route, and Blade patterns.
- Extend the existing income and expense modules only when required; do not create duplicate finance modules.
- Keep frontend views maintainable by extracting shared layout pieces.
- Validate all admin form input.
- Use file upload handling that stores paths consistently.
- Add tests for core CRUD behavior and public visibility rules where practical.
- Avoid changing existing finance/project behavior unless required by shared layout updates.
