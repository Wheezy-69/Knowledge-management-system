<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeading from '@/components/PageHeading.vue';
import CodeBlock from '@/components/CodeBlock.vue';
import { Button } from '@/components/ui/button';
import { compareLines } from '@/lib/diff';
import { show, compare } from '@/routes/vaults/sections/problems';
import type { Vault, Section, Problem } from '@/types/knowledge';
const props = defineProps<{
    vault: Vault;
    section: Section;
    problem: Problem;
    choices: { id: string; label: string; code: string }[];
    left: string;
    right: string;
}>();
const form = useForm({ left: props.left, right: props.right });
const leftCode = computed(
    () => props.choices.find((c) => c.id === props.left)?.code ?? '',
);
const rightCode = computed(
    () => props.choices.find((c) => c.id === props.right)?.code ?? '',
);
const diff = computed(() => compareLines(leftCode.value, rightCode.value));
function submit() {
    form.get(
        compare.url({
            vault: props.vault.id,
            section: props.section.id,
            problem: props.problem.id,
        }),
        { preserveScroll: true },
    );
}
</script>
<template>
    <Head title="Compare code" /><AppLayout title="Compare code"
        ><PageHeading
            :title="'Compare: ' + problem.title"
            description="Whitespace-sensitive line comparison. Original snippets are never changed."
            ><Button as-child variant="outline"
                ><Link
                    :href="
                        show({
                            vault: vault.id,
                            section: section.id,
                            problem: problem.id,
                        })
                    "
                    >Back to problem</Link
                ></Button
            ></PageHeading
        >
        <form class="flex flex-wrap items-end gap-3" @submit.prevent="submit">
            <label
                v-for="side in ['left', 'right'] as const"
                :key="side"
                class="flex min-w-48 flex-1 flex-col gap-2 text-sm"
                >{{ side === 'left' ? 'Original' : 'Compare with'
                }}<select
                    v-model="form[side]"
                    class="h-10 rounded-md border bg-background px-3"
                >
                    <option
                        v-for="choice in choices"
                        :key="choice.id"
                        :value="choice.id"
                    >
                        {{ choice.label }}
                    </option>
                </select></label
            ><Button :disabled="form.processing">Compare</Button>
        </form>
        <p class="text-sm text-muted-foreground">
            − Red: removed from original. + Green: added in comparison. Unmarked
            lines are equal.
        </p>
        <template v-if="diff.limited"
            ><p role="status" class="rounded-lg border p-4">
                This comparison is too large for an interactive diff. The
                unchanged snippets are shown below.
            </p>
            <div class="grid min-w-0 gap-4 lg:grid-cols-2">
                <CodeBlock
                    :code="leftCode"
                    :language="problem.programming_language"
                /><CodeBlock
                    :code="rightCode"
                    :language="problem.programming_language"
                /></div
        ></template>
        <div v-else class="overflow-auto rounded-xl border">
            <div
                class="grid min-w-[40rem] grid-cols-2 bg-muted px-3 py-2 text-sm font-semibold"
            >
                <span>Original</span><span>Comparison</span>
            </div>
            <div
                v-for="(row, i) in diff.rows"
                :key="i"
                class="grid min-w-[40rem] grid-cols-2 text-sm"
            >
                <pre
                    class="min-h-6 border-r px-3 font-mono whitespace-pre-wrap"
                    :class="{
                        'bg-red-100 text-red-950 dark:bg-red-950 dark:text-red-100':
                            row.kind === 'remove',
                    }"
                    >{{
                        row.left === null
                            ? ''
                            : (row.kind === 'remove' ? '− ' : '  ') + row.left
                    }}</pre>
                <pre
                    class="min-h-6 px-3 font-mono whitespace-pre-wrap"
                    :class="{
                        'bg-emerald-100 text-emerald-950 dark:bg-emerald-950 dark:text-emerald-100':
                            row.kind === 'add',
                    }"
                    >{{
                        row.right === null
                            ? ''
                            : (row.kind === 'add' ? '+ ' : '  ') + row.right
                    }}</pre>
            </div>
            <p v-if="!diff.rows.length" class="p-5 text-muted-foreground">
                Both snippets are empty.
            </p>
        </div>
    </AppLayout>
</template>
