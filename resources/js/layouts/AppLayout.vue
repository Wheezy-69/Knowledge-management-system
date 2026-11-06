<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import type { Crumb } from '@/types/knowledge';
import AppSidebar from '@/components/AppSidebar.vue';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbList,
    BreadcrumbLink,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';

withDefaults(defineProps<{ title: string; breadcrumbs?: Crumb[] }>(), {
    breadcrumbs: () => [],
});
const page = usePage<{ flash: { success?: string } }>();
</script>

<template>
    <SidebarProvider style="--sidebar-width: 17rem">
        <a
            href="#main-content"
            class="sr-only rounded-lg bg-primary p-3 text-primary-foreground focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50"
            >Skip to content</a
        >
        <AppSidebar />
        <SidebarInset class="min-w-0 bg-background">
            <header
                class="flex min-h-18 shrink-0 flex-wrap items-center gap-3 border-b px-6 py-3 sm:px-10"
            >
                <SidebarTrigger />
                <Breadcrumb
                    class="order-last w-full min-w-0 sm:order-none sm:w-auto sm:flex-1"
                >
                    <BreadcrumbList>
                        <template
                            v-for="crumb in breadcrumbs"
                            :key="crumb.label"
                        >
                            <BreadcrumbItem class="min-w-0">
                                <BreadcrumbLink v-if="crumb.href" as-child
                                    ><Link
                                        :href="crumb.href"
                                        prefetch
                                        class="max-w-40 truncate"
                                        >{{ crumb.label }}</Link
                                    ></BreadcrumbLink
                                >
                                <span v-else>{{ crumb.label }}</span>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                        </template>
                        <BreadcrumbItem>
                            <BreadcrumbPage class="max-w-48 truncate">{{
                                title
                            }}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>
                <ThemeToggle class="ml-auto" />
            </header>
            <main
                id="main-content"
                tabindex="-1"
                class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-6 py-10 focus:outline-none sm:px-10 sm:py-12"
            >
                <p
                    v-if="page.props.flash?.success"
                    role="status"
                    class="rounded-lg border border-primary/30 bg-primary/10 px-4 py-3 text-sm"
                >
                    {{ page.props.flash.success }}
                </p>
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
