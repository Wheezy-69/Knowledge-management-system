<script setup lang="ts">
import { Label } from '@/components/ui/label';
defineProps<{ id: string; label: string; error?: string }>();
</script>
<template>
    <div class="flex flex-col gap-2">
        <Label :for="id">{{ label }}</Label>
        <slot
            :invalid="!!error"
            :describedby="error ? id + '-error' : undefined"
        />
        <p
            v-if="error"
            :id="id + '-error'"
            role="alert"
            class="text-sm text-destructive"
        >
            {{ error }}
        </p>
    </div>
</template>
