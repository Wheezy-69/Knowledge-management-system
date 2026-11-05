<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';

defineProps<{ title: string }>();
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
                class="flex h-18 shrink-0 items-center gap-4 border-b px-6 sm:px-10"
            >
                <SidebarTrigger />
                <Breadcrumb>
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <span>Workspace</span>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>{{ title }}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>
            </header>
            <main
                id="main-content"
                tabindex="-1"
                class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-6 py-10 focus:outline-none sm:px-10 sm:py-12"
            >
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
