<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, X } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { index } from '@/routes/vaults';
import { index as problemIndex } from '@/routes/problems';
import { index as tagIndex } from '@/routes/tags';
import { index as backupIndex } from '@/routes/backups';
import { index as graphIndex } from '@/routes/graph';
const secondaryNavigation = [
    { label: 'Tags', route: tagIndex() },
    { label: 'Knowledge graph', route: graphIndex() },
    { label: 'Backup and restore', route: backupIndex() },
];

const page = usePage();
const vaultsActive = computed(() =>
    page.url.split('?')[0].startsWith(index.url()),
);
const problemsActive = computed(
    () => page.url.split('?')[0] === problemIndex.url(),
);
const { isMobile, setOpenMobile } = useSidebar();
</script>

<template>
    <Sidebar collapsible="offcanvas">
        <SidebarHeader class="p-6">
            <Button
                v-if="isMobile"
                variant="ghost"
                size="icon"
                class="self-end"
                @click="setOpenMobile(false)"
            >
                <X aria-hidden="true" />
                <span class="sr-only">Close menu</span>
            </Button>
            <Link
                :href="index()"
                prefetch
                class="flex items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                @click="setOpenMobile(false)"
            >
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sidebar-primary font-mono text-sm font-semibold text-sidebar-primary-foreground"
                    aria-hidden="true"
                    >&lt;/&gt;</span
                >
                <span class="text-lg font-semibold tracking-tight"
                    >Knowledge Vault</span
                >
            </Link>
        </SidebarHeader>

        <SidebarContent class="px-4">
            <nav aria-label="Main navigation">
                <SidebarGroup>
                    <SidebarGroupLabel>Workspace</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem
                                v-for="item in secondaryNavigation"
                                :key="item.label"
                                ><SidebarMenuButton
                                    as-child
                                    :is-active="
                                        page.url.split('?')[0] ===
                                        item.route.url
                                    "
                                    size="lg"
                                    ><Link
                                        :href="item.route"
                                        prefetch
                                        @click="setOpenMobile(false)"
                                        ><LayoutGrid
                                            aria-hidden="true"
                                        /><span>{{ item.label }}</span></Link
                                    ></SidebarMenuButton
                                ></SidebarMenuItem
                            >
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    as-child
                                    :is-active="problemsActive"
                                    size="lg"
                                >
                                    <Link
                                        :href="problemIndex()"
                                        prefetch
                                        :aria-current="
                                            problemsActive ? 'page' : undefined
                                        "
                                        @click="setOpenMobile(false)"
                                        ><LayoutGrid aria-hidden="true" /><span
                                            >All problems</span
                                        ></Link
                                    >
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    as-child
                                    :is-active="vaultsActive"
                                    size="lg"
                                >
                                    <Link
                                        :href="index()"
                                        prefetch
                                        :aria-current="
                                            vaultsActive ? 'page' : undefined
                                        "
                                        @click="setOpenMobile(false)"
                                    >
                                        <LayoutGrid
                                            class="text-primary"
                                            aria-hidden="true"
                                        />
                                        <span>Vaults</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </nav>
        </SidebarContent>

        <SidebarFooter class="gap-2 border-t p-6">
            <p class="text-sm font-medium">A little better, every attempt.</p>
            <p class="text-xs leading-5 text-muted-foreground">
                Your personal library of lessons learned in code.
            </p>
        </SidebarFooter>
    </Sidebar>
</template>
