<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { highlight } from '@/lib/highlight';
import { copyText } from '@/lib/clipboard';
const props = defineProps<{ code: string; language: string }>();
const html = computed(() => highlight(props.code, props.language));
const copyState = ref('Copy');
const codeElement = ref<HTMLElement | null>(null);
const wrap = ref(false);
async function copy() {
    if (await copyText(props.code)) {
        copyState.value = 'Copied';
    } else {
        if (codeElement.value) {
            const range = document.createRange();
            range.selectNodeContents(codeElement.value);
            const selection = window.getSelection();
            selection?.removeAllRanges();
            selection?.addRange(range);
        }
        copyState.value = 'Selected — press Ctrl/Cmd+C';
    }
}
</script>
<template>
    <div class="min-w-0 overflow-hidden rounded-lg border bg-muted/40">
        <div class="flex items-center justify-between gap-2 border-b px-4 py-2">
            <Button
                variant="ghost"
                size="sm"
                :aria-pressed="wrap"
                @click="wrap = !wrap"
                >{{ wrap ? 'No wrap' : 'Wrap lines' }}</Button
            >
            <span class="text-xs text-muted-foreground">{{ language }}</span>
            <Button variant="ghost" size="sm" @click="copy">{{
                copyState
            }}</Button>
        </div>
        <!-- highlight.js escapes source text; never render raw stored HTML. -->
        <pre
            :class="{ 'break-all whitespace-pre-wrap': wrap }"
            class="max-h-[40rem] overflow-auto p-4 text-sm leading-6"
            tabindex="0"
            aria-label="Code"
        ><code ref="codeElement" class="hljs" v-html="html" /></pre>
    </div>
</template>
