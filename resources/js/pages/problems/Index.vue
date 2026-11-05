<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PageHeading from '@/components/PageHeading.vue';
import ProblemList from '@/components/ProblemList.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/problems';
import { index as vaultIndex } from '@/routes/vaults';
import type { Problem, Paginated } from '@/types/knowledge';
defineProps<{ problems: Paginated<Problem>; filters: { search: string } }>();
</script>
<template>
    <Head title="All problems" />
    <AppLayout title="All problems">
        <PageHeading
            title="All problems"
            description="Find a lesson across every vault and section."
            ><Button as-child variant="outline"
                ><Link :href="vaultIndex()">Browse vaults</Link></Button
            ></PageHeading
        >
        <ProblemList
            :problems="problems"
            :search="filters.search"
            :action="index.url()"
        />
    </AppLayout>
</template>
