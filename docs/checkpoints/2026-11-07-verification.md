# Roadmap verification — 2026-11-07

Continued from local checkpoint `2292c6b` on `feat/backup-search-filters`.
The historical [checkpoint note](2026-11-06-roadmap.md) records the earlier state.

## Completed scope

The first eight roadmap items are implemented: content search with previews and
pagination, reusable tags and combined filters, snippet highlighting/copy/wrapping,
manual related-problem links, portable backup and append-only restore, side-by-side
comparison, ordered text/code blocks, and a derived knowledge graph.
The [documentation index](../README.md) links the feature guides, schema,
architecture, and TypeScript learning material.

The finishing changes reject archives whose JSON tag labels disagree with their
normalized assignments, reset the file input after a successful restore, display
backup errors, and complete the feature and learning documentation. The new
integrity regression test also checks that failed restoration preserves records.

## Verified locally

- `composer ci:check`: passed, including formatting/lint, Vue TypeScript,
  8 frontend tests, PHP formatting, PHPStan, and 78 PHP tests / 616 assertions.
- `npm run build`: passed. The font plugin reported its optional fontaine notice;
  no dependency was added for this optimization.
- `php artisan migrate:status`: all five migrations ran, including the additive
  tags, links, and content-block migration. No migration was rerun or rolled back.
- Relative Markdown links resolve. Feature guides describe implemented behavior.
- Tests use the in-memory SQLite database and fake attachment storage; no real
  notebook data was restored, replaced, or deleted during verification.

## Remaining limits

- Real-browser visual and interaction verification remains outstanding.
- AI generation and provider setup remain separate; no content is sent to AI.
- This is a local single-user app without authentication or per-user ownership.
  Nested binding enforces parent relationships, not user isolation.
- Portable backups are unencrypted and limited to 20 MB. Restore adds copies;
  database and private-cover backups remain useful for disaster recovery.
- Comparison and graph work are explicitly bounded. Blocks use numeric ordering
  and dialogs rather than drag-and-drop editing. Legacy content is retained.
- The installed Node 23 runtime is outside the toolchain's declared support range;
  use a supported Node version as described in the setup guide.

The checkpoint commit and tag remain intact. Verification did not push or deploy
changes. The completed work is organized separately on
`feat/knowledge-vault-roadmap`; see the [commit guide](2026-11-07-feature-history.md).
