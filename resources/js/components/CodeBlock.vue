<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { highlight } from '@/lib/highlight';
const props = defineProps<{ code: string; language: string }>();
const html = computed(() => highlight(props.code, props.language));
const copyState = ref('Copy');
async function copy() {
    try {
        await navigator.clipboard.writeText(props.code);
        copyState.value = 'Copied';
    } catch {
        copyState.value = 'Select code to copy';
    }
}
</script>
<template>
    <div class="min-w-0 overflow-hidden rounded-lg border bg-muted/40">
        <div class="flex items-center justify-between gap-2 border-b px-4 py-2">
            <span class="text-xs text-muted-foreground">{{ language }}</span>
            <Button variant="ghost" size="sm" @click="copy">{{
                copyState
            }}</Button>
        </div>
        <!-- highlight.js escapes source text; never render raw stored HTML. -->
        <pre
            class="max-h-[40rem] overflow-auto p-4 text-sm leading-6"
            tabindex="0"
            aria-label="Code"
        ><code class="hljs" v-html="html" /></pre>
    </div>
</template>
