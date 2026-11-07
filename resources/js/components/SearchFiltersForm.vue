<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch, computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import type { SearchFilters, FilterOptions } from '@/types/knowledge';
const props = defineProps<{
    filters: SearchFilters;
    options: FilterOptions;
    action: string;
    scoped?: boolean;
    graph?: boolean;
}>();
const form = useForm({ ...props.filters });
watch(
    () => props.filters,
    (value) => {
        form.defaults({ ...value });
        form.reset();
    },
    { deep: true },
);
const sections = computed(() =>
    props.options.sections.filter(
        (s) => !form.vault_id || s.vault_id === Number(form.vault_id),
    ),
);
function submit() {
    form.get(props.action, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
function clear() {
    form.search = '';
    form.language = '';
    form.vault_id = '';
    form.section_id = '';
    form.tag_ids = [];
    submit();
}
</script>
<template>
    <form
        class="flex flex-col gap-3 rounded-xl border p-4"
        role="search"
        @submit.prevent="submit"
    >
        <label class="flex flex-col gap-2 text-sm"
            >Search<Input
                v-model="form.search"
                type="search"
                maxlength="200"
                :placeholder="
                    graph
                        ? 'Filter problem titles…'
                        : 'Titles, explanations, solutions, attempts, and blocks…'
                "
        /></label>
        <div class="grid gap-3 sm:grid-cols-3">
            <label v-if="!scoped" class="flex flex-col gap-2 text-sm"
                >Vault<select
                    v-model="form.vault_id"
                    class="h-9 rounded-md border bg-background px-3"
                    @change="form.section_id = ''"
                >
                    <option value="">All vaults</option>
                    <option
                        v-for="v in options.vaults"
                        :key="v.id"
                        :value="v.id"
                    >
                        {{ v.name }}
                    </option>
                </select></label
            >
            <label v-if="!scoped" class="flex flex-col gap-2 text-sm"
                >Section<select
                    v-model="form.section_id"
                    class="h-9 rounded-md border bg-background px-3"
                >
                    <option value="">All sections</option>
                    <option v-for="s in sections" :key="s.id" :value="s.id">
                        {{ s.name }}
                    </option>
                </select></label
            >
            <label class="flex flex-col gap-2 text-sm"
                >Language<select
                    v-model="form.language"
                    class="h-9 rounded-md border bg-background px-3"
                >
                    <option value="">All languages</option>
                    <option
                        v-for="language in options.languages"
                        :key="language"
                        :value="language"
                    >
                        {{ language }}
                    </option>
                </select></label
            >
        </div>
        <fieldset v-if="options.tags.length" class="flex flex-col gap-2">
            <legend class="text-sm">Tags (matches all selected)</legend>
            <div class="flex max-h-32 flex-wrap gap-3 overflow-auto py-2">
                <label
                    v-for="tag in options.tags"
                    :key="tag.id"
                    class="flex items-center gap-2 text-sm"
                    ><input
                        v-model="form.tag_ids"
                        type="checkbox"
                        :value="tag.id"
                    />{{ tag.name }}</label
                >
            </div>
        </fieldset>
        <p
            v-for="(message, key) in form.errors"
            :key="key"
            role="alert"
            class="text-sm text-destructive"
        >
            {{ message }}
        </p>
        <div class="flex gap-2">
            <Button :disabled="form.processing">{{
                form.processing ? 'Searching…' : 'Search'
            }}</Button
            ><Button
                type="button"
                variant="outline"
                :disabled="form.processing"
                @click="clear"
                >Clear filters</Button
            >
        </div>
    </form>
</template>
