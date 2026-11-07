# Sections

## 1. What the feature does

Create, open, rename, describe, and delete categories inside a selected vault. Opening a section shows its searchable problems.

## 2. Implementing files

`SectionController.php`, `SectionRequest.php`, `Models/Section.php`, `SectionFactory.php`, `pages/vaults/Show.vue`, `pages/sections/Show.vue`, `CollectionDialog.vue`, `CollectionCard.vue`, and `ProblemList.vue`.
Backend paths are under `app/Http/Controllers`, `app/Http/Requests`,
`app/Models`, and `database/factories`; Vue paths are under `resources/js`.
Routes are in `routes/web.php`; all four tables are in the knowledge-vault migration.

## 3. Frontend communication

The vault detail page opens `CollectionDialog` in section mode. Inertia sends POST or PUT requests through `@/routes/vaults/sections`. `show` receives both resolved parents and renders a section with problem pagination and the current search filter. Breadcrumbs link back to the vault list and parent vault.

## 4. Controller, model, and database

`$vault->sections()->create(...)` supplies vault_id from the bound parent, never client input. Section detail passes a problem query constrained by the bound section ID to `ProblemSearch`, which applies search, filters, and pagination. Sections list by name and include problem counts. Deletion removes the section and cascades to its problems and attempts, then redirects to the surviving vault.

## 5. Validation and errors

Name is required and capped at 120 characters; description is optional up to 10,000. Inertia exposes the request errors to the dialog. Scoped binding returns 404 if a section is requested under another vault, including update/delete and downstream create routes. This protects hierarchy rather than individual users.

## 6. Testing

`tests/Feature/SectionCrudTest.php` covers creation, reading through the parent, update, validation, ignored forged vault_id, wrong-vault GET/PUT/DELETE requests, and cascading deletion that preserves the parent. Run `php artisan test --filter=SectionCrud`.

## 7. Implementation decisions

The database owns referential integrity. The UI reuses collection components but sections do not support covers. Parent association is immutable in the MVP; moving sections between vaults is not implemented. The vault page paginates sections at 12 entries.

## 8. Rebuild it independently

1. Create a section table with a vault foreign key and cascade deletion.
2. Define both belongsTo and hasMany relationships.
3. Create sections through the parent relationship.
4. Enable scoped binding on nested routes and test a deliberately wrong parent.
5. Reuse the collection dialog without introducing file fields for sections.
6. Add the problem list to section detail and test cascade boundaries.
