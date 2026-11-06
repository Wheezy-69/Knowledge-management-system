<script setup lang="ts">
import { useColorMode, useMounted } from '@vueuse/core';
import { Button } from '@/components/ui/button';
const mode = useColorMode({ storageKey: 'knowledge-vault-theme' });
const selected = mode.store;
const mounted = useMounted();
function cycle() {
    selected.value =
        selected.value === 'auto'
            ? 'light'
            : selected.value === 'light'
              ? 'dark'
              : 'auto';
}
</script>
<template>
    <Button variant="outline" size="sm" @click="cycle"
        >Theme:
        {{
            !mounted || selected === 'auto'
                ? 'System'
                : selected === 'dark'
                  ? 'Dark'
                  : 'Light'
        }}</Button
    >
</template>
