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
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { store, update } from '@/routes/vaults/sections/problems/attempts';
import type { Vault, Section, Problem, Attempt } from '@/types/knowledge';
const props = defineProps<{
    vault: Vault;
    section: Section;
    problem: Problem;
    attempt?: Attempt;
}>();
const open = ref(false);
const form = useForm({ code: '', notes: '', position: 1 });
watch(open, (value) => {
    if (!value) return;
    form.defaults({
        code: props.attempt?.code ?? '',
        notes: props.attempt?.notes ?? '',
        position:
            props.attempt?.position ??
            Math.max(
                0,
                ...(props.problem.attempts ?? []).map((a) => a.position),
            ) + 1,
    });
    form.reset();
    form.clearErrors();
});
function changeOpen(value: boolean) {
    if (!form.processing) open.value = value;
}
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
    if (props.attempt)
        form.put(update.url({ ...args, attempt: props.attempt.id }), options);
    else form.post(store.url(args), options);
}
</script>
<template>
    <Dialog :open="open" @update:open="changeOpen">
        <DialogTrigger as-child
            ><Button
                :variant="attempt ? 'outline' : 'default'"
                :size="attempt ? 'sm' : 'default'"
                >{{ attempt ? 'Edit' : 'Add attempt' }}</Button
            ></DialogTrigger
        >
        <DialogContent class="max-h-[90svh] overflow-y-auto sm:max-w-3xl">
            <DialogHeader
                ><DialogTitle>{{
                    attempt ? 'Edit attempt' : 'Add code attempt'
                }}</DialogTitle
                ><DialogDescription
                    >Keep your original approach and what you learned from it.
                    Use a unique order number.</DialogDescription
                ></DialogHeader
            >
            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <FormField
                    id="attempt-order"
                    label="Attempt order"
                    :error="form.errors.position"
                    v-slot="field"
                    ><Input
                        id="attempt-order"
                        v-model="form.position"
                        type="number"
                        min="1"
                        max="1000000"
                        required
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                /></FormField>
                <FormField
                    id="attempt-code"
                    label="Code"
                    :error="form.errors.code"
                    v-slot="field"
                    ><Textarea
                        id="attempt-code"
                        v-model="form.code"
                        rows="14"
                        maxlength="100000"
                        required
                        spellcheck="false"
                        class="font-mono"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                /></FormField>
                <FormField
                    id="attempt-notes"
                    label="Notes (optional)"
                    :error="form.errors.notes"
                    v-slot="field"
                    ><Textarea
                        id="attempt-notes"
                        v-model="form.notes"
                        rows="4"
                        maxlength="10000"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                /></FormField>
                <DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        :disabled="form.processing"
                        @click="changeOpen(false)"
                        >Cancel</Button
                    ><Button :disabled="form.processing">{{
                        form.processing ? 'Saving…' : 'Save attempt'
                    }}</Button></DialogFooter
                >
            </form>
        </DialogContent>
    </Dialog>
</template>
