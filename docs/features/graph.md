# Knowledge graph

## 1. What the feature does

Visualize existing problem/tag assignments and related-problem links. Navigate to problems or filtered tag results; include isolated problems.

## 2. Implementing files

GraphController.php; Graph.vue; SearchFiltersForm.vue; existing tag and link tables.
PHP classes live under `app/`; Vue and TypeScript paths live under `resources/js/`.
Routes are declared in `routes/web.php`.

## 3. Frontend communication

The Graph navigation item opens an Inertia page. Graph filters use GET. Problem nodes open detail pages; tag nodes open All problems with that tag selected. A text-link list supplements SVG navigation.

## 4. Controller, model, and database

The controller derives nodes and edges from database relationships without a graph table. Only edges whose endpoints are displayed are included. The graph uses deterministic positions, not a continuously running simulation.

## 5. Validation and errors

Filters are validated by ProblemSearchRequest. Graph search matches problem titles; other filters combine. Nested page links retain correct ancestry. The app has no per-user access rules to apply until authentication is introduced.

## 6. Testing

KnowledgeExpansionTest checks node/edge counts, filtering, isolated entries, and the declared problem cap. Normal CRUD tests cover linked deletions and invalid hierarchy access.

## 7. Implementation decisions

The view is a bounded slice: at most 30 problems and 50 tags, with a visible limit message. Narrow filters to explore another slice. It does not claim to show the entire workspace when limited. No new visualization dependency was added.

## 8. Rebuild independently

Start from real relationships. Bound the query, build explicit node IDs, discard out-of-slice edges, then render static SVG with accessible links and clear truncation feedback.
