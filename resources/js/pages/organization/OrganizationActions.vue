<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Pencil, Trash } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import organizationRoute from '@/routes/organization';

interface Organization {
  id: string;
  name: string;
  // ... other fields
}

const props = defineProps<{
  organization: Organization;
  onEdit: (org: Organization) => void;
  onSuccess?: () => void; // Callback to refresh data
  canEdit?: boolean;
  canDelete?: boolean;
}>();

const handleDelete = () => {
  if (!confirm('Are you sure you want to delete this organization?')) return;

  router.delete(organizationRoute.destroy(props.organization.id).url, {
    onSuccess: () => {
      toast.success('Organization deleted successfully');
      props.onSuccess?.();
    },
    onError: () => {
      toast.error('Failed to delete organization');
    },
  });
};
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" class="h-8 w-8 p-0">
        <span class="sr-only">Open menu</span>
        <MoreHorizontal class="h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuLabel>Actions</DropdownMenuLabel>
      <DropdownMenuItem @click="() => navigator.clipboard.writeText(organization.id)">
        Copy ID
      </DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem @click="() => onEdit(organization)" :disabled="!canEdit">
        <Pencil class="mr-2 h-4 w-4" />
        Edit
      </DropdownMenuItem>
      <DropdownMenuItem @click="handleDelete" class="text-red-600 focus:text-red-600" :disabled="!canDelete">
        <Trash class="mr-2 h-4 w-4" />
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
