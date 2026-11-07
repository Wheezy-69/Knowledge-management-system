# Knowledge Vault

Knowledge Vault is a personal coding notebook built with Laravel 13, Vue 3.5,
Inertia 3, TypeScript, Tailwind CSS 4, shadcn-vue, and SQLite.
The lockfiles record exact installed versions.

The implemented hierarchy is **Vault → Section → Problem → Attempts**.
A problem also has a corrected solution, an explanation, a language, and tags.

## Documentation

- [Architecture and request lifecycle](architecture.md)
- [Database schema and migrations](database.md)
- [Vaults](features/vaults.md)
- [Sections](features/sections.md)
- [Problems and search](features/problems.md)
- [Code attempts](features/attempts.md)
- [Programming concepts and rebuilding](learning/concepts.md)
- [TypeScript concepts with project examples](learning/typescript.md)
- [Expanded search and filters](features/search.md)
- [Reusable tags](features/tags.md)
- [Snippet presentation and copying](features/snippets.md)
- [Related problems](features/related.md)
- [Backup format and restore](features/backups.md)
- [Code comparison](features/comparison.md)
- [Text and code blocks](features/blocks.md)
- [Knowledge graph](features/graph.md)
- [AI provider boundary (deferred)](features/ai-explanations.md)
- [UI components and theming](ui.md)
- [Performance findings and measurement](performance.md)
- [Roadmap completion and verification](checkpoints/2026-11-07-verification.md)

## Run locally

For this existing checkout, start Herd and run:

```sh
php artisan migrate
npm run dev
```

Open your configured `APP_URL` followed by `/vaults`.
The starter welcome page at `/` still links to the workspace.
For a fresh checkout, install Composer/npm dependencies, copy `.env.example`
to `.env`, generate the application key, create `database/database.sqlite`,
and run migrations. Configure `DB_CONNECTION=sqlite`.
Use a Node version supported by Vite Plus (for example Node 22.22.2+ or 24.15+).
If PHP/Node are not on PATH, use the executable directories supplied by Herd.
`composer dev` also starts the starter kit's development processes.

Create a vault, open it, create a section, then create a problem. Open the
problem to add attempts or edit its corrected solution and explanation.
Use **All problems** for search across titles, descriptions, explanations, solutions,
attempts, notes, and blocks. Combine search with vault/section/language/tag filters.
Section search always stays within its section. The Tags page manages reusable
names. Problem detail supports related links, code comparison, and ordered blocks.
Legacy content can be copied into blocks without modifying original fields.
The graph derives relationships from the same records and supports navigation.
Themes cycle through System, Light, and Dark.

Covers live on Laravel's private local disk and are served by a vault route,
so no `storage:link` is needed. The **Backup and restore** page downloads a validated
portable JSON archive with embedded images; restoring adds copies, never replaces
records. `php artisan vault:backup` writes and verifies a private local archive.
Also back up the SQLite database and `storage/app/private/vault-covers` for recovery. Never run `migrate:fresh` against your notebook.

## Verification

```sh
php artisan test
php vendor/bin/pint --test
php vendor/bin/phpstan analyse --memory-limit=512M
npm run types:check
npm run check
npm run build
npm run test:code
```

PHP feature tests use the isolated in-memory SQLite database configured in
`phpunit.xml`; upload tests use a fake disk. They do not delete local notebook data.
`composer ci:check` is the existing aggregate check used by GitHub Actions.
It also runs the highlighting, clipboard, and comparison tests. PHPStan gets a 512 MB process budget
through the Composer script, without changing the global PHP configuration.

## Scope and limitations

This starter has no sign-in or registration flow. The MVP intentionally remains
a **single-user local workspace**, with no per-user ownership. Everyone who can
reach its URL has access to its content and mutations. Nested scoped route
bindings prevent accessing a child through the wrong parent; this is hierarchy
integrity, not authentication. Add authentication, ownership columns, and policies
before hosting a private or multi-user service.

Descriptions, notes, and explanations are plain text, not rendered Markdown or
HTML. Code is displayed, never executed. Unrecognized languages render as escaped
plain text. Attempts are ordered by distinct positive numbers; gaps are allowed.
Deletion is permanent after confirmation and cascades to descendants.
Search uses SQL LIKE with bound parameters; `%` and `_` have LIKE wildcard meaning.
Search is SQL LIKE, not full-text indexing. Blocks use separate text/code dialogs
and numeric ordering rather than a drag-and-drop document canvas. Legacy fields
remain available. Code comparisons have explicit work limits, graphs show a
bounded slice, and portable backups have a 20 MB limit; see their feature guides.
AI generation is not configured; no content is sent to a provider.
Problem lists paginate at 15 items; vaults and sections paginate at 12.
Attempts load together on the problem page, appropriate for a personal MVP.

The local Node 23 installation is outside the toolchain's declared support range.
The starter font plugin may report an optional `fontaine` optimization notice;
neither requires another UI library.
