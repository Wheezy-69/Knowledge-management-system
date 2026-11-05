<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CollectionCard from '@/components/CollectionCard.vue';
import CollectionDialog from '@/components/CollectionDialog.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeading from '@/components/PageHeading.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, destroy } from '@/routes/vaults';
import {
    show as sectionShow,
    destroy as sectionDestroy,
} from '@/routes/vaults/sections';
import type { Vault, Section, Paginated } from '@/types/knowledge';
defineProps<{ vault: Vault; sections: Paginated<Section> }>();
</script>
<template>
    <Head :title="vault.name" />
    <AppLayout
        :title="vault.name"
        :breadcrumbs="[{ label: 'Vaults', href: index.url() }]"
    >
        <img
            v-if="vault.cover_url"
            :src="vault.cover_url"
            alt=""
            class="max-h-64 w-full rounded-xl object-cover"
        />
        <PageHeading :title="vault.name" :description="vault.description">
            <CollectionDialog kind="vault" :vault="vault" />
            <DeleteDialog
                :action="destroy(vault.id)"
                title="Delete vault?"
                description="All sections, problems, and attempts in this vault will also be deleted."
            />
            <CollectionDialog kind="section" :vault="vault" />
        </PageHeading>
        <h2 class="text-lg font-semibold">Sections</h2>
        <div
            v-if="sections.data.length"
            class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
        >
            <CollectionCard
                v-for="section in sections.data"
                :key="section.id"
                :title="section.name"
                :description="section.description"
                :href="
                    sectionShow.url({ vault: vault.id, section: section.id })
                "
                :count="(section.problems_count ?? 0) + ' problems'"
            >
                <CollectionDialog
                    kind="section"
                    :vault="vault"
                    :section="section"
                />
                <DeleteDialog
                    :action="
                        sectionDestroy({ vault: vault.id, section: section.id })
                    "
                    title="Delete section?"
                    :description="
                        'Delete “' +
                        section.name +
                        '” and all its problems and attempts.'
                    "
                />
            </CollectionCard>
        </div>
        <EmptyState
            v-else
            title="Give your vault some structure"
            description="Create a section to group related programming problems."
            ><CollectionDialog kind="section" :vault="vault"
        /></EmptyState>
        <PaginationLinks :links="sections.links" />
    </AppLayout>
</template>
