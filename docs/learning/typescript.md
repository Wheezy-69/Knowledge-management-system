# TypeScript in Knowledge Vault

This guide explains the TypeScript already used in the application. Examples are
excerpts from real source files, sometimes with surrounding code omitted.
Paths below are relative to the project root; links open the corresponding source.

Read sections 1–8 first. The later sections explain syntax you will encounter
while studying forms, shadcn-vue components, and generated route helpers.

## 1. TypeScript versus JavaScript

TypeScript adds descriptions of allowed values to JavaScript. Your editor and
type checker use those descriptions to find mistakes before the application runs.
The browser ultimately runs JavaScript, not TypeScript types.

From [CodeBlock.vue](../../resources/js/components/CodeBlock.vue):

```vue
<script setup lang="ts">
import { computed } from 'vue';
import { highlight } from '@/lib/highlight';

const props = defineProps<{ code: string; language: string }>();
const html = computed(() => highlight(props.code, props.language));
</script>
```

`lang="ts"` enables TypeScript inside this Vue single-file component.
`string` means text. The component expects text for both the code and language.
Vue's compiler processes `defineProps`; TypeScript checks how those props are used.

A JavaScript version uses runtime Vue prop declarations instead:

```vue
<script setup>
import { computed } from 'vue';
import { highlight } from '@/lib/highlight';

const props = defineProps({
    code: { type: String, required: true },
    language: { type: String, required: true },
});
const html = computed(() => highlight(props.code, props.language));
</script>
```

The computed behavior is the same. TypeScript additionally helps check calls and
templates during development. It does **not** validate a complete object received
over the network. Laravel requests and database constraints still validate data.

## 2. Interfaces: describing the shape of an object

An interface is a named description of the fields an object should have.
It does not create that object or a database table.

From [types/knowledge.ts](../../resources/js/types/knowledge.ts):

```ts
export interface Vault {
    id: number;
    name: string;
    description: string | null;
    cover_url: string | null;
    sections_count?: number;
}
```

- `id: number` means the ID is numeric.
- `name: string` means the name is text.
- The other marks are explained in section 3.
- `export` lets other files import this description.

Laravel sends a vault as serialized JSON through Inertia. The interface describes
the fields the frontend expects to use from that JSON. It does not contain
Eloquent methods such as `save()` or `sections()`.

JavaScript has no equivalent interface declaration. The runtime value is simply
an object. For example, this illustrates a value matching the project's interface:

```js
const vault = {
    id: 1,
    name: 'PHP',
    description: null,
    cover_url: null,
    sections_count: 0,
};
```

We use interfaces so pages and reusable components agree on what a Vault,
Section, Problem, or Attempt looks like. A misspelling such as
`vault.sectionsCount` is caught because the real field is `sections_count`.

## 3. Optional properties, nullable values, unions, and arrays

These excerpts are all from [types/knowledge.ts](../../resources/js/types/knowledge.ts):

```ts
// Vault
description: string | null;
sections_count?: number;

// Problem
tags: string[] | null;
attempts?: Attempt[];
section?: Section;
```

The `|` character means “or,” and creates a **union type**:

| Declaration                   | Meaning                                                                              |
| ----------------------------- | ------------------------------------------------------------------------------------ |
| `description: string \| null` | The field is required in the described object, but may deliberately contain no text. |
| `sections_count?: number`     | The field may be omitted; reading it may produce undefined.                          |
| `tags: string[] \| null`      | The field contains a list of strings or null.                                        |
| `attempts?: Attempt[]`        | The field may be omitted; when present it contains a list of Attempt objects.        |
| `section?: Section`           | An optional nested object shaped like Section.                                       |

The distinction reflects actual page responses. Vault listings include section
counts; a detail response does not have to. The problem detail loads attempts;
problem lists use counts instead. Empty database text fields may be null.

The declarations disappear in JavaScript. JavaScript can still hold null,
undefined, arrays, and objects; it simply does not enforce the declared contract.

