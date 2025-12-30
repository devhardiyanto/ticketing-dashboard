<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { MoreHorizontal, Pencil, Trash, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { TicketType } from '@/types/dashboard';
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
import { destroy } from '@/actions/App/Http/Controllers/TicketTypeController';
import { toast } from 'vue-sonner';

const props = defineProps<{
	ticket_type: TicketType;
}>();

const emit = defineEmits(['edit', 'success']);

const isViewOpen = ref(false);
const isDeleteOpen = ref(false);

const form = useForm({
	id: props.ticket_type.id,
});

const handleDelete = () => {
	form.submit(destroy(props.ticket_type.id), {
		preserveScroll: true,
		onSuccess: () => {
			emit('success');
			isDeleteOpen.value = false;
			toast.success('Ticket type deleted successfully');
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
        <DropdownMenuItem class="hover:cursor-pointer" @click="$emit('edit', ticket_type)">
          <Pencil class="mr-2 h-4 w-4" />
          Edit
        </DropdownMenuItem>
        <DropdownMenuItem class="hover:cursor-pointer" @click="isViewOpen = true">
          <Eye class="mr-2 h-4 w-4" />
          View Details
        </DropdownMenuItem>
        <DropdownMenuItem @click="isDeleteOpen = true" class="hover:cursor-pointer text-red-600 focus:text-red-600 focus:bg-red-50">
          <Trash class="mr-2 h-4 w-4" />
          Delete
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>

  <!-- View Dialog -->
  <BaseDialog v-model:open="isViewOpen" title="TicketType Details">
    <div class="grid gap-4 py-4">
      <div class="grid grid-cols-4 items-center gap-4">
        <span class="font-bold">Name:</span>
        <span class="col-span-3">{{ ticket_type.name }}</span>
      </div>
      <div class="grid grid-cols-4 items-center gap-4">
        <span class="font-bold">Description:</span>
        <span class="col-span-3">{{ ticket_type.description || '-' }}</span>
      </div>
    </div>
  </BaseDialog>

  <!-- Delete Alert -->
  <AlertDialog :open="isDeleteOpen" @update:open="isDeleteOpen = $event">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
        <AlertDialogDescription>
          This action cannot be undone. This will permanently delete the ticket_type
          <strong>{{ ticket_type.name }}</strong> and remove it from our servers.
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
