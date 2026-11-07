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
Problem tags retain an Eloquent array cast. The expansion adds a normalized
catalog and pivot while keeping the original JSON column for compatibility.
Controllers use ProblemTags to synchronize both representations.
The backfill preserves existing JSON labels and all vault/section associations.

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

## Additive expansion schema

Migration `2026_11_06_122221_add_tags_links_and_content_blocks.php` adds:

| Table          | Fields and constraints                                                                                                              |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| tags           | id, name/key (40), timestamps; normalized key unique                                                                                |
| problem_tag    | problem_id, tag_id; composite primary key and reverse lookup index                                                                  |
| problem_links  | problem_id, related_problem_id; canonical smaller-ID-first pair; primary and reverse index                                          |
| content_blocks | id, problem_id, type, content, nullable language, position, nullable source, timestamps; unique problem/position and problem/source |

All new foreign keys cascade only their dependent rows. Removing a tag preserves
problems; removing a problem removes its assignments, links, and blocks. The old
problem columns and attempts are retained. Null block sources permit arbitrary
manual blocks; non-null legacy sources prevent duplicate conversion copies.
Rollback of this migration drops only the added tables but loses their new data;
it does not erase original problem content or JSON tags. Do not roll back real
content without a verified backup.

Tag/reverse-link indexes match actual relationship lookup patterns. No ordinary
text index was added for leading-wildcard LIKE search. Search previews are bounded
and detail fields are excluded from serialized list responses.
