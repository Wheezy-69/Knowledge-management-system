# Code comparison

## 1. What the feature does

Compare a corrected solution, attempt, or code block against another snippet from the same problem. No saved comparisons or source changes.

## 2. Implementing files

ComparisonController.php; problems/Compare.vue; lib/diff.ts; tests/frontend/diff.test.mjs.
PHP classes live under `app/`; Vue and TypeScript paths live under `resources/js/`.
Routes are declared in `routes/web.php`.

## 3. Frontend communication

Compare code on problem detail opens a GET page. Two selectors submit left/right IDs through the generated compare route. Back to problem returns to the exact parent hierarchy.

## 4. Controller, model, and database

The controller builds allowed choices only from the bound problem. Unknown or foreign snippet IDs return 404. The browser computes a line-based longest-common-subsequence diff; the database only reads snippets.

## 5. Validation and errors

No posted code is accepted. Comparison preserves spaces, tabs, CRLF/LF differences, Unicode, and final newlines. Vue interpolation renders diff content as text rather than HTML.

## 6. Testing

KnowledgeExpansionTest covers snippet isolation. diff.test.mjs reconstructs both originals from diff rows, tests empty/Unicode/whitespace cases, additions/removals, and large-input fallback.

## 7. Implementation decisions

The work is bounded: over 200,000 combined characters, over 4,000 combined lines, or a line-product over 250,000 skips diffing and shows originals. Diff rows remain plain text; large-fallback blocks use syntax highlighting. No code is reformatted.

## 8. Rebuild independently

Implement a pure comparison function and reconstruct both inputs in tests. Add size limits before UI rendering. Scope choices on the server, then add the side-by-side page.
