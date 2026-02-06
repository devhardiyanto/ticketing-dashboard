<script setup lang="ts">
import { cn } from '@/lib/utils';
import SimpleBar from 'simplebar-vue';
import 'simplebar-vue/dist/simplebar.min.css';

interface Props {
  class?: string;
  options?: any;
}

const props = withDefaults(defineProps<Props>(), {
  options: () => ({}),
});
</script>

<template>
  <SimpleBar :class="cn('h-full', props.class)" :options="props.options">
    <slot />
  </SimpleBar>
</template>

<style>
/* Custom styling to match shadcn/ui aesthetic (optional) */
.simplebar-scrollbar::before {
  @apply bg-muted-foreground/50;
}

.simplebar-track.simplebar-vertical {
  width: 10px;
}

.simplebar-scrollbar.simplebar-visible:before {
  opacity: 1;
}
</style>
