<script setup lang="ts">
defineProps<{ title: string; description?: string | null }>();
</script>
<template>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex min-w-0 flex-col gap-2">
            <h1 class="text-3xl font-semibold tracking-tight wrap-anywhere">
                {{ title }}
            </h1>
            <p
                v-if="description"
                class="max-w-2xl wrap-anywhere whitespace-pre-wrap text-muted-foreground"
            >
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.default" class="flex shrink-0 flex-wrap gap-2">
            <slot />
        </div>
    </div>
</template>
