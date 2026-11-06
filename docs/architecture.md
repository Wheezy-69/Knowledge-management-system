# Architecture

## Boundaries

- `routes/web.php`: named web routes and scoped nested resource bindings.
- `app/Http/Controllers/{Vault,Section,Problem,Attempt}Controller.php`: HTTP coordination.
- `app/Http/Requests/*Request.php`: shared create/update validation.
- `app/Models/{Vault,Section,Problem,Attempt}.php`: data and relationships.
- `database/migrations/*create_knowledge_vault_tables.php`: additive schema.
- `resources/js/pages`: Inertia pages for vaults, sections, and problems.
- `resources/js/components`: application forms and composition.
- `resources/js/components/ui`: project-owned shadcn-vue primitives.
- `resources/js/types/knowledge.ts`: frontend contracts for records and pagination.

There is no separate JSON API, frontend state store, repository layer, or service
container abstraction. The database is the source of truth. Inertia supplies each
page with server-side props. Records are paginated; counts and global-search
relationships are loaded with Eloquent to avoid per-record queries.

Problem lists select summary fields rather than loading corrected solutions and
explanations. The frontend uses `ProblemSummary` for these responses and `Problem`
for detail pages. Global results load only the IDs/names needed for ancestry.
Primary navigation links prefetch on hover using Inertia's existing cache.
The app bootstrap clears that cache after mutations finish to keep lists fresh.
See [performance notes](performance.md) for measurements and limitations.

## Example: saving an attempt

1. The problem page opens `AttemptDialog.vue`, which initializes `useForm`.
2. The submit handler calls the generated Wayfinder `store` or `update` URL.
3. Inertia sends a request through Laravel's web middleware, including CSRF handling.
4. Scoped route binding resolves the vault, then its section, that section's
   problem, and (for updates) that problem's attempt. A mismatch returns 404
   before the controller runs.
5. `AttemptRequest` validates code, notes, and position. Validation errors redirect
   back and Inertia places the field messages in `form.errors`.
6. `AttemptController` uses the resolved parent's relationship to create the row,
   or updates the bound attempt using validated fields only.
7. The response redirects back, with a session success message. Inertia reloads
   page props; the UI displays the new ordered attempts and a status message.

The same pattern drives the other resources. Successful creations navigate to the
new vault/section/problem. Successful edits and attempt operations return back.
Deleting a vault, section, or problem navigates to its surviving parent.

## Routing

`/vaults` lists vaults. Nested paths are:

- `/vaults/{vault}`
- `/vaults/{vault}/sections/{section}`
- `/vaults/{vault}/sections/{section}/problems/{problem}`
- `/vaults/{vault}/sections/{section}/problems/{problem}/attempts/{attempt}`

POST creates at the corresponding collection path. PUT updates; DELETE removes.
The attempts have no standalone GET page: the problem page reads them.
`GET /problems?search=...` is global search, and `GET /vaults/{vault}/cover`
streams a validated uploaded image. The starter `/` and health `/up` remain.

Wayfinder generates ignored TypeScript route files during builds. Regenerate with
`php artisan wayfinder:generate --with-form` when routes change before type checking.

## State, files, and safety

`AppLayout.vue` composes sidebar, breadcrumbs, theme control, and flash messages.
`HandleInertiaRequests` shares the success message. Each dialog owns its form state;
it clears stale errors and resets from current props when reopened.
Submit and cancel actions are disabled while saving. Uploads show progress.
Delete dialogs explain descendant deletion and require an explicit action.

The existing `cn` helper merges Tailwind classes. Theme variables and syntax
highlight colors live in `resources/css/app.css`. `ThemeToggle.vue` uses VueUse,
already present, to persist the selection under `knowledge-vault-theme`.

Vue escapes all text. Only `CodeBlock.vue` uses `v-html`, with output produced by
highlight.js's escaping highlighter, never raw stored source. Code is not evaluated.
`bootstrap/app.php` exempts code and corrected solutions from whitespace trimming.

Uploads accept raster images up to 2 MB, get generated filenames, and live on the
private disk. The cover path is hidden from serialization; the model appends a
cover URL. Replacement/removal cleans up the previous file after saving the row.
A failed storage write becomes a field validation error. Database and filesystem
writes are not one atomic transaction; an unexpected database failure after an
upload can leave an orphaned file.

There is no authentication in this starter. Validation requests deliberately allow
the local workspace. Scoped binding and guarded fillable fields protect hierarchy,
but do not provide multi-user isolation. See the overview before hosting.
