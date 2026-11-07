# Expanded search and filters

## 1. What the feature does

Find problems across titles, descriptions, explanations, corrected solutions, attempt code/notes, and text/code blocks. Combine the term with vault, section, language, and all selected tags.

## 2. Implementing files

ProblemSearchRequest.php; Services/ProblemSearch.php; ProblemController::index; SectionController::show; SearchFiltersForm.vue; ProblemList.vue; types/knowledge.ts.
PHP classes live under `app/`; Vue and TypeScript paths live under `resources/js/`.
Routes are declared in `routes/web.php`.

## 3. Frontend communication

The shared filter form issues a named-route Inertia GET. Filters are normalized on the server and retained in paginator URLs. Clear resets every filter. The section page always applies its bound section, regardless of submitted filters.

## 4. Controller, model, and database

ProblemSearch groups all OR conditions inside the selected problem query. Attempt/block matches use relationship EXISTS subqueries, so they do not duplicate rows. Tag filters use one relationship constraint per selected tag (AND semantics). Only matching-page rows get bounded, escaped-text previews; full content is hidden from list serialization.

## 5. Validation and errors

Terms are strings up to 200 characters. Parent/tag filters must reference existing records; languages have a 40-character limit and at most 12 distinct tag IDs are accepted. Wrong URL parents return 404. This is a local workspace, not per-user authorization.

## 6. Testing

KnowledgeExpansionTest covers all content fields, combined filters, pagination, empty results, and mismatched parents. ProblemCrudTest retains SQL-like-input and small-list-payload regression coverage.

## 7. Implementation decisions

No search service or full-text index was added. SQL LIKE values are bound; % and _ retain wildcard meaning. Preview text is at most 242 characters including ellipses. LIKE case folding follows the database, so not every Unicode case pair is guaranteed equivalent.

## 8. Rebuild independently

Build grouped search first; test a cross-section OR case. Add validated filters one at a time. Normalize IDs before rendering checkboxes. Add previews without serializing large fields, then test filter persistence across pagination.
