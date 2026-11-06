<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { FolderCode } from '@lucide/vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter,
} from '@/components/ui/card';
defineProps<{
    title: string;
    description: string | null;
    href: string;
    count: string;
    cover?: string | null;
}>();
</script>
<template>
    <Card class="overflow-hidden py-0 transition-shadow hover:shadow-md">
        <Link
            :href="href"
            prefetch
            class="flex flex-1 flex-col rounded-t-xl focus-visible:outline-2 focus-visible:outline-ring"
        >
            <img
                v-if="cover"
                :src="cover"
                alt=""
                class="h-36 w-full object-cover"
                loading="lazy"
            />
            <div
                v-else
                class="flex h-28 items-center justify-center bg-primary/10 text-primary"
                aria-hidden="true"
            >
                <FolderCode class="size-9" />
            </div>
            <CardHeader class="gap-2 py-5">
                <CardTitle class="leading-6 wrap-anywhere">{{
                    title
                }}</CardTitle>
                <CardDescription
                    class="line-clamp-3 wrap-anywhere whitespace-pre-wrap"
                    >{{ description || 'No description yet.' }}</CardDescription
                >
            </CardHeader>
        </Link>
        <CardFooter class="flex flex-wrap justify-between gap-2 border-t py-4">
            <span class="text-xs text-muted-foreground">{{ count }}</span>
            <div class="flex gap-2"><slot /></div>
        </CardFooter>
    </Card>
</template>
