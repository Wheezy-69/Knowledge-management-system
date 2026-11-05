<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EmptyState from '@/components/EmptyState.vue';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Vaults" />

    <AppLayout title="Vaults">
        <div class="flex flex-col gap-3">
            <p
                class="text-xs font-semibold tracking-widest text-emerald-800 uppercase dark:text-emerald-400"
            >
                Your coding knowledge, collected
            </p>
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                Vaults
            </h1>
            <p
                class="max-w-xl text-base leading-7 text-stone-500 dark:text-stone-400"
            >
                A home for your programming mistakes, discoveries, and the
                solutions that finally clicked.
            </p>
        </div>

        <section aria-label="Your vaults" class="flex flex-col gap-5">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-sm font-medium">
                    All vaults
                    <span
                        class="ml-2 rounded-md bg-stone-200 px-2 py-0.5 text-xs text-stone-600 dark:bg-stone-800 dark:text-stone-300"
                        >0</span
                    >
                </h2>
                <span class="text-xs text-stone-500 dark:text-stone-400"
                    >Personal workspace</span
                >
            </div>

            <EmptyState
                title="Your knowledge starts here"
                description="No vaults yet. Soon, you’ll be able to create collections for the languages, frameworks, and concepts you’re learning."
            >
                <template #icon>
                    <svg
                        class="size-8"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"
                        />
                        <path d="m10 12-2 2 2 2m4-4 2 2-2 2" />
                    </svg>
                </template>
                <p
                    class="rounded-full bg-stone-100 px-4 py-2 text-xs font-medium text-stone-600 dark:bg-stone-800 dark:text-stone-300"
                >
                    Vault creation is coming in a future phase
                </p>
            </EmptyState>
        </section>

        <section
            aria-labelledby="organization-heading"
            class="flex flex-col gap-4"
        >
            <h2 id="organization-heading" class="text-sm font-medium">
                From a mistake to a lesson learned
            </h2>
            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <li
                    v-for="(step, i) in [
                        {
                            name: 'Vaults',
                            description: 'Collect a language or area of study.',
                        },
                        {
                            name: 'Sections',
                            description: 'Group related topics together.',
                        },
                        {
                            name: 'Problems',
                            description: 'Document what went wrong and why.',
                        },
                        {
                            name: 'Attempts',
                            description:
                                'Keep each approach and what you learned.',
                        },
                    ]"
                    :key="step.name"
                    class="flex flex-col gap-2 rounded-xl border border-stone-200 p-5 dark:border-stone-800"
                >
                    <span
                        class="font-mono text-xs text-emerald-800 dark:text-emerald-400"
                        >0{{ i + 1 }}</span
                    >
                    <h3 class="text-sm font-semibold">{{ step.name }}</h3>
                    <p
                        class="text-xs leading-5 text-stone-500 dark:text-stone-400"
                    >
                        {{ step.description }}
                    </p>
                </li>
            </ol>
        </section>
    </AppLayout>
</template>
