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
Empty. Sheet, Input, Separator, Skeleton, and Tooltip are registry dependencies
of Sidebar. No CRUD controls or populated vault cards are implemented yet.

Add components only when needed, following the
[official documentation](https://www.shadcn-vue.com/docs/installation/laravel):

```sh
npx shadcn-vue@latest add <component>
```

Review generated changes and dependencies, retain the existing `cn` helper, and
run `npm run types:check`, `npm run check`, and `npm run build`.
