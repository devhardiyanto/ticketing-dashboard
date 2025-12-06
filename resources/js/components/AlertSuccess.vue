<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle } from 'lucide-vue-next';

interface Props {
  title?: string;
}

withDefaults(defineProps<Props>(), {
  title: 'Operation successful.',
});
</script>

<template>
    <Alert class="border-green-500 text-green-600 [&>svg]:text-green-600">
        <CheckCircle class="size-4" />
        <AlertTitle>{{ title }}</AlertTitle>
        <AlertDescription>
            <slot />
        </AlertDescription>
    </Alert>
</template>
