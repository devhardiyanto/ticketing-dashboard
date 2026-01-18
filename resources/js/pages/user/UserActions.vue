<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Pencil, Trash } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import userRoute from '@/routes/user';

const props = defineProps<{
  user: any;
  onEdit?: (user: any) => void;
  canDelete?: boolean;
  canEdit?: boolean;
}>();

const form = useForm({});

const handleDelete = () => {
  if (!confirm('Are you sure you want to delete this user?')) return;

  form.delete(userRoute.destroy(props.user.id).url, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('User deleted successfully');
    },
    onError: () => {
      toast.error('Failed to delete user');
    }
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
      <DropdownMenuItem v-if="canEdit" @click="onEdit?.(user)">
        <Pencil class="mr-2 h-4 w-4" />
        Edit
      </DropdownMenuItem>
      <DropdownMenuItem
        v-if="canDelete"
        @click="handleDelete"
        class="text-destructive focus:text-destructive"
      >
        <Trash class="mr-2 h-4 w-4" />
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
