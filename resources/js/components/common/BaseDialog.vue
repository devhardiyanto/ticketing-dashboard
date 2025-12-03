<script setup lang="ts">
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';

defineProps<{
	open: boolean;
	title: string;
	description?: string;
	maxWidth?: string;
}>();

const emit = defineEmits(['update:open']);
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent :class="maxWidth || 'sm:max-w-2xl'">
      <DialogHeader class="mx-4 mt-4">
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>
          {{ description || ' ' }}
        </DialogDescription>
      </DialogHeader>

      <!-- Dynamic Content -->
      <ScrollArea class="max-h-[70vh]">
        <div class="py-4 mx-4">
          <slot />
        </div>
      </ScrollArea>

      <DialogFooter v-if="$slots.footer">
        <slot name="footer" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
