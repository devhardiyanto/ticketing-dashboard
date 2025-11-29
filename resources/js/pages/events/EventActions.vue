<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Pencil, Trash, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Event } from '@/types/event';
import BaseDialog from '@/components/common/BaseDialog.vue';
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const props = defineProps<{
  event: Event;
}>();

defineEmits(['edit']);

const isViewOpen = ref(false);
const isDeleteOpen = ref(false);

const handleDelete = () => {
  router.delete(route('events.destroy', props.event.id), {
    preserveScroll: true,
    onSuccess: () => {
      isDeleteOpen.value = false;
    },
  });
};
</script>

<template>
  <div class="flex items-center gap-2">
    <Button variant="ghost" size="icon" @click="$emit('edit', event)">
      <Pencil class="h-4 w-4" />
    </Button>
    <Button variant="ghost" size="icon" @click="isViewOpen = true">
      <Eye class="h-4 w-4" />
    </Button>

    <AlertDialog @update:open="isDeleteOpen = $event">
      <AlertDialogTrigger as-child>
        <Button variant="ghost" size="icon" class="text-red-500 hover:text-red-600 hover:bg-red-50">
          <Trash class="h-4 w-4" />
        </Button>
      </AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete the event
            <strong>{{ event.name }}</strong> and remove it from our servers.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction class="bg-red-600 hover:bg-red-700 text-white" @click="handleDelete">
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>

  <!-- View Dialog -->
  <BaseDialog v-model:open="isViewOpen" title="Event Details">
    <div class="grid gap-4 py-4">
      <div class="grid grid-cols-4 items-center gap-4">
        <span class="font-bold">Name:</span>
        <span class="col-span-3">{{ event.name }}</span>
      </div>
      <div class="grid grid-cols-4 items-center gap-4">
        <span class="font-bold">Description:</span>
        <span class="col-span-3">{{ event.description || '-' }}</span>
      </div>
      <div class="grid grid-cols-4 items-center gap-4">
        <span class="font-bold">Location:</span>
        <span class="col-span-3">{{ event.location }}</span>
      </div>
      <div class="grid grid-cols-4 items-center gap-4">
        <span class="font-bold">Start Date:</span>
        <span class="col-span-3">{{ new Date(event.start_date).toLocaleString() }}</span>
      </div>
      <div class="grid grid-cols-4 items-center gap-4">
        <span class="font-bold">End Date:</span>
        <span class="col-span-3">{{ new Date(event.end_date).toLocaleString() }}</span>
      </div>
    </div>
  </BaseDialog>
</template>
