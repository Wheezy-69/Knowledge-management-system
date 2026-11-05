# Code attempts

## 1. What the feature does

Save multiple approaches to a problem, including source code, optional notes, and an editable order number. Edit, reorder, or delete attempts without changing the corrected solution.

## 2. Implementing files

`AttemptController.php`, `AttemptRequest.php`, `Models/Attempt.php`, `AttemptFactory.php`, `AttemptDialog.vue`, `pages/problems/Show.vue`, `CodeBlock.vue`, and `bootstrap/app.php` (code-whitespace preservation).
Backend paths are under `app/Http/Controllers`, `app/Http/Requests`,
`app/Models`, and `database/factories`; Vue paths are under `resources/js`.
Routes are in `routes/web.php`; all four tables are in the knowledge-vault migration.

## 3. Frontend communication

The problem detail page initializes `AttemptDialog` with the next unused order above the current maximum. Edit initializes the existing values. Inertia POST/PUT/DELETE calls use `@/routes/vaults/sections/problems/attempts`. Mutations redirect back, preserving scroll; the refreshed problem props contain the saved attempts. Each attempt has separate editing and confirmation controls.

## 4. Controller, model, and database

The controller creates via `$problem->attempts()`; client problem_id is ignored. Scoped binding resolves updates/deletes only from that problem. The relationship orders by position. A unique index on problem_id plus position backs the validation rule. Deletion removes only the attempt, retains its siblings, and leaves the corrected_solution field alone.

## 5. Validation and errors

Code is required (100,000 maximum); notes optional (10,000). Position must be an integer from 1 to 1,000,000 and unique within the problem. Updates ignore their own row in uniqueness validation. Another problem may reuse the same position. Laravel does not trim code fields; whitespace-only code is still invalid. Field errors appear inline and saving disables repeated submission.

## 6. Testing

`tests/Feature/AttemptCrudTest.php` exercises multiple saves, read ordering, updates, deletion, code whitespace, all required/size/order rules, uniqueness and self-exclusion, cross-problem access, and forged parent IDs. It confirms the corrected solution remains unchanged. Run `php artisan test --filter=AttemptCrud`.

## 7. Implementation decisions

Order numbers are stable and may contain gaps; deleting does not renumber history. Reordering edits the number; swaps use a temporary unused number. All attempts are displayed on the problem page, with no standalone attempt route or pagination. Code is highlighted but never executed. Simultaneous writes are constrained by the database; a rare racing unique constraint failure may require retrying in this single-user MVP.

## 8. Rebuild it independently

1. Create an attempts table with a problem foreign key and unique composite order index.
2. Add the ordered hasMany relation on Problem.
3. Validate order against the route-bound problem and ignore the edited row.
4. Save through the parent relationship and preserve source whitespace in middleware.
5. Build a dedicated attempt dialog and display each record separately from the solution.
6. Test collisions, wrong parents, ordering, editing, and deletion before adding UI polish.
