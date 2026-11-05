<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FolderCode } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

const organizationSteps = [
    { name: 'Vaults', description: 'Collect a language or area of study.' },
    { name: 'Sections', description: 'Group related topics together.' },
    { name: 'Problems', description: 'Document what went wrong and why.' },
    {
        name: 'Attempts',
        description: 'Keep each approach and what you learned.',
    },
];
</script>

<template>
    <Head title="Vaults" />
    <AppLayout title="Vaults">
        <div class="flex flex-col gap-3">
            <p
                class="text-xs font-semibold tracking-widest text-primary uppercase"
            >
                Your coding knowledge, collected
            </p>
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                Vaults
            </h1>
            <p class="max-w-xl text-base leading-7 text-muted-foreground">
                A home for your programming mistakes, discoveries, and the
                solutions that finally clicked.
            </p>
        </div>

        <section aria-label="Your vaults" class="flex flex-col gap-5">
            <div class="flex items-center justify-between gap-4">
                <h2 class="flex items-center gap-2 text-sm font-medium">
                    All vaults <Badge variant="secondary">0</Badge>
                </h2>
                <span class="text-xs text-muted-foreground"
                    >Personal workspace</span
                >
            </div>
            <EmptyState
                title="Your knowledge starts here"
                description="No vaults yet. Soon, you’ll be able to create collections for the languages, frameworks, and concepts you’re learning."
            >
                <template #icon>
                    <FolderCode class="size-8" />
                </template>
                <Badge
                    variant="secondary"
                    class="max-w-full px-4 py-2 text-center whitespace-normal"
                    >Vault creation is coming in a future phase</Badge
                >
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
                <li v-for="(step, i) in organizationSteps" :key="step.name">
                    <Card class="h-full gap-0 py-5 shadow-none">
                        <CardHeader class="gap-2 px-5">
                            <span class="font-mono text-xs text-primary"
                                >0{{ i + 1 }}</span
                            >
                            <CardTitle class="text-sm">{{
                                step.name
                            }}</CardTitle>
                            <CardDescription class="text-xs leading-5">{{
                                step.description
                            }}</CardDescription>
                        </CardHeader>
                    </Card>
                </li>
            </ol>
        </section>
    </AppLayout>
</template>
