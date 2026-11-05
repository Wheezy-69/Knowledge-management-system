<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center gap-5 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center sm:py-24 dark:border-stone-700 dark:bg-stone-900"
    >
        <div
            class="flex size-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400"
            aria-hidden="true"
        >
            <slot name="icon" />
        </div>
        <div class="flex max-w-md flex-col gap-2">
            <h2 class="text-xl font-semibold tracking-tight">{{ title }}</h2>
            <p class="text-sm leading-6 text-stone-500 dark:text-stone-400">
                {{ description }}
            </p>
        </div>
        <slot />
    </div>
</template>
