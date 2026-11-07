<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import FormField from '@/components/FormField.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
    DialogFooter,
} from '@/components/ui/dialog';
import { store, update } from '@/routes/vaults/sections/problems/blocks';
import type { Vault, Section, Problem, ContentBlock } from '@/types/knowledge';
const props = defineProps<{
    vault: Vault;
    section: Section;
    problem: Problem;
    block?: ContentBlock;
}>();
const open = ref(false);
const form = useForm({
    type: 'text' as 'text' | 'code',
    content: '',
    language: '',
    position: 1,
});
watch(open, (value) => {
    if (value) {
        form.defaults({
            type: props.block?.type ?? 'text',
            content: props.block?.content ?? '',
            language:
                props.block?.language ?? props.problem.programming_language,
            position:
                props.block?.position ??
                Math.max(
                    0,
                    ...(props.problem.blocks ?? []).map((b) => b.position),
                ) + 1,
        });
        form.reset();
        form.clearErrors();
    }
});
function submit() {
    const args = {
        vault: props.vault.id,
        section: props.section.id,
        problem: props.problem.id,
    };
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };
    if (props.block)
        form.put(update.url({ ...args, block: props.block.id }), options);
    else form.post(store.url(args), options);
}
</script>
<template>
    <Dialog v-model:open="open"
        ><DialogTrigger as-child
            ><Button
                :variant="block ? 'outline' : 'default'"
                :size="block ? 'sm' : 'default'"
                >{{ block ? 'Edit' : 'Add block' }}</Button
            ></DialogTrigger
        >
        <DialogContent class="max-h-[90svh] overflow-auto sm:max-w-3xl"
            ><DialogHeader
                ><DialogTitle>{{
                    block ? 'Edit block' : 'Add content block'
                }}</DialogTitle
                ><DialogDescription
                    >Text stays plain text. Code preserves whitespace and is
                    never executed.</DialogDescription
                ></DialogHeader
            >
            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <label class="flex flex-col gap-2 text-sm"
                    >Type<select
                        v-model="form.type"
                        class="h-9 rounded-md border bg-background px-3"
                    >
                        <option value="text">Text</option>
                        <option value="code">Code</option>
                    </select></label
                >
                <FormField
                    id="block-order"
                    label="Order"
                    :error="form.errors.position"
                    ><Input
                        id="block-order"
                        v-model="form.position"
                        type="number"
                        min="1"
                        max="1000000"
                        required
                /></FormField>
                <FormField
                    v-if="form.type === 'code'"
                    id="block-language"
                    label="Language"
                    :error="form.errors.language"
                    ><Input
                        id="block-language"
                        v-model="form.language"
                        maxlength="40"
                        placeholder="plaintext"
                /></FormField>
                <FormField
                    id="block-content"
                    label="Content"
                    :error="form.errors.content"
                    ><Textarea
                        id="block-content"
                        v-model="form.content"
                        rows="12"
                        maxlength="100000"
                        required
                        :spellcheck="form.type !== 'code'"
                        :class="{ 'font-mono': form.type === 'code' }"
                /></FormField>
                <p
                    v-if="form.errors.type"
                    role="alert"
                    class="text-sm text-destructive"
                >
                    {{ form.errors.type }}
                </p>
                <DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        :disabled="form.processing"
                        @click="open = false"
                        >Cancel</Button
                    ><Button :disabled="form.processing">{{
                        form.processing ? 'Saving…' : 'Save block'
                    }}</Button></DialogFooter
                >
            </form>
        </DialogContent>
    </Dialog>
</template>
