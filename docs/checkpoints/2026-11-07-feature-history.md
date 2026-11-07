# Roadmap feature commits

Branch: `feat/knowledge-vault-roadmap`, based on `c118cd0` (the merged performance
improvements). The original `feat/backup-search-filters` checkpoint and its tags
are preserved. The new branch separates the combined checkpoint into these commits:

| Commit subject                                         | Scope                                                                                                                                                  |
| ------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Add shared schema and domain support for the roadmap   | Additive migration, tag/block models, relationships, shared types and search/tag services. Original content remains intact.                            |
| Add global content search and combined filters         | Global and section search, previews, pagination, validated filters and search guide.                                                                   |
| Add reusable tag management                            | Tag catalog CRUD, normalized names, assignments, routes, dialogs and tag guide.                                                                        |
| Improve snippet copying and readable formatting        | Exact-source clipboard helper, manual fallback, wrapping, frontend tests and snippet guide. Existing language selection and highlighting are retained. |
| Add manually related problems                          | Reciprocal links, duplicate/self-link protection, unlink confirmation, component, routes and guide.                                                    |
| Add portable backup and append-only restore            | Archive validation, attachments, web/CLI entry points, restore UI, routes and backup format guide.                                                     |
| Add bounded side-by-side code comparison               | Scoped snippet choices, whitespace-sensitive comparison, limits, frontend tests, route and guide.                                                      |
| Add ordered text and code blocks                       | Block CRUD, ordering, non-destructive legacy copies, routes, dialog and guide.                                                                         |
| Add a relationship-derived knowledge graph             | Bounded graph, filters, navigation, accessible link list, route and guide.                                                                             |
| Integrate roadmap navigation and document verification | Sidebar and problem-detail integration, cross-feature backend tests, architecture/schema/learning guides and verification report.                      |

Shared foundations precede the features that use them. Sidebar and problem-detail
entry points land after all their target features. Cross-feature tests remain
together to exercise the complete workflow. The complete branch is the verified
delivery; the intermediate commits were not each run through the full suite.

AI provider integration remains deferred. There is no provider configuration,
external content sharing, or fabricated generation feature in these commits.

Use `git log --reverse --oneline c118cd0..HEAD` to follow the sequence and
`git show <commit>` to study one change. See the
[verification report](2026-11-07-verification.md) for checks and limitations.