In [AttemptDialog.vue](../../resources/js/components/AttemptDialog.vue), this
expression safely handles an unloaded or empty attempts array:

```ts
Math.max(0, ...(props.problem.attempts ?? []).map((a) => a.position)) + 1;
```

It computes the next order number from the attempts on the Inertia page.
The `??`, array methods, and spread syntax are **JavaScript features**, so this
expression stays the same when written in JavaScript.

## 4. Type aliases and literal types

A `type` declaration gives a reusable name to a type expression. Like an
interface, it does not create runtime data. An interface is convenient for named
object shapes; a type alias can also name unions and types derived from other types.

From [ui/button/index.ts](../../resources/js/components/ui/button/index.ts):

```ts
export type ButtonVariants = VariantProps<typeof buttonVariants>;
```

This derives the allowed button variant and size props from the actual
`buttonVariants` configuration. `typeof` here asks for the TypeScript type of that
value; it is not running JavaScript's `typeof` operator to inspect a value.

The generated button uses the derived type so its props stay aligned with the
configured shadcn-vue variants. There is no JavaScript equivalent to this alias:
the `buttonVariants` function remains at runtime, but the alias disappears.

A **literal type** permits a particular value rather than every possible string.
From [CollectionDialog.vue](../../resources/js/components/CollectionDialog.vue):

```ts
kind: 'vault' | 'section';
```

Only those two strings are allowed. A plain `string` would also allow typos such
as `'vualt'`. Vue's template checking can flag an invalid prop value.

[DeleteDialog.vue](../../resources/js/components/DeleteDialog.vue) similarly uses:

```ts
action: {
    url: string;
    method: 'delete';
}
```

This documents that a delete dialog receives a delete route, rather than any
route object. The actual route is generated by Wayfinder. In JavaScript the
object still contains `url` and `method`, but these declarations provide no
automatic check.

## 5. Function parameters and return types

From [lib/highlight.ts](../../resources/js/lib/highlight.ts):

```ts
export function highlight(code: string, language: string): string {
    const requested = language.trim().toLowerCase();
    return hljs.highlight(code, {
        language: hljs.getLanguage(requested) ? requested : 'plaintext',
        ignoreIllegals: true,
    }).value;
}
```

The annotations between the parentheses describe the inputs. The `: string`
after the closing parenthesis describes the output: highlighted HTML text.

The equivalent JavaScript removes just those annotations:

```js
export function highlight(code, language) {
    const requested = language.trim().toLowerCase();
    return hljs.highlight(code, {
        language: hljs.getLanguage(requested) ? requested : 'plaintext',
        ignoreIllegals: true,
    }).value;
}
```

This is used by Vue's CodeBlock component to display code received through
Inertia. Explicit types make it clear that this function accepts source text and
a language name, and returns a string rather than a DOM element.

From [CollectionDialog.vue](../../resources/js/components/CollectionDialog.vue):

```ts
function changeOpen(value: boolean) {
    if (!form.processing) open.value = value;
}
```

`boolean` allows true or false. This handler receives the dialog's open-state
event. It has no returned value, so TypeScript infers its return type as `void`.
We do not need to write a return annotation on every small function.

The JavaScript version is `function changeOpen(value) { ... }`, with the same body.

## 6. Type inference: types you do not have to write

TypeScript can work out many types from the initial value or returned expression.

From [CollectionDialog.vue](../../resources/js/components/CollectionDialog.vue):

```ts
const open = ref(false);
const editing = computed(() => !!item.value);
```

Vue's `ref(false)` creates a reactive value. TypeScript infers that its value is a
boolean. `!!item.value` converts the value to a boolean, so `editing` is a
computed boolean. These are roughly `Ref<boolean>` and `ComputedRef<boolean>`,
but those annotations are not written in the source.

Both statements are identical in JavaScript. Vue provides the reactivity;
TypeScript helps catch incompatible assignments such as setting `open.value`
to a string.

In script code, refs use `.value`. Vue templates unwrap refs automatically.
The dialog template can use `:open="open"` instead of `:open="open.value"`.
That unwrapping is Vue behavior, not an Inertia feature.

