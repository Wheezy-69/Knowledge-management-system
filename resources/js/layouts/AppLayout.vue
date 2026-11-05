<script setup lang="ts">
import { ref } from 'vue';
import AppSidebar from '@/components/AppSidebar.vue';

defineProps<{ title: string }>();

const navigationOpen = ref(false);
</script>

<template>
    <div
        class="min-h-screen bg-stone-50 text-stone-900 md:grid md:grid-cols-[17rem_minmax(0,1fr)] dark:bg-stone-950 dark:text-stone-100"
    >
        <a
            href="#main-content"
            class="sr-only rounded-lg bg-emerald-800 p-3 text-white focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50"
            >Skip to content</a
        >

        <div
            class="flex items-center justify-between border-b border-stone-200 px-5 py-4 md:hidden dark:border-stone-800"
        >
            <span class="font-semibold">Knowledge Vault</span>
            <button
                type="button"
                aria-controls="app-navigation"
                :aria-expanded="navigationOpen"
                class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-medium hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-emerald-600 dark:border-stone-700 dark:hover:bg-stone-900"
                @click="navigationOpen = !navigationOpen"
            >
                {{ navigationOpen ? 'Close menu' : 'Menu' }}
            </button>
        </div>

        <div
            id="app-navigation"
            :class="navigationOpen ? 'block' : 'hidden'"
            class="border-r border-stone-200 md:sticky md:top-0 md:block md:h-screen dark:border-stone-800"
        >
            <AppSidebar />
        </div>

        <div class="min-w-0">
            <header
                class="flex h-18 items-center border-b border-stone-200 px-6 text-sm text-stone-500 sm:px-10 dark:border-stone-800 dark:text-stone-400"
            >
                <span>Workspace</span>
                <span
                    class="px-3 text-stone-300 dark:text-stone-700"
                    aria-hidden="true"
                    >/</span
                >
                <span class="font-medium text-stone-900 dark:text-stone-100">{{
                    title
                }}</span>
            </header>
            <main
                id="main-content"
                tabindex="-1"
                class="mx-auto flex max-w-6xl flex-col gap-10 px-6 py-10 focus:outline-none sm:px-10 sm:py-12"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
