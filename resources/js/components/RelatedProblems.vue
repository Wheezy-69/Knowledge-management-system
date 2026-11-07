<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import DeleteDialog from '@/components/DeleteDialog.vue';
import { store, destroy } from '@/routes/vaults/sections/problems/related';
import { show } from '@/routes/vaults/sections/problems';
import type {
    Vault,
    Section,
    Problem,
    RelatedProblem,
} from '@/types/knowledge';
const props = defineProps<{
    vault: Vault;
    section: Section;
    problem: Problem;
    related: RelatedProblem[];
    candidates: { id: number; title: string }[];
}>();
const form = useForm({ related_problem_id: '' });
function submit() {
    form.post(
        store.url({
            vault: props.vault.id,
            section: props.section.id,
            problem: props.problem.id,
        }),
        { preserveScroll: true, onSuccess: () => form.reset() },
    );
}
</script>
<template>
    <section class="flex flex-col gap-4" aria-labelledby="related-heading">
        <h2 id="related-heading" class="text-xl font-semibold">
            Related problems
        </h2>
        <div
            v-for="item in related"
            :key="item.id"
            class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-4"
        >
            <Link
                :href="
                    show({
                        vault: item.section.vault_id,
                        section: item.section_id,
                        problem: item.id,
                    })
                "
                class="font-medium text-primary underline"
                prefetch
                >{{ item.title }}</Link
            ><DeleteDialog
                label="Unlink"
                confirm-label="Remove link"
                :irreversible="false"
                :action="
                    destroy({
                        vault: vault.id,
                        section: section.id,
                        problem: problem.id,
                        related: item.id,
                    })
                "
                title="Remove relationship?"
                description="Both problems and their contents will remain."
            />
        </div>
        <p v-if="!related.length" class="text-sm text-muted-foreground">
            No related problems yet.
        </p>
        <form class="flex flex-wrap gap-2" @submit.prevent="submit">
            <label class="flex min-w-48 flex-1 flex-col gap-2 text-sm"
                >Link a problem<select
                    v-model="form.related_problem_id"
                    required
                    class="h-10 rounded-md border bg-background px-3"
                >
                    <option value="" disabled>Select a problem</option>
                    <option
                        v-for="candidate in candidates"
                        :key="candidate.id"
                        :value="candidate.id"
                    >
                        {{ candidate.title }}
                    </option>
                </select></label
            ><Button
                class="self-end"
                :disabled="form.processing || !form.related_problem_id"
                >{{ form.processing ? 'Linking…' : 'Link problem' }}</Button
            >
            <p
                v-if="form.errors.related_problem_id"
                role="alert"
                class="w-full text-sm text-destructive"
            >
                {{ form.errors.related_problem_id }}
            </p>
        </form>
    </section>
</template>
