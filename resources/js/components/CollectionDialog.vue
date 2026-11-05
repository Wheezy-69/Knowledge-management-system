<script setup lang="ts">
import { computed, ref, watch } from 'vue';
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
import * as vaultRoutes from '@/routes/vaults';
import * as sectionRoutes from '@/routes/vaults/sections';
import type { Section, Vault } from '@/types/knowledge';

const props = defineProps<{
    kind: 'vault' | 'section';
    vault?: Vault;
    section?: Section;
}>();
const open = ref(false);
const item = computed(() =>
    props.kind === 'vault' ? props.vault : props.section,
);
const editing = computed(() => !!item.value);
const title = computed(
    () => (editing.value ? 'Edit ' : 'Create ') + props.kind,
);
const form = useForm({
    name: '',
    description: '',
    cover: null as File | null,
    remove_cover: false,
    _method: 'post',
});
watch(open, (value) => {
    if (!value) return;
    form.defaults({
        name: item.value?.name ?? '',
        description: item.value?.description ?? '',
        cover: null,
        remove_cover: false,
        _method: editing.value ? 'put' : 'post',
    });
    form.reset();
    form.clearErrors();
});
function changeOpen(value: boolean) {
    if (!form.processing) open.value = value;
}
function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };
    if (props.kind === 'vault') {
        form.post(
            editing.value
                ? vaultRoutes.update.url(props.vault!.id)
                : vaultRoutes.store.url(),
            options,
        );
    } else if (props.vault) {
        if (props.section)
            form.put(
                sectionRoutes.update.url({
                    vault: props.vault.id,
                    section: props.section.id,
                }),
                options,
            );
        else form.post(sectionRoutes.store.url(props.vault.id), options);
    }
}
function chooseCover(event: Event) {
    form.cover = (event.target as HTMLInputElement).files?.[0] ?? null;
}
</script>
<template>
    <Dialog :open="open" @update:open="changeOpen">
        <DialogTrigger as-child
            ><Button
                :variant="editing ? 'outline' : 'default'"
                :size="editing ? 'sm' : 'default'"
                >{{ editing ? 'Edit' : 'New ' + kind }}</Button
            ></DialogTrigger
        >
        <DialogContent class="max-h-[90svh] overflow-y-auto">
            <DialogHeader
                ><DialogTitle>{{ title }}</DialogTitle
                ><DialogDescription>{{
                    kind === 'vault'
                        ? 'Organize a language, framework, or area of study.'
                        : 'Group related problems within this vault.'
                }}</DialogDescription></DialogHeader
            >
            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <FormField
                    id="collection-name"
                    label="Name"
                    :error="form.errors.name"
                    v-slot="field"
                >
                    <Input
                        id="collection-name"
                        v-model="form.name"
                        required
                        maxlength="120"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                        autofocus
                    />
                </FormField>
                <FormField
                    id="collection-description"
                    label="Description (optional)"
                    :error="form.errors.description"
                    v-slot="field"
                >
                    <Textarea
                        id="collection-description"
                        v-model="form.description"
                        rows="4"
                        maxlength="10000"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedby"
                    />
                </FormField>
                <template v-if="kind === 'vault'">
                    <FormField
                        id="collection-cover"
                        label="Cover image (optional)"
                        :error="form.errors.cover"
                        v-slot="field"
                    >
                        <Input
                            id="collection-cover"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            :aria-invalid="field.invalid"
                            :aria-describedby="field.describedby"
                            @change="chooseCover"
                        />
                        <p class="text-xs text-muted-foreground">
                            JPEG, PNG, WebP, or GIF. Maximum 2 MB.
                        </p>
                    </FormField>
                    <label
                        v-if="vault?.cover_url"
                        class="flex items-center gap-2 text-sm"
                        ><input v-model="form.remove_cover" type="checkbox" />
                        Remove current cover</label
                    >
                    <progress
                        v-if="form.progress"
                        :value="form.progress.percentage"
                        max="100"
                        class="w-full"
                        aria-label="Upload progress"
                    />
                </template>
                <DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        :disabled="form.processing"
                        @click="changeOpen(false)"
                        >Cancel</Button
                    ><Button type="submit" :disabled="form.processing">{{
                        form.processing ? 'Saving…' : 'Save ' + kind
                    }}</Button></DialogFooter
                >
            </form>
        </DialogContent>
    </Dialog>
</template>
