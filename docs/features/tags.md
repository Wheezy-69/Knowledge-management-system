# Reusable tags

## 1. What the feature does

Create unused reusable tags, assign/remove them while editing problems, rename them everywhere, and delete a tag without deleting problems.

## 2. Implementing files

Models/Tag.php; Services/ProblemTags.php; TagController.php; ProblemRequest.php; tags/Index.vue; TagDialog.vue; SearchFiltersForm.vue; additive expansion migration.
PHP classes live under `app/`; Vue and TypeScript paths live under `resources/js/`.
Routes are declared in `routes/web.php`.

## 3. Frontend communication

The Tags page uses Inertia POST/PUT/DELETE via generated helpers. ProblemDialog keeps comma-separated entry and submits an array. Existing catalog names can be reused by typing them; unknown names create new tags.

## 4. Controller, model, and database

The tags table has a normalized unique key. problem_tag is the many-to-many pivot. ProblemTags updates pivot assignments and the existing JSON tags field together. Rename and deletion update linked JSON labels and the catalog within a transaction. Original JSON was retained during migration and backfilled into the new relationships.

## 5. Validation and errors

Names are trimmed, repeated whitespace collapsed, and keys lowercased with multibyte support. Name/key length is 40; duplicate normalized names are rejected. Per-problem arrays allow up to 12 distinct names. Native text feedback shows name/key validation errors.

## 6. Testing

KnowledgeExpansionTest covers duplicate names, assignment/removal, rename propagation, deletion without problem loss, and filtering. Existing ProblemCrudTest covers per-problem tag validation.

## 7. Implementation decisions

JSON remains alongside normalized relationships as a compatibility copy. Do not edit either table manually. Deleting a problem leaves unused catalog tags available; remove them explicitly on the Tags page if desired. No existing vault/section associations were changed.

## 8. Rebuild independently

Add the catalog and pivot with unique keys. Backfill without dropping JSON. Centralize synchronization, test it, then build management forms and combined filters.
