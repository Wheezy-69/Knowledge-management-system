<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeading from '@/components/PageHeading.vue';
import FormField from '@/components/FormField.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { store, exportMethod as exportBackup } from '@/routes/backups';
const form = useForm({ backup: null as File | null, confirm: false });
const fileKey = ref(0);
const page = usePage<{ errors: Record<string, string> }>();
function choose(event: Event) {
    form.backup = (event.target as HTMLInputElement).files?.[0] ?? null;
}
function submit() {
    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            fileKey.value++;
        },
    });
}
</script>
<template>
    <Head title="Backup and restore" /><AppLayout title="Backup and restore"
        ><PageHeading
            title="Backup and restore"
            description="Portable, versioned JSON containing knowledge, relationships, tags, blocks, and cover images."
        />
        <p
            v-if="page.props.errors.backup"
            role="alert"
            class="text-sm text-destructive"
        >
            {{ page.props.errors.backup }}
        </p>
        <Card
            ><CardHeader
                ><CardTitle>Export your workspace</CardTitle></CardHeader
            ><CardContent class="flex flex-col gap-4"
                ><p class="text-sm text-muted-foreground">
                    The download includes checksums and embedded attachments.
                    Keep it somewhere private; it is not encrypted. Maximum
                    portable archive size: 20 MB.
                </p>
                <Button as-child class="self-start"
                    ><a :href="exportBackup.url()">Download backup</a></Button
                ></CardContent
            ></Card
        >
        <Card
            ><CardHeader
                ><CardTitle>Restore as new copies</CardTitle></CardHeader
            ><CardContent
                ><form class="flex flex-col gap-5" @submit.prevent="submit">
                    <p class="text-sm text-muted-foreground">
                        Existing records are never replaced or deleted.
                        Restoring the same archive again creates another copy.
                        Reusable tags with the same normalized name are shared.
                    </p>
                    <FormField
                        id="backup-file"
                        :key="fileKey"
                        label="Backup file"
                        :error="form.errors.backup"
                        ><Input
                            id="backup-file"
                            type="file"
                            accept=".json,application/json"
                            required
                            @change="choose"
                    /></FormField>
                    <label class="flex items-start gap-2 text-sm"
                        ><input v-model="form.confirm" type="checkbox" />I
                        understand this imports new copies, including
                        attachments.</label
                    >
                    <p
                        v-if="form.errors.confirm"
                        role="alert"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.confirm }}
                    </p>
                    <progress
                        v-if="form.progress"
                        :value="form.progress.percentage"
                        max="100"
                        aria-label="Upload progress"
                    />
                    <Button
                        class="self-start"
                        :disabled="
                            form.processing || !form.backup || !form.confirm
                        "
                        >{{
                            form.processing
                                ? 'Validating and restoring…'
                                : 'Validate and restore'
                        }}</Button
                    >
                </form></CardContent
            ></Card
        >
    </AppLayout>
</template>
