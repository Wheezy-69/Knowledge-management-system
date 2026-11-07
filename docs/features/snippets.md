# Code snippet presentation

## 1. What the feature does

Choose problem/block languages, highlight code, copy exact stored source, and toggle visual wrapping without rewriting data. Unknown languages remain readable as plaintext.

## 2. Implementing files

CodeBlock.vue; lib/highlight.ts; lib/clipboard.ts; ProblemDialog.vue; ContentBlockDialog.vue; bootstrap/app.php.
PHP classes live under `app/`; Vue and TypeScript paths live under `resources/js/`.
Routes are declared in `routes/web.php`.

## 3. Frontend communication

Vue passes source strings and language names into CodeBlock. Copy calls the browser Clipboard API. When unavailable/denied, the code is selected and the user is instructed to copy manually. Wrapping changes CSS only.

## 4. Controller, model, and database

Attempts and corrected solutions keep their existing storage; code blocks have independent language metadata. No code is executed, normalized, or automatically formatted. Legacy fields remain unchanged.

## 5. Validation and errors

Laravel preserves raw code/content whitespace and validates lengths. highlight.js escapes source before its generated markup is rendered. Other content uses escaped Vue interpolation. Clipboard success is shown only after a successful write.

## 6. Testing

clipboard.test.mjs verifies the exact Unicode/whitespace string reaches the writer and denied writes do not claim success. highlight.test.mjs checks safe HTML-looking inputs and fallback. Backend attempt/block tests check exact storage.

## 7. Implementation decisions

Clipboard access depends on browser permissions and secure-context rules; plain HTTP may require manual copying. The async Clipboard API is used rather than deprecated execCommand. Selection fallback and visual wrapping still need real-browser verification.

## 8. Rebuild independently

Keep storage and presentation separate. Test the raw string sent to the clipboard, register explicit grammars, escape rendering, and treat wrapping as a view preference.
