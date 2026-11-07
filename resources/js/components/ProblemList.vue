<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SearchFiltersForm from '@/components/SearchFiltersForm.vue';
import EmptyState from '@/components/EmptyState.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { index } from '@/routes/problems';
import { show } from '@/routes/vaults/sections/problems';
import type {
    ProblemSummary,
    Vault,
    Section,
    Paginated,
} from '@/types/knowledge';

const props = defineProps<{
    problems: Paginated<ProblemSummary>;
    search: string;
    filters: import('@/types/knowledge').SearchFilters;
    filterOptions: import('@/types/knowledge').FilterOptions;
    action: string;
    vault?: Vault;
    section?: Section;
}>();
function href(problem: ProblemSummary) {
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
        <SearchFiltersForm
            :filters="filters"
            :options="filterOptions"
            :action="action"
            :scoped="!!section"
        />
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
                    prefetch
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
                                problem.preview
                                    ? problem.preview.field +
                                      ': ' +
                                      problem.preview.text
                                    : problem.description ||
                                      'No description yet.'
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
