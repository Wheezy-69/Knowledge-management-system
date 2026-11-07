# Related problems

## 1. What the feature does

Manually link problems and navigate in either direction. Unlinking preserves both problems.

## 2. Implementing files

RelatedProblemController.php; problem_links migration; ProblemController::show; RelatedProblems.vue; problems/Show.vue.
PHP classes live under `app/`; Vue and TypeScript paths live under `resources/js/`.
Routes are declared in `routes/web.php`.

## 3. Frontend communication

The detail page posts a selected problem ID to its nested related route. Related entries use their own complete vault/section/problem URL. Unlink uses a confirmation dialog and DELETE.

## 4. Controller, model, and database

Each undirected link stores the smaller problem ID first. The composite primary key prevents duplicates in either direction. Foreign keys remove links when either problem is deleted. The detail query checks both endpoints.

## 5. Validation and errors

The target must exist, cannot be the same problem, and cannot already be linked. The source problem is scoped to its URL parents before any mutation. All records share the same intentionally unauthenticated local workspace.

## 6. Testing

KnowledgeExpansionTest covers reciprocal navigation, reversed duplicate requests, self-links, unlinking, and linked-record deletion. Nested CRUD tests cover parent scoping.

## 7. Implementation decisions

The selector shows the newest 500 other problems. This is a personal-workspace limit, not a permission boundary. Links do not imply that either problem belongs in the other section. The down migration drops only the new relationship table, so rollback would lose links but not problems.

## 8. Rebuild independently

Create a canonical undirected pivot and cascade constraints. Test both request directions before building the selector. Always generate each target URL from the target ancestry.