## 7. Generics: reusable types with a placeholder

A generic lets one definition work with different types while retaining the
details of each one. Think of `T` as a blank to fill in.

From [types/knowledge.ts](../../resources/js/types/knowledge.ts):

```ts
export interface Paginated<T> {
    data: T[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}
```

A paginated response always has data, a total, and navigation links.
The item type is the part that changes:

- `Paginated<Vault>` means `data` is a Vault array.
- `Paginated<Problem>` means `data` is a Problem array.

From [pages/vaults/Index.vue](../../resources/js/pages/vaults/Index.vue):

```ts
defineProps<{ vaults: Paginated<Vault> }>();
```

Inertia passes Laravel's paginator response into the Vue page. The generic helps
check each vault used by the page without duplicating the paginator shape.

JavaScript receives the same paginator object. It has no generic declaration
or `<Vault>` argument. Generics neither fetch records nor transform JSON.

Another real generic appears in
[AppLayout.vue](../../resources/js/layouts/AppLayout.vue):

```ts
const page = usePage<{ flash: { success?: string } }>();
```

This tells Inertia's helper about the additional shared props used by the layout.
It describes `page.props.flash.success` as an optional message. The JavaScript
call is `const page = usePage();`; Laravel still supplies the actual flash data.

## 8. Vue defineProps: typed component inputs

`defineProps` is a Vue compiler macro available inside `<script setup>`.
It declares the inputs a parent component may pass to a child.

From [ProblemDialog.vue](../../resources/js/components/ProblemDialog.vue):

```ts
const props = defineProps<{
    vault: Vault;
    section: Section;
    problem?: Problem;
}>();
```

The vault and section are required. The problem is optional because the same
dialog handles two operations:

- No problem prop: create a new problem inside the given section.
- Existing problem prop: edit that problem.

Inertia first supplies data to the page; the page passes it to the dialog using
Vue bindings such as `:vault="vault"` and `:section="section"`.
TypeScript checks those bindings using the prop declarations.

An equivalent JavaScript declaration is:

```js
const props = defineProps({
    vault: { type: Object, required: true },
    section: { type: Object, required: true },
    problem: { type: Object, required: false },
});
```

The generic TypeScript version describes each object's fields more precisely
than JavaScript's `Object` constructor. Neither version validates all nested
server data. Props are inputs; the dialog copies their fields into an Inertia
form instead of directly mutating them.

## 9. withDefaults: making optional props convenient

From [AppLayout.vue](../../resources/js/layouts/AppLayout.vue):

```ts
withDefaults(defineProps<{ title: string; breadcrumbs?: Crumb[] }>(), {
    breadcrumbs: () => [],
});
```

Parents must pass a title but may omit breadcrumbs. Vue then supplies an empty
array. The function `() => []` makes a fresh default array for each instance,
rather than sharing a mutable array between layouts.

The JavaScript runtime-props equivalent is:

```js
defineProps({
    title: { type: String, required: true },
    breadcrumbs: { type: Array, default: () => [] },
});
```

This keeps the layout usable for both top-level and nested Inertia pages.
TypeScript knows that the defaulted breadcrumbs are available inside the
component even when a parent omitted them.

## 10. Typed Inertia form state and assertions

From [ProblemDialog.vue](../../resources/js/components/ProblemDialog.vue),
this excerpt shows the real form fields:

```ts
const form = useForm({
    title: '',
    description: '',
    programming_language: 'plaintext',
    tags: [] as string[],
    corrected_solution: '',
    explanation: '',
});
```

`useForm` is generic, but here TypeScript infers its shape from the object.
For example, title is a string. The `as string[]` tells it that the initially
empty tags array is intended to contain strings. An empty array alone does not
provide an example item from which to infer that element type.

The JavaScript version has exactly these fields but uses `tags: []`.
Inertia supplies reactive values, `errors`, `processing`, and request methods.
Laravel's validation still decides whether submitted tags are acceptable.

