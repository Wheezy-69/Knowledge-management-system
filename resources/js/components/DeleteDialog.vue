<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
withDefaults(
    defineProps<{
        action: { url: string; method: 'delete' };
        title: string;
        description: string;
        label?: string;
        confirmLabel?: string;
        irreversible?: boolean;
    }>(),
    { label: 'Delete', confirmLabel: 'Delete permanently', irreversible: true },
);
const open = ref(false);
const form = useForm({});
function changeOpen(value: boolean) {
    if (!form.processing) open.value = value;
}
function submit(url: string) {
    form.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>
<template>
    <Dialog :open="open" @update:open="changeOpen">
        <DialogTrigger as-child
            ><Button variant="outline" size="sm">{{
                label
            }}</Button></DialogTrigger
        >
        <DialogContent>
            <DialogHeader
                ><DialogTitle>{{ title }}</DialogTitle
                ><DialogDescription
                    >{{ description }}
                    {{
                        irreversible ? 'This cannot be undone.' : ''
                    }}</DialogDescription
                ></DialogHeader
            >
            <DialogFooter>
                <Button
                    variant="outline"
                    :disabled="form.processing"
                    @click="changeOpen(false)"
                    >Cancel</Button
                >
                <Button
                    variant="destructive"
                    :disabled="form.processing"
                    @click="submit(action.url)"
                    >{{ form.processing ? 'Working…' : confirmLabel }}</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
