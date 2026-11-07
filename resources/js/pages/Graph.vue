<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeading from '@/components/PageHeading.vue';
import SearchFiltersForm from '@/components/SearchFiltersForm.vue';
import EmptyState from '@/components/EmptyState.vue';
import { index } from '@/routes/graph';
import type { SearchFilters, FilterOptions } from '@/types/knowledge';
interface Node {
    id: string;
    label: string;
    type: 'problem' | 'tag';
    url: string;
}
const props = defineProps<{
    nodes: Node[];
    edges: { from: string; to: string; type: string }[];
    filters: SearchFilters;
    filterOptions: FilterOptions;
    limited: boolean;
}>();
const positioned = computed(() => {
    let p = 0;
    let t = 0;
    return props.nodes.map((node) => ({
        ...node,
        x: node.type === 'problem' ? 40 : 620,
        y: 30 + (node.type === 'problem' ? p++ : t++) * 64,
    }));
});
const height = computed(() =>
    Math.max(250, ...positioned.value.map((n) => n.y + 70)),
);
function node(id: string) {
    return positioned.value.find((n) => n.id === id);
}
</script>
<template>
    <Head title="Knowledge graph" /><AppLayout title="Knowledge graph"
        ><PageHeading
            title="Knowledge graph"
            description="Problems, reusable tags, and manually linked problems. Click a node to navigate."
        />
        <SearchFiltersForm
            :filters="filters"
            :options="filterOptions"
            :action="index.url()"
            graph
        />
        <p v-if="limited" role="status" class="text-sm text-muted-foreground">
            Showing at most 30 problems and 50 tags. Narrow the filters to see
            another part of the graph.
        </p>
        <EmptyState
            v-if="!nodes.length"
            title="No graph results"
            description="Create problems or adjust your filters."
        />
        <div
            v-else
            class="max-h-[40rem] overflow-auto rounded-xl border bg-card"
        >
            <svg
                :viewBox="'0 0 1000 ' + height"
                width="1000"
                :height="height"
                role="img"
                aria-label="Knowledge relationships"
            >
                <line
                    v-for="(edge, i) in edges"
                    :key="i"
                    :x1="(node(edge.from)?.x ?? 0) + 150"
                    :y1="(node(edge.from)?.y ?? 0) + 20"
                    :x2="(node(edge.to)?.x ?? 0) + 150"
                    :y2="(node(edge.to)?.y ?? 0) + 20"
                    stroke="currentColor"
                    :stroke-dasharray="
                        edge.type === 'related' ? '5 4' : undefined
                    "
                    class="text-muted-foreground/40"
                />
                <Link
                    v-for="item in positioned"
                    :key="item.id"
                    :href="item.url"
                >
                    <title>{{ item.label }}</title>
                    <rect
                        :x="item.x"
                        :y="item.y"
                        width="300"
                        height="42"
                        rx="8"
                        class="fill-background stroke-primary"
                    />
                    <text
                        :x="item.x + 12"
                        :y="item.y + 26"
                        class="fill-foreground text-sm"
                    >
                        {{
                            (item.type === 'tag' ? '# ' : '') +
                            (item.label.length > 32
                                ? item.label.slice(0, 32) + '…'
                                : item.label)
                        }}
                    </text>
                </Link>
            </svg>
        </div>
        <p class="text-xs text-muted-foreground">
            Solid lines: tag assignments. Dashed lines: related problems.
            Isolated problems remain visible.
        </p>
        <details v-if="nodes.length">
            <summary class="cursor-pointer text-sm font-medium">
                Browse graph entries as links
            </summary>
            <ul class="grid gap-2 py-3 sm:grid-cols-2">
                <li v-for="item in nodes" :key="item.id">
                    <Link
                        :href="item.url"
                        class="text-sm text-primary underline"
                        >{{ item.type === 'tag' ? '# ' : ''
                        }}{{ item.label }}</Link
                    >
                </li>
            </ul>
        </details>
    </AppLayout>
</template>
