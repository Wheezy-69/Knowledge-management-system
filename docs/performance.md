# Performance investigation

Measurements below were taken on the local Windows/Herd development environment
on 2026-11-06, with Vite running and APP_DEBUG enabled. They are observations,
not production benchmarks or guarantees for another machine.

## Findings

- The first observed vault HTML response took about 4.6 seconds. Subsequent warm
  HTML responses were about 110–300 ms. This establishes a cold-start difference,
  but does not isolate which runtime stage caused the first delay.
- Valid warm Inertia list/search responses took about 90–160 ms. Vite's entry
  module took about 4 ms; sampled page/form modules took about 1–16 ms. These
  measurements did not reproduce a multi-second warm navigation delay.
- Both problem-list queries selected full corrected solutions and explanations
  even though the lists did not display them. At the request validation limits,
  those two fields alone could add 150,000 characters per listed problem.
- The existing browser log contained SSR hydration mismatch warnings for the
  theme label and sidebar. Browser preferences are unavailable to the server,
  so using them before hydration can require Vue to repair the initial markup.
- The existing local Node 23 runtime is outside Vite Plus's declared support
  range. Build times varied during the investigation; that alone does not prove
  the unsupported runtime caused the perceived delay.

## Changes

ProblemController and SectionController select summary fields for list queries.
Global results restrict eagerly loaded ancestry to IDs/names. Full corrected
solutions, explanations, and attempts still load on problem detail pages.
`ProblemSummary` documents the smaller frontend contract; `Problem` extends it.

On the same local problem-list dataset, the Inertia response decreased from
**5,265 to 2,174 bytes (about 59% smaller)**. Warm response time remained around
120–170 ms after the change. The size improvement is measured; a corresponding
server-latency improvement was not demonstrated by these small local samples.

Sidebar, collection-card, breadcrumb, and problem-result links now use Inertia
hover prefetching. This can fetch a likely destination before a click; it does
not accelerate writes, guarantee a cache hit, or make the server itself faster.
The app clears prefetched pages when a mutation finishes, so returning to a
previously hovered list does not reuse its pre-save contents.

ThemeToggle renders the same initial label on server and client before restoring
the selected browser theme. SidebarProvider uses an SSR media-query width and
restores its cookie only after mounting. These changes remove known sources of
initial-state disagreement. Real-browser confirmation is still needed to verify
that all hydration warnings are gone and assess perceived interaction latency.

## Verification and further diagnosis

Problem feature tests cover both global and section list payloads excluding large
detail fields, while the detail response retains them. Run:

```sh
php artisan test --filter=ProblemCrud
npm run types:check
npm run check
npm run build
```

For remaining delays, inspect a slow Save/Search/navigation in the browser's
Network panel. Separate request time from JavaScript download/evaluation and
rendering. A save includes a mutation and a redirect/read, so its total is not
comparable to one list GET. No synthetic saves were sent to the real notebook
as part of this investigation.

When measuring Inertia requests, send the current page's asset version and check
the status: a 409 asset-version response triggers a reload and is not a successful
navigation-payload measurement. Invalidate old browser assets after a build.

Compare cold and repeated warm requests separately. To compare against built
assets, stop Vite intentionally, ensure its hot-file lifecycle is correct, and
use the production build. Do not confuse development compilation timings with
production performance. Debug settings and running services were left intact.
No real notebook records or database schema were changed.
