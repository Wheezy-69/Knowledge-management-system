<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { index } from '@/routes/vaults';

const page = usePage();
const vaultsActive = computed(() => page.url.split('?')[0] === index.url());
</script>

<template>
    <aside
        class="flex h-full flex-col gap-10 bg-stone-100 p-6 dark:bg-stone-900"
    >
        <Link
            :href="index()"
            class="flex items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600"
        >
            <span
                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-800 font-mono text-sm font-semibold text-white"
                aria-hidden="true"
                >&lt;/&gt;</span
            >
            <span class="text-lg font-semibold tracking-tight"
                >Knowledge Vault</span
            >
        </Link>

        <nav aria-label="Main navigation" class="flex flex-col gap-3">
            <p
                class="px-3 text-xs font-medium tracking-widest text-stone-500 uppercase dark:text-stone-400"
            >
                Workspace
            </p>
            <Link
                :href="index()"
                :aria-current="vaultsActive ? 'page' : undefined"
                class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium hover:bg-stone-200 focus-visible:outline-2 focus-visible:outline-emerald-600 dark:hover:bg-stone-800"
                :class="{
                    'bg-white shadow-xs dark:bg-stone-800': vaultsActive,
                }"
            >
                <svg
                    class="size-5 text-emerald-800 dark:text-emerald-400"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                </svg>
                Vaults
            </Link>
        </nav>

        <div
            class="mt-auto flex flex-col gap-2 border-t border-stone-200 pt-5 dark:border-stone-800"
        >
            <p class="text-sm font-medium">A little better, every attempt.</p>
            <p class="text-xs leading-5 text-stone-500 dark:text-stone-400">
                Your personal library of lessons learned in code.
            </p>
        </div>
    </aside>
</template>
