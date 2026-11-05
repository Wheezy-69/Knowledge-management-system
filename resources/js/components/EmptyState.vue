<script setup lang="ts">
import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';

defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <Empty class="gap-5 rounded-2xl border bg-card px-6 py-16 sm:py-24">
        <EmptyHeader class="max-w-md gap-5">
            <EmptyMedia
                v-if="$slots.icon"
                variant="icon"
                class="size-16 rounded-2xl bg-primary/10 text-primary [&_svg:not([class*='size-'])]:size-8"
                aria-hidden="true"
            >
                <slot name="icon" />
            </EmptyMedia>
            <div class="flex flex-col gap-2">
                <EmptyTitle
                    ><h2 class="text-xl font-semibold">
                        {{ title }}
                    </h2></EmptyTitle
                >
                <EmptyDescription class="text-sm leading-6">{{
                    description
                }}</EmptyDescription>
            </div>
        </EmptyHeader>
        <EmptyContent v-if="$slots.default">
            <slot />
        </EmptyContent>
    </Empty>
</template>
