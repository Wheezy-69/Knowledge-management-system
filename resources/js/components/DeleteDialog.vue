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
defineProps<{
    action: { url: string; method: 'delete' };
    title: string;
    description: string;
}>();
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
            ><Button variant="outline" size="sm">Delete</Button></DialogTrigger
        >
        <DialogContent>
            <DialogHeader
                ><DialogTitle>{{ title }}</DialogTitle
                ><DialogDescription
                    >{{ description }} This cannot be undone.</DialogDescription
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
                    >{{
                        form.processing ? 'Deleting…' : 'Delete permanently'
                    }}</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
