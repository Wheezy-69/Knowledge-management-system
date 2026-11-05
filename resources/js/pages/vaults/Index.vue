<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CollectionCard from '@/components/CollectionCard.vue';
import CollectionDialog from '@/components/CollectionDialog.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeading from '@/components/PageHeading.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { show, destroy } from '@/routes/vaults';
import type { Vault, Paginated } from '@/types/knowledge';
defineProps<{ vaults: Paginated<Vault> }>();
</script>
<template>
    <Head title="Vaults" />
    <AppLayout title="Vaults">
        <PageHeading
            title="Vaults"
            description="Your programming mistakes, discoveries, and the solutions that finally clicked."
            ><CollectionDialog kind="vault"
        /></PageHeading>
        <div
            v-if="vaults.data.length"
            class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
        >
            <CollectionCard
                v-for="vault in vaults.data"
                :key="vault.id"
                :title="vault.name"
                :description="vault.description"
                :href="show.url(vault.id)"
                :cover="vault.cover_url"
                :count="(vault.sections_count ?? 0) + ' sections'"
            >
                <CollectionDialog kind="vault" :vault="vault" />
                <DeleteDialog
                    :action="destroy(vault.id)"
                    title="Delete vault?"
                    :description="
                        'Delete “' +
                        vault.name +
                        '” and all its sections, problems, and attempts.'
                    "
                />
            </CollectionCard>
        </div>
        <EmptyState
            v-else
            title="Your knowledge starts here"
            description="Create a vault for a language, framework, or topic you are learning."
            ><CollectionDialog kind="vault"
        /></EmptyState>
        <PaginationLinks :links="vaults.links" />
    </AppLayout>
</template>
