<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types/knowledge';
defineProps<{ links: Paginated<unknown>['links'] }>();
function label(text: string) {
    return text.replace('&laquo;', '«').replace('&raquo;', '»');
}
</script>
<template>
    <nav
        v-if="links.length > 3"
        aria-label="Pagination"
        class="flex flex-wrap justify-center gap-2"
    >
        <template v-for="(link, i) in links" :key="i">
            <Button
                v-if="link.url"
                as-child
                size="sm"
                :variant="link.active ? 'default' : 'outline'"
            >
                <Link
                    :href="link.url"
                    :aria-current="link.active ? 'page' : undefined"
                    preserve-scroll
                    >{{ label(link.label) }}</Link
                >
            </Button>
            <Button v-else disabled variant="outline" size="sm">{{
                label(link.label)
            }}</Button>
        </template>
    </nav>
</template>
