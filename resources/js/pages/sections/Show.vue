<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CollectionDialog from '@/components/CollectionDialog.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import PageHeading from '@/components/PageHeading.vue';
import ProblemDialog from '@/components/ProblemDialog.vue';
import ProblemList from '@/components/ProblemList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show as vaultShow } from '@/routes/vaults';
import { show, destroy } from '@/routes/vaults/sections';
import type {
    Vault,
    Section,
    ProblemSummary,
    Paginated,
} from '@/types/knowledge';
import type { SearchFilters, FilterOptions } from '@/types/knowledge';
defineProps<{
    vault: Vault;
    section: Section;
    problems: Paginated<ProblemSummary>;
    filters: SearchFilters;
    filterOptions: FilterOptions;
}>();
</script>
<template>
    <Head :title="section.name" />
    <AppLayout
        :title="section.name"
        :breadcrumbs="[
            { label: 'Vaults', href: index.url() },
            { label: vault.name, href: vaultShow.url(vault.id) },
        ]"
    >
        <PageHeading :title="section.name" :description="section.description">
            <CollectionDialog
                kind="section"
                :vault="vault"
                :section="section"
            />
            <DeleteDialog
                :action="destroy({ vault: vault.id, section: section.id })"
                title="Delete section?"
                description="All problems and attempts in this section will also be deleted."
            />
            <ProblemDialog :vault="vault" :section="section" />
        </PageHeading>
        <ProblemList
            :filters="filters"
            :filter-options="filterOptions"
            :problems="problems"
            :search="filters.search"
            :action="show.url({ vault: vault.id, section: section.id })"
            :vault="vault"
            :section="section"
        />
    </AppLayout>
</template>
