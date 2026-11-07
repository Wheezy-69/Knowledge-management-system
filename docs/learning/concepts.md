# Concepts demonstrated by Knowledge Vault

## MVC and a request lifecycle

A Vue form sends a named-route request. Laravel binds models, validates input,
runs a controller, persists through Eloquent, and returns an Inertia response or
redirect. Follow `AttemptDialog.vue → AttemptRequest → AttemptController →
Attempt::problem()` to trace a small complete vertical feature.

## Relationships and referential integrity

Foreign keys describe ownership in the hierarchy. Eloquent relationships make
queries readable, while database constraints keep orphan rows out even if PHP
code has a defect. `cascadeOnDelete` explains why a parent's deletion removes its
descendants. Study `SectionCrudTest.php` for the intended boundary.

## Validation, mass assignment, and authorization

These solve different problems. Validation checks acceptable values. Fillable
fields decide which attributes may be assigned from arrays. Scoped binding
requires a child to belong to its URL parent. Authentication and policies would
decide which person may access the record; that layer is intentionally absent in
this local-only workspace. Adding a users table alone does not add authentication.

## Database constraints and concurrency

The unique attempt-order index is the final guard against duplicate positions.
Request validation provides friendly feedback before saving. A database
constraint is still needed because requests could race after validation.
This MVP is optimized for one local user; a rare simultaneous-write conflict may
need a retry. SQLite is appropriate here without introducing a database server.

## Inertia and TypeScript

See [TypeScript in Knowledge Vault](typescript.md) for beginner-friendly examples,
JavaScript equivalents, and explanations of the types used in these components.

Inertia keeps Laravel routing and controllers while rendering Vue pages.
`useForm` tracks errors, progress, and processing without a separate client
store. Wayfinder generates route functions from Laravel routes.
`types/knowledge.ts` describes the page data; static checking catches many
mismatches before running the app, but does not replace server validation.

## Composition and small components

Collection dialogs share name/description behavior; problem and attempt dialogs
remain separate because their data and responsibilities differ.
CollectionCard, FormField, PaginationLinks, ProblemList, and CodeBlock each solve
one repeated UI problem. shadcn-vue provides accessible interaction primitives
that stay in the repository and can be studied.

## Encoding, not code execution

Saving code is different from executing it. highlight.js converts source into
escaped HTML with highlighting spans. Unknown languages fall back to escaped
plaintext. The security test in `tests/frontend/highlight.test.mjs` checks that
stored HTML-looking source cannot become real script/image elements.
Plain descriptions use Vue interpolation, which escapes HTML automatically.

## Search and performance

Search queries bind their values and group title/description OR conditions inside
the section relationship query. Without the grouping, an OR can accidentally
include another section's results. Pagination caps payload sizes; eager loading
`section.vault` and loading counts avoids repeated queries per result.
LIKE substring matching is intentionally simpler than a search service.

## Files are outside database transactions

Upload validation checks content and size. Storage generates safe filenames.
The private image route avoids executing uploaded source from a public directory.
A database transaction cannot roll back a filesystem write. Study cover
replacement tests and consider cleanup jobs only if usage later justifies them.

## A practical rebuilding exercise

1. Draw the four-table hierarchy and manually define its foreign keys.
2. Build a vault create request, controller action, route, and one passing test.
3. Write an invalid-input test, then implement its validation.
4. Add an Inertia listing page and the smallest working form.
5. Repeat with sections; intentionally pass a section under a wrong vault and
   confirm 404.
6. Add problems, grouped search conditions, and a corrected solution field.
7. Add attempts, uniqueness rules, and ordering.
8. Extract repeated UI only after noticing real repetition.
9. Add uploads, code highlighting, and the corresponding failure tests.
10. Run the checks and update the feature docs from the behavior you observe.

Follow the individual feature documents for exact source files and smaller steps.

## Additional concepts from the expansion

- Many-to-many relationships: normalized tags and problem_tag preserve reusable
  identities while JSON labels remain a compatibility copy.
- Canonical pairs: related links store the smaller ID first to prevent a reversed
  duplicate of an undirected relationship.
- Database transactions plus file cleanup: a portable import rolls back rows and
  removes only its newly generated paths if an attachment write fails.
- Portable formats: checksums detect corruption, while schema/reference validation
  handles malformed data. A checksum is not an authenticity signature.
- Non-destructive evolution: source-marked blocks copy original content and leave
  old fields intact. Numeric ordering and unique indexes protect block order.
- Bounded algorithms: line comparison caps matrix size; graphs cap displayed nodes.
- Derived views: graph nodes/edges come from existing relationships, not a parallel
  graph database. Text interpolation avoids turning diff/source strings into HTML.