From [CollectionDialog.vue](../../resources/js/components/CollectionDialog.vue):

```ts
cover: null as File | null,
```

This form field starts empty but will later hold a browser File object.
The annotation widens the intended type beyond just null. Its JavaScript
equivalent is `cover: null`.

`as` is a **type assertion**, not conversion or validation. It does not turn a
string into a File or check a file's MIME type. Laravel validates the upload.

## 11. DOM event types and narrowing optional values

From [CollectionDialog.vue](../../resources/js/components/CollectionDialog.vue):

```ts
function chooseCover(event: Event) {
    form.cover = (event.target as HTMLInputElement).files?.[0] ?? null;
}
```

A browser Event's target is general: it is not guaranteed to be an input.
The assertion tells TypeScript that this handler is attached to a file input,
which has a `files` property. The first selected file becomes the form cover;
canceling the selection leaves null.

The equivalent JavaScript is:

```js
function chooseCover(event) {
    form.cover = event.target.files?.[0] ?? null;
}
```

Vue calls the handler through `@change="chooseCover"`. Inertia later handles
sending the file. The assertion is justified by the template connection;
if the handler were reused elsewhere, it would need a real runtime check.

From [ProblemList.vue](../../resources/js/components/ProblemList.vue):

```ts
function href(problem: Problem) {
    const section = problem.section ?? props.section;
    const vault = section?.vault ?? props.vault;
    return section && vault
        ? show.url({
              vault: vault.id,
              section: section.id,
              problem: problem.id,
          })
        : index.url();
}
```

This is **narrowing**: in the true branch, TypeScript knows that section and
vault exist because the condition checked them. It can safely allow their IDs.
The JavaScript version removes only `: Problem`; the checks still run.

`?.` stops an access when its receiver is null/undefined.
`??` supplies a fallback for null/undefined. They are JavaScript features,
not types, and do not treat 0 or an empty string as missing.

You will also find `props.vault!.id` in CollectionDialog's editing branch.
The postfix `!` tells TypeScript to assume the vault exists there.
It adds no runtime check and can hide a mistake if the calling code violates
that assumption. Prefer a real conditional guard when existence is uncertain.

## 12. Type-only imports and module aliases

From [pages/vaults/Index.vue](../../resources/js/pages/vaults/Index.vue):

```ts
import type { Vault, Paginated } from '@/types/knowledge';
```

`import type` imports descriptions for checking only. It is removed from the
runtime JavaScript, because an interface is not a value the browser can use.

Compare a real value import from the same page:

```ts
import { show, destroy } from '@/routes/vaults';
```

These functions remain in JavaScript and actually build route objects/URLs.
TypeScript checks their accepted arguments using Wayfinder's generated types.

The `@` alias points to `resources/js`, configured in both `tsconfig.json`
and `vite.config.ts`. It keeps Vue page/component imports readable.
The alias is a project configuration choice, not a special TypeScript type.

JavaScript removes the type-only import but keeps the route-function import.
Wayfinder files are regenerated from Laravel routes; edit `routes/web.php`
rather than generated route files.

## 13. Indexed access types and unknown

From [PaginationLinks.vue](../../resources/js/components/PaginationLinks.vue):

```ts
defineProps<{ links: Paginated<unknown>['links'] }>();
```

Break it into parts:

1. `Paginated<unknown>` describes a paginator without assuming its item type.
2. `['links']` asks TypeScript for the type of that interface's links property.
3. The component accepts just that links array, not the entire paginator.

`unknown` means “we do not know what this value is.” It does not permit arbitrary
property access the way `any` does. Here it is useful because PaginationLinks
never uses the paginator's data items. This avoids duplicating the links type.

The JavaScript component could declare `links: { type: Array, required: true }`.
It loses the checking of each link's URL, label, and active state.

The same indexed-access idea appears in generated shadcn-vue props as
`HTMLAttributes["class"]`, reusing Vue's accepted class-binding type.

## 14. Typed Vue events and v-model

From [ui/input/Input.vue](../../resources/js/components/ui/input/Input.vue):

