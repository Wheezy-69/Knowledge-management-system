# Database

The additive migration
`database/migrations/2026_11_05_111443_create_knowledge_vault_tables.php`
creates four tables. Existing users, sessions, cache, and jobs tables are unchanged.

| Table    | Fields beyond id and created_at/updated_at                                                                                                                                                         |
| -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| vaults   | name (string 120), description (nullable text), cover_path (nullable string)                                                                                                                       |
| sections | vault_id (foreign key), name (string 120), description (nullable text)                                                                                                                             |
| problems | section_id (foreign key), title (string 200), description (nullable text), programming_language (string 40), tags (nullable JSON), corrected_solution (nullable text), explanation (nullable text) |
| attempts | problem_id (foreign key), code (text), notes (nullable text), position (unsigned integer)                                                                                                          |

## Relationships

```text
Vault hasMany Sections
Section belongsTo Vault, hasMany Problems
Problem belongsTo Section, hasMany Attempts
Attempt belongsTo Problem
```

Foreign keys cascade on deletion. Removing a vault deletes its entire subtree;
removing a section keeps its vault; removing a problem keeps its section.
The application deletes the vault cover file separately from the database cascade.

Indexes support section listing by vault/name and problem listing by
section/updated_at. A unique composite index on `attempts(problem_id, position)`
prevents duplicate order numbers within one problem. Another problem may use the
same number. Position gaps are retained after deletion or reordering. To swap two
positions, move one to a temporarily unused positive number first.

SQLite's foreign-key enforcement is enabled by the existing database config.
Only the problem tags need an Eloquent array cast. Tags are stored inline as JSON
because there is no tag-management or tag-filter feature in this MVP.

## Validation and persistence

Controllers pass validated allowed fields to models. Parent IDs are not fillable
or accepted by form requests: relationships set them on creation, and a request
cannot move a record to another parent. Factories use related factories to produce
independent fixture trees for feature tests.

Application validation limits descriptions (vault/section: 10,000; problem: 50,000),
code and corrected solution (100,000), explanation (50,000), attempt notes (10,000),
tags (12 strings of at most 40 characters), and position (1–1,000,000).
These are request limits; text columns deliberately do not truncate content.

## Applying and protecting data

Run `php artisan migrate` to add tables without resetting existing tables.
Tests use `RefreshDatabase` against `:memory:`, not the development database.
The migration's `down()` drops only these four tables, child-first; rolling it back
is destructive and loses notebook entries. Prefer forward migrations for future
schema changes. Do not use `migrate:fresh` on real notebook data.

Back up the SQLite file and private cover directory together. Factory classes are
for testing; the MVP does not seed sample data into the local notebook.
