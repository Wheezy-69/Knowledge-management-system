<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import FormField from '@/components/FormField.vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
    DialogFooter,
} from '@/components/ui/dialog';
import { store, update } from '@/routes/tags';
const props = defineProps<{ tag?: { id: number; name: string } }>();
const open = ref(false);
const form = useForm({ name: '', key: '' });
watch(open, (value) => {
    if (value) {
        form.defaults({ name: props.tag?.name ?? '', key: '' });
        form.reset();
        form.clearErrors();
    }
});
function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };
    if (props.tag) form.put(update.url(props.tag.id), options);
    else form.post(store.url(), options);
}
</script>
<template>
    <Dialog v-model:open="open"
        ><DialogTrigger as-child
            ><Button
                :variant="tag ? 'outline' : 'default'"
                :size="tag ? 'sm' : 'default'"
                >{{ tag ? 'Rename' : 'New tag' }}</Button
            ></DialogTrigger
        >
        <DialogContent
            ><DialogHeader
                ><DialogTitle>{{
                    tag ? 'Rename tag' : 'Create tag'
                }}</DialogTitle
                ><DialogDescription
                    >Names are reused across problems. Renaming updates every
                    assignment.</DialogDescription
                ></DialogHeader
            >
            <form class="flex flex-col gap-5" @submit.prevent="submit">
                <FormField
                    id="tag-name"
                    label="Name"
                    :error="form.errors.name || form.errors.key"
                    ><Input
                        id="tag-name"
                        v-model="form.name"
                        required
                        maxlength="40" /></FormField
                ><DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        :disabled="form.processing"
                        @click="open = false"
                        >Cancel</Button
                    ><Button :disabled="form.processing">{{
                        form.processing ? 'Saving…' : 'Save tag'
                    }}</Button></DialogFooter
                >
            </form>
        </DialogContent>
    </Dialog>
</template>
