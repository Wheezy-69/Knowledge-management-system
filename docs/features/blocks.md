# Text and code blocks

## 1. What the feature does

Add/edit/delete ordered text or code blocks on a problem. Copy legacy description, solution, and explanation into blocks without deleting or changing those fields.

## 2. Implementing files

Models/ContentBlock.php; ContentBlockRequest.php; ContentBlockController.php; ContentBlockDialog.vue; problems/Show.vue; content_blocks migration.
PHP classes live under `app/`; Vue and TypeScript paths live under `resources/js/`.
Routes are declared in `routes/web.php`.

## 3. Frontend communication

Dialogs submit nested Inertia mutations. A separate Copy legacy fields action posts to the conversion route. Refreshed detail props include ordered blocks. The existing attempt editor and corrected-solution panel remain.

## 4. Controller, model, and database

The table stores type, content, optional language, position, and an optional legacy source marker. problem_id is set through the parent relationship. Unique indexes protect position and conversion source per problem. Conversion skips sources already copied and runs in a transaction.

## 5. Validation and errors

Type is text or code. Content is required up to 100,000 characters; language is optional up to 40; position is unique and between 1 and 1,000,000. Raw content is excluded from whitespace trimming. Wrong parent/block combinations return 404. Exhausted order numbers abort conversion without partial copies.

## 6. Testing

KnowledgeExpansionTest covers CRUD, code whitespace, ordering/reload, repeated conversion, originals remaining intact, invalid types, duplicate positions, wrong parents, and conversion capacity.

## 7. Implementation decisions

This is a simple block editor using separate dialogs, not a drag-and-drop document canvas. Original fields remain the compatibility source; edits are not bidirectionally synchronized. Converted blocks can be deleted individually to return to the legacy-only view. Deleting a source-marked block permits an explicit new copy later.

## 8. Rebuild independently

Add a new table rather than replacing legacy columns. Implement block CRUD and numeric ordering. Add source markers to make conversion repeatable. Verify originals before and after conversion/deletion.
