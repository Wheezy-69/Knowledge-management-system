# Knowledge Vault UI

The application uses shadcn-vue's New York style with Tailwind CSS 4. Components
are source files owned by this project, rather than a global Vue plugin.

- `components.json` configures TypeScript, the stone base palette, CSS variables,
  and aliases under `resources/js`.
- `resources/css/app.css` defines semantic colors and radii. The emerald primary
  color and stone surfaces preserve the existing palette. System dark mode works
  automatically; an explicit `.dark` root also works. A `.light` root opts out of
  system dark mode.
- `resources/js/components/ui` contains the official generated components. Their
  `cn` imports use the existing `@/lib/utils` helper. Keep application-specific
  composition in `AppSidebar`, `AppLayout`, and `EmptyState`.
- Inertia links remain `Link` components with Wayfinder route helpers. Use
  `as-child` on shadcn buttons or sidebar menu buttons to compose them with links.

Installed component families are Sidebar, Button, Card, Badge, Breadcrumb, and
Empty, Dialog, Label, and Textarea. Sheet, Input, Separator, Skeleton, and Tooltip
support the sidebar and forms. CollectionDialog, ProblemDialog, AttemptDialog,
and DeleteDialog implement CRUD interactions. CodeBlock uses highlight.js core
with explicitly registered grammars; theme-compatible colors live in app.css.

ThemeToggle cycles System, Light, and Dark using the existing VueUse dependency.
Its label stays System until mounted, then reflects the browser preference, so
the initial client markup matches SSR. SidebarProvider similarly restores its
cookie after mounting and uses a shared SSR media-query width during hydration.
Breadcrumbs follow the hierarchy, and ProblemList/PaginationLinks share search
and navigation behavior. Breadcrumbs wrap below the header controls on mobile.
See [the documentation index](README.md) for feature details.

Add components only when needed, following the
[official documentation](https://www.shadcn-vue.com/docs/installation/laravel):

```sh
npx shadcn-vue@latest add <component>
```

Review generated changes and dependencies, retain the existing `cn` helper, and
run `npm run types:check`, `npm run check`, and `npm run build`.

## Expansion UI

SearchFiltersForm centralizes term, parent, language, and all-tag filters.
TagDialog manages reusable names. ContentBlockDialog adds ordered text/code
blocks without changing the legacy editor. RelatedProblems uses scoped links and
explicit unlink confirmation. The Compare page renders safe side-by-side text;
CodeBlock adds visual wrapping and selects source for manual copying when the
Clipboard API is unavailable. Graph has deterministic SVG nodes and a text-link
alternative. Backup downloads use a normal anchor; restore uses a confirmed
Inertia file form. Native selects/checkboxes complement existing shadcn primitives
without adding a second UI library. AI generation has no inactive/fake button.