```ts
const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
}>();
```

This is a function signature describing an event. The event name must be
`update:modelValue`; its value must be a string or number; emitting it does not
return a useful value (`void`).

A JavaScript declaration can list the event name:

```js
const emits = defineEmits(['update:modelValue']);
```

The generated Input uses VueUse's `useVModel` to connect modelValue and that
event. Parent forms use `v-model="form.name"`. Vue propagates edits into the
reactive Inertia form; Laravel sees the values only when the form submits.
TypeScript documents/checks that connection but does not submit the request.

## 15. Async functions and Promise return types

From [CodeBlock.vue](../../resources/js/components/CodeBlock.vue):

```ts
async function copy() {
    try {
        await navigator.clipboard.writeText(props.code);
        copyState.value = 'Copied';
    } catch {
        copyState.value = 'Select code to copy';
    }
}
```

A Promise represents an operation that finishes later. An async function always
returns one. TypeScript infers `Promise<void>` here because the clipboard work
is asynchronous but the function does not return a result value.
That return annotation is inferred, not written in the project.

This function is already valid JavaScript without changes. Vue calls it from
the Copy button. The browser handles clipboard access; Inertia is not involved.
The catch block supplies feedback when clipboard access fails.

The same asynchronous principle appears in the highlighting tests, where
`await test(...)` handles Node's test Promise. The lint check warns about
unhandled Promises, helping prevent accidentally ignored work or failures.

## 16. Declaration files and compiler checks

From [types/vue-shims.d.ts](../../resources/js/types/vue-shims.d.ts):

```ts
declare module '*.vue' {
    import type { DefineComponent } from 'vue';
    const component: DefineComponent;
    export default component;
}
```

A `.d.ts` file describes types without providing executable implementation.
This declaration tells TypeScript how to understand imports of Vue files.
There is no JavaScript equivalent to ship to the browser: the actual Vue
components are compiled separately.

The starter's `types/global.d.ts` also extends library declarations for Vite,
Inertia, and Vue. Those declarations are descriptions, not a guarantee of runtime
behavior. In particular, starter auth-related types do not add authentication;
this MVP remains a single-user local workspace. Begin with the domain interfaces
in `types/knowledge.ts` when studying the implemented feature data.

The project's `tsconfig.json` enables `strict` and `noEmit`.
Strict checking helps find missing/invalid values. NoEmit means the type-checking
command checks code without writing JavaScript output.
`vue-tsc` understands Vue components and their templates; Vite builds the actual
application.

Run:

```sh
npm run types:check
npm run check
npm run build
```

These are different checks: Vue/TypeScript correctness, formatting/linting, and
production bundling. A successful build alone does not replace type checking,
backend validation, or behavioral tests.

## Follow one typed value through the application

1. Laravel's VaultController queries vaults and returns an Inertia paginator.
2. `pages/vaults/Index.vue` describes that prop as `Paginated<Vault>`.
3. The template loops over `vaults.data`; each item is known as a Vault.
4. It passes a vault to CollectionDialog using Vue props.
5. CollectionDialog initializes an Inertia form from the typed optional record.
6. A generated Wayfinder helper supplies the update URL.
7. Laravel validates the submitted values, saves them, and redirects.
8. Inertia supplies fresh props. TypeScript helps us use those props consistently,
   but the real request and database operations happen at runtime.

## Keep this guide current

Whenever a feature changes a frontend data contract or introduces unfamiliar
TypeScript syntax, update this guide in the same change:

- Align `types/knowledge.ts` with the actual Laravel props/serialization.
- Check page `defineProps`, nested component props, and Inertia form defaults.
- Verify optional versus nullable fields against the real responses.
- Regenerate Wayfinder helpers after route changes.
- Refresh the linked excerpts and their JavaScript comparisons.
- Explain new assertions/generics with the feature that uses them.
- Run type checking, formatting/linting, and the relevant feature tests.

This is a maintained learning document, not an automatically generated API
reference. Do not document a proposed type as if the application already uses it.
