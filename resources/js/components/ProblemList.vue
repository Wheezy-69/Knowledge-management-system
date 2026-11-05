<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { index } from '@/routes/problems';
import { show } from '@/routes/vaults/sections/problems';
import type { Problem, Vault, Section, Paginated } from '@/types/knowledge';

const props = defineProps<{
    problems: Paginated<Problem>;
    search: string;
    action: string;
    vault?: Vault;
    section?: Section;
}>();
const form = useForm({ search: props.search });
watch(
    () => props.search,
    (value) => {
        form.search = value;
    },
);
function submit() {
    form.get(props.action, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['problems', 'filters'],
    });
}
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
</script>
<template>
    <div class="flex flex-col gap-5">
        <form
            class="flex flex-wrap gap-2"
            role="search"
            @submit.prevent="submit"
        >
            <label for="problem-search" class="sr-only"
                >Search problem titles and descriptions</label
            >
            <Input
                id="problem-search"
                v-model="form.search"
                type="search"
                maxlength="200"
                placeholder="Search titles and descriptions…"
                class="min-w-48 flex-1"
                :aria-invalid="!!form.errors.search"
                aria-describedby="search-error"
            />
            <Button :disabled="form.processing">{{
                form.processing ? 'Searching…' : 'Search'
            }}</Button>
            <Button
                v-if="search || form.search"
                type="button"
                variant="outline"
                :disabled="form.processing"
                @click="
                    form.search = '';
                    submit();
                "
                >Clear</Button
            >
            <p
                v-if="form.errors.search"
                id="search-error"
                role="alert"
                class="w-full text-sm text-destructive"
            >
                {{ form.errors.search }}
            </p>
        </form>
        <p class="text-sm text-muted-foreground" aria-live="polite">
            {{ problems.total }}
            {{ problems.total === 1 ? 'problem' : 'problems'
            }}{{ search ? ' matching “' + search + '”' : '' }}
        </p>
        <div v-if="problems.data.length" class="flex flex-col gap-3">
            <Card
                v-for="problem in problems.data"
                :key="problem.id"
                class="py-0 transition-shadow hover:shadow-md"
            >
                <Link
                    :href="href(problem)"
                    class="rounded-xl focus-visible:outline-2 focus-visible:outline-ring"
                >
                    <CardHeader class="gap-3 py-5">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <CardTitle class="leading-6 wrap-anywhere">{{
                                problem.title
                            }}</CardTitle>
                            <Badge variant="secondary">{{
                                problem.programming_language
                            }}</Badge>
                        </div>
                        <CardDescription
                            class="line-clamp-2 wrap-anywhere whitespace-pre-wrap"
                            >{{
                                problem.description || 'No description yet.'
                            }}</CardDescription
                        >
                        <div
                            class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground"
                        >
                            <span v-if="problem.section?.vault"
                                >{{ problem.section.vault.name }} /
                                {{ problem.section.name }} ·</span
                            >
                            <span
                                >{{
                                    problem.attempts_count ?? 0
                                }}
                                attempts</span
                            >
                            <Badge
                                v-for="tag in problem.tags"
                                :key="tag"
                                variant="outline"
                                >{{ tag }}</Badge
                            >
                        </div>
                    </CardHeader>
                </Link>
            </Card>
        </div>
        <EmptyState
            v-else
            :title="search ? 'No matching problems' : 'No problems yet'"
            :description="
                search
                    ? 'Try another title or a word from the description.'
                    : 'Create a problem within a section to capture your first lesson.'
            "
        />
        <PaginationLinks :links="problems.links" />
    </div>
</template>
