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
- [UI components and theming](ui.md)
- [Performance findings and measurement](performance.md)

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
Use **All problems** for search across the workspace; section search stays
inside the selected section. Themes cycle through System, Light, and Dark.

Covers live on Laravel's private local disk and are served by a vault route,
so no `storage:link` is needed. Back up both the SQLite database and
`storage/app/private/vault-covers`. Never run `migrate:fresh` against your notebook.

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
It now also runs the highlighting tests. PHPStan gets a 512 MB process budget
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
There is no full-text index, tag filtering, drag-and-drop sorting, or rich editor.
Problem lists paginate at 15 items; vaults and sections paginate at 12.
Attempts load together on the problem page, appropriate for a personal MVP.

The local Node 23 installation is outside the toolchain's declared support range.
The starter font plugin may report an optional `fontaine` optimization notice;
neither requires another UI library.
