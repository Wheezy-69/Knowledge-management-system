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
import { store, update } from '@/routes/vaults/sections/problems';
import type { Vault, Section, Problem } from '@/types/knowledge';
const props = defineProps<{
    vault: Vault;
    section: Section;
    problem?: Problem;
}>();
const open = ref(false);
const tagsText = ref('');
const form = useForm({
    title: '',
    description: '',
    programming_language: 'plaintext',
    tags: [] as string[],
    corrected_solution: '',
    explanation: '',
});
watch(open, (value) => {
    if (!value) return;
    const p = props.problem;
    form.defaults({
        title: p?.title ?? '',
        description: p?.description ?? '',
        programming_language: p?.programming_language ?? 'plaintext',
        tags: p?.tags ?? [],
        corrected_solution: p?.corrected_solution ?? '',
        explanation: p?.explanation ?? '',
    });
    form.reset();
    form.clearErrors();
    tagsText.value = (p?.tags ?? []).join(', ');
});
function changeOpen(value: boolean) {
    if (!form.processing) open.value = value;
}
function submit() {
    form.tags = tagsText.value
        .split(',')
        .map((tag) => tag.trim())
        .filter(Boolean);
    const args = { vault: props.vault.id, section: props.section.id };
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };
    if (props.problem)
        form.put(update.url({ ...args, problem: props.problem.id }), options);
    else form.post(store.url(args), options);
}
</script>
<template>
    <Dialog :open="open" @update:open="changeOpen">
        <DialogTrigger as-child
            ><Button
                :variant="problem ? 'outline' : 'default'"
                :size="problem ? 'sm' : 'default'"
                >{{ problem ? 'Edit problem' : 'New problem' }}</Button
            ></DialogTrigger
        >
        <DialogContent class="max-h-[90svh] overflow-y-auto sm:max-w-3xl">
            <DialogHeader
                ><DialogTitle>{{
                    problem ? 'Edit problem' : 'Create problem'
                }}</DialogTitle
                ><DialogDescription
                    >Capture the problem now. Add your solution and explanation
                    whenever you are ready.</DialogDescription
                ></DialogHeader
            >
            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <FormField
                    id="problem-title"
                    label="Title"
                    :error="form.errors.title"
                    v-slot="field"
                    ><Input
                        id="problem-title"
                        v-model="form.title"
                        required
                        maxlength="200"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                        autofocus
                /></FormField>
                <FormField
                    id="problem-description"
                    label="Description (optional)"
                    :error="form.errors.description"
                    v-slot="field"
                    ><Textarea
                        id="problem-description"
                        v-model="form.description"
                        rows="4"
                        maxlength="50000"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                /></FormField>
                <FormField
                    id="problem-language"
                    label="Programming language"
                    :error="form.errors.programming_language"
                    v-slot="field"
                >
                    <Input
                        id="problem-language"
                        v-model="form.programming_language"
                        list="code-languages"
                        required
                        maxlength="40"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                    />
                    <datalist id="code-languages">
                        <option
                            v-for="language in [
                                'plaintext',
                                'php',
                                'javascript',
                                'typescript',
                                'python',
                                'html',
                                'css',
                                'sql',
                                'json',
                                'bash',
                                'java',
                                'csharp',
                                'cpp',
                                'go',
                                'rust',
                                'ruby',
                            ]"
                            :key="language"
                            :value="language"
                        />
                    </datalist>
                </FormField>
                <FormField
                    id="problem-tags"
                    label="Tags (comma separated, optional)"
                    :error="
                        form.errors.tags ||
                        Object.entries(form.errors).find(([key]) =>
                            key.startsWith('tags.'),
                        )?.[1]
                    "
                    v-slot="field"
                    ><Input
                        id="problem-tags"
                        v-model="tagsText"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                        placeholder="arrays, validation, debugging"
                /></FormField>
                <FormField
                    id="problem-solution"
                    label="Corrected solution (optional)"
                    :error="form.errors.corrected_solution"
                    v-slot="field"
                    ><Textarea
                        id="problem-solution"
                        v-model="form.corrected_solution"
                        rows="10"
                        maxlength="100000"
                        spellcheck="false"
                        class="font-mono"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                /></FormField>
                <FormField
                    id="problem-explanation"
                    label="Explanation (optional)"
                    :error="form.errors.explanation"
                    v-slot="field"
                    ><Textarea
                        id="problem-explanation"
                        v-model="form.explanation"
                        rows="5"
                        maxlength="50000"
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
                        form.processing ? 'Saving…' : 'Save problem'
                    }}</Button></DialogFooter
                >
            </form>
        </DialogContent>
    </Dialog>
</template>
