# Problems and search

## 1. What the feature does

Capture a title, description, programming language, tags, corrected solution, and detailed explanation. Browse/search within a section or across the whole workspace, and open a problem to see its attempts.

## 2. Implementing files

`ProblemController.php`, `ProblemRequest.php`, `Models/Problem.php`, `ProblemFactory.php`, `pages/problems/{Index,Show}.vue`, `pages/sections/Show.vue`, `ProblemDialog.vue`, `ProblemList.vue`, `CodeBlock.vue`, and `lib/highlight.ts`.
Backend paths are under `app/Http/Controllers`, `app/Http/Requests`,
`app/Models`, and `database/factories`; Vue paths are under `resources/js`.
Routes are in `routes/web.php`; all four tables are in the knowledge-vault migration.

## 3. Frontend communication

`ProblemDialog` submits via `@/routes/vaults/sections/problems`. Tags are entered as comma-separated text and sent as a trimmed array. SearchFiltersForm uses an explicit Search button and an Inertia GET with the
term and combined filters, preserving page state. Clear resets all filters. Generated server pagination links retain the query string. The global list links each result to its nested detail URL.

## 4. Controller, model, and database

List queries select only summary fields and omit corrected_solution/explanation;
those potentially large fields remain available on the detail page and in its
edit dialog. Global results load only section/vault IDs and names for navigation.
The frontend distinguishes `ProblemSummary` from `Problem` accordingly.
Problem links prefetch on hover so a likely next page can load before the click.

Problems are created with `$section->problems()` and updated with validated fields. Tags cast between a PHP array and a JSON database column. Section search starts from its bound section and groups OR conditions across
titles, descriptions, solutions, explanations, attempts/notes, and blocks. Global search eager-loads section.vault and attempt counts. ProblemSearch validates
and applies vault, section, language, and all-selected-tag filters. Small previews
identify matching fields without sending full stored code. Both lists sort by updated_at and paginate at 15 results. The detail action loads attempts in position order. Deleting a problem cascades only its attempts.

## 5. Validation and errors

Title is required (200), language required (40), description/explanation optional (50,000 each), corrected solution optional (100,000). Tags are optional, limited to 12 distinct case-insensitive strings of at most 40 characters. Tags have inline error feedback including indexed errors. Search must be a string up to 200 characters. Nested binding rejects wrong section IDs. Code fields bypass trimming, preserving indentation.

## 6. Testing

Regression tests cover both list endpoints omitting large solution/explanation
fields while the detail page retains them.

`tests/Feature/ProblemCrudTest.php` covers persisted fields, code whitespace, updates, tag and length validation, case-insensitive title/description search, section isolation, global ancestry data, SQL-like input, wrong parents, immutable section_id, and cascade deletion. `tests/frontend/highlight.test.mjs` checks code escaping and language fallback. Run `php artisan test --filter=ProblemCrud` and the frontend test command in the overview.

## 7. Implementation decisions

Descriptions and explanation are escaped plain text. Corrected solution has a separate visual reference panel and database field; attempts never overwrite it. Tags keep their JSON compatibility copy alongside a normalized reusable catalog.
See [tags](tags.md) and [expanded search](search.md). Search uses bound LIKE values, so percent/underscore retain wildcard meaning. Unknown languages render as plaintext. Combined tag filters and a simple text/code block editor are available.
Descriptions/explanations still do not render Markdown or raw HTML.

## 8. Rebuild it independently

1. Create the table and the section relationship.
2. Implement a validated create/edit request and test every stored field.
3. Build the detail page and keep explanation distinct from code.
4. Add tags as a JSON array and normalize comma-separated text at the UI boundary.
5. Add grouped title/description queries within a section, then global search with eager loading.
6. Add syntax highlighting from an escaping library and test HTML-looking inputs.
