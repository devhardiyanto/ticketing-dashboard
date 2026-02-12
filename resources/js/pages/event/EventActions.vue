<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { MoreHorizontal, Pencil, Trash, Eye, Ticket, Tickets } from 'lucide-vue-next';
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import type { Event } from '@/types/dashboard/event';
import BaseDialog from '@/components/common/BaseDialog.vue';
import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { destroy } from '@/actions/App/Http/Controllers/EventController';
import { toast } from 'vue-sonner'

const props = defineProps<{
	event: Event;
	canEdit?: boolean;
	canDelete?: boolean;
}>();

const emit = defineEmits(['edit', 'success']);

const isViewOpen = ref(false);
const isDeleteOpen = ref(false);
const isTicketTypeOpen = ref(false);

import items from '@/routes/items';

const handleChildSelect = (childId: string) => {
	router.visit(items.index({ query: { event_id: childId } }).url);
};

const form = useForm({
	id: props.event.id || '',
});

const handleDelete = () => {
	form.submit(destroy(props.event.id), {
		preserveScroll: true,
		onSuccess: () => {
			emit('success');
			isDeleteOpen.value = false;
			toast.success('Event deleted successfully');
		},
	})
};
</script>

<template>
	<div class="flex items-center justify-end">
		<DropdownMenu>
			<DropdownMenuTrigger as-child>
				<Button variant="ghost" size="icon" class="h-8 w-8 p-0">
					<span class="sr-only">Open menu</span>
					<MoreHorizontal class="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuItem class="hover:cursor-pointer" v-if="canEdit" @click="$emit('edit', event)">
          <Pencil class="mr-2 h-4 w-4" />
          Edit
        </DropdownMenuItem>
        <DropdownMenuItem class="hover:cursor-pointer" @click="isViewOpen = true">
          <Eye class="mr-2 h-4 w-4" />
          View Details
        </DropdownMenuItem>
        <DropdownMenuItem as-child class="hover:cursor-pointer" v-if="event.is_parent">
					<Link :href="`/event/${event.id}`">
						<Tickets class="mr-2 h-4 w-4" />
						Manage Child Events
					</Link>
        </DropdownMenuItem>
        <DropdownMenuItem as-child class="hover:cursor-pointer" v-if="!event.is_parent && canEdit">
					<Link :href="`/items?event_id=${event.id}`">
						<Ticket class="mr-2 h-4 w-4" />
						Ticket Settings
					</Link>
        </DropdownMenuItem>
        <DropdownMenuItem v-if="canDelete" @click="isDeleteOpen = true" class="hover:cursor-pointer text-red-600 focus:text-red-600">
          <Trash class="mr-2 h-4 w-4" />
          Delete
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
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
        <span class="font-bold">Organization:</span>
        <span class="col-span-3">{{ event.organization.name }}</span>
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

  <!-- Ticket Type Selection Dialog (Parent Event) -->
  <BaseDialog v-model:open="isTicketTypeOpen" title="Select Event">
    <div class="grid gap-4 py-4">
      <div v-if="event.child_events && event.child_events.length > 0" class="flex flex-col gap-2">
         <p class="text-sm text-gray-500 mb-2">This is a parent event. Please select a sub-event to manage ticket types:</p>
         <Button
            v-for="child in event.child_events"
            :key="child.id"
            variant="outline"
            class="justify-start"
            @click="handleChildSelect(child.id)"
         >
           {{ child.name }}
         </Button>
      </div>
      <div v-else class="text-center py-4 text-gray-500">
        No sub-events found for this parent event.
      </div>
    </div>
  </BaseDialog>

  <!-- Delete Alert -->
  <AlertDialog :open="isDeleteOpen" @update:open="isDeleteOpen = $event">
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
</template>
