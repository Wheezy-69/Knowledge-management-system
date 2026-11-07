<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeading from '@/components/PageHeading.vue';
import TagDialog from '@/components/TagDialog.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Card, CardContent } from '@/components/ui/card';
import { destroy } from '@/routes/tags';
import type { Paginated } from '@/types/knowledge';
defineProps<{
    tags: Paginated<{ id: number; name: string; problems_count: number }>;
}>();
</script>
<template>
    <Head title="Tags" /><AppLayout title="Tags"
        ><PageHeading
            title="Tags"
            description="A reusable vocabulary for your knowledge."
            ><TagDialog
        /></PageHeading>
        <Card v-for="tag in tags.data" :key="tag.id"
            ><CardContent
                class="flex flex-wrap items-center justify-between gap-3"
                ><div>
                    <h2 class="font-semibold">{{ tag.name }}</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ tag.problems_count }} problems
                    </p>
                </div>
                <div class="flex gap-2">
                    <TagDialog :tag="tag" /><DeleteDialog
                        :action="destroy(tag.id)"
                        title="Delete tag?"
                        description="The tag will be removed from all problems. Problems and their content will remain."
                    /></div></CardContent
        ></Card>
        <EmptyState
            v-if="!tags.data.length"
            title="No tags yet"
            description="Create a tag here or enter one when editing a problem."
        /><PaginationLinks :links="tags.links" />
    </AppLayout>
</template>
