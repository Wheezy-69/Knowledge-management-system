# Vaults

## 1. What the feature does

Create, list, open, edit, and delete top-level collections, with optional raster cover images.

## 2. Implementing files

`VaultController.php`, `VaultRequest.php`, `Models/Vault.php`, `VaultFactory.php`, `pages/vaults/{Index,Show}.vue`, `CollectionCard.vue`, `CollectionDialog.vue`, and `DeleteDialog.vue`.
Backend paths are under `app/Http/Controllers`, `app/Http/Requests`,
`app/Models`, and `database/factories`; Vue paths are under `resources/js`.
Routes are in `routes/web.php`; all four tables are in the knowledge-vault migration.

## 3. Frontend communication

`CollectionDialog` uses Inertia `useForm` and `@/routes/vaults` helpers. Creation posts to `/vaults`; editing posts multipart data to the generated update URL with `_method=put` so PHP accepts file uploads. `show` renders sections; `index` paginates vaults. Delete uses Inertia DELETE after confirmation.

## 4. Controller, model, and database

The controller creates or updates a Vault from allowed fields. Covers are stored on the private local disk under `vault-covers`. `cover_url` is appended for display while `cover_path` is hidden. The cover GET action streams the image. Replacing or removing a cover deletes the previous file after saving. Deleting a vault cascades through sections, problems, and attempts and removes its cover.

## 5. Validation and errors

Name is required (120 characters), description optional (10,000). Cover must be JPEG, PNG, WebP, or GIF up to 2 MB; SVG and oversized uploads are rejected. Upload failure returns a cover-field validation error. The form shows messages next to fields, upload progress, and a disabled saving state.

## 6. Testing

`tests/Feature/VaultCrudTest.php` covers CRUD, field validation, upload serving/replacement/removal, failed storage writes, invalid files, pagination, and cascades. The existing `VaultsTest.php` verifies intentional unauthenticated local access. Run `php artisan test --filter=Vault`.

## 7. Implementation decisions

Covers are served privately through Laravel rather than requiring a storage symlink. All users who can reach this local app share one workspace: there is no user ownership or authentication yet. Vault and section listing use 12 items per page. There are no create/edit pages because dialogs handle those operations.

## 8. Rebuild it independently

1. Create the vault table and model with an explicit fillable list.
2. Add the validated store/update actions and named resource routes.
3. Build the listing and detail pages before adding the dialog.
4. Use the generated Wayfinder helpers for navigation and submissions.
5. Add private upload storage and serving, then test rejection and replacement.
6. Implement cascade deletion with confirmation; run tests on a fake disk, never real covers.
