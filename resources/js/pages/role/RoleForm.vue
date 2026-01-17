<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
	DialogClose,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import type { Role, CandidateUser } from '@/types/dashboard';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import roleRoute from '@/routes/role';
import { toast } from 'vue-sonner';

const props = defineProps<{
	role: Role | null;
	users: CandidateUser[];
}>();

const emit = defineEmits<{
	(e: 'success'): void;
}>();

const searchQuery = ref('');

// Form state
const form = useForm({
	user_ids: [] as number[],
});

// Initialize form when role changes
watch(() => props.role, (newRole) => {
	if (newRole && newRole.users) {
		form.user_ids = newRole.users.map(u => u.id);
	} else {
		form.user_ids = [];
	}
}, { immediate: true });

// Filter users based on search
const filteredUsers = computed(() => {
	if (!searchQuery.value) return props.users;
	const lowerQuery = searchQuery.value.toLowerCase();
	return props.users.filter(user =>
		user.name.toLowerCase().includes(lowerQuery) ||
		user.email.toLowerCase().includes(lowerQuery)
	);
});

// Helper to handle checkbox changes
const toggleUser = (userId: number, checked: boolean) => {
	if (checked) {
		if (!form.user_ids.includes(userId)) {
			form.user_ids.push(userId);
		}
	} else {
		form.user_ids = form.user_ids.filter(id => id !== userId);
	}
};

const onSubmit = () => {
	if (!props.role) return;

	form.put(roleRoute.update(props.role.id).url, {
		onSuccess: () => {
			toast({
				title: 'Success',
				description: 'Role assignments updated successfully.',
			});
			emit('success');
		},
		onError: (errors) => {
			toast({
				title: 'Error',
				description: 'Failed to update role assignments.',
				variant: 'destructive',
			});
			console.error(errors);
		}
	});
};
</script>

<template>
  <DialogContent class="sm:max-w-[500px]">
    <DialogHeader>
      <DialogTitle>Edit Role: {{ role?.label || role?.name }}</DialogTitle>
      <DialogDescription>
        Assign users to this role. Search to find specific users.
      </DialogDescription>
    </DialogHeader>

    <div class="grid gap-4 py-4" v-if="role">
      <!-- Role Name (Read Only) -->
      <div class="grid grid-cols-4 items-center gap-4">
        <Label for="name" class="text-right">Name</Label>
        <Input id="name" :model-value="role.name" readonly disabled class="col-span-3 bg-muted" />
      </div>

      <Separator />

      <!-- User Search & Selection -->
      <div class="space-y-2">
        <h4 class="text-sm font-medium leading-none">Assign Users</h4>
        <Input
            v-model="searchQuery"
            placeholder="Search users by name or email..."
            class="h-8"
        />

        <div class="border rounded-md">
            <ScrollArea class="h-[200px] w-full p-4">
                <div v-if="filteredUsers.length === 0" class="text-sm text-muted-foreground text-center py-4">
                    No users found.
                </div>
                <div v-else class="space-y-3">
                    <div v-for="user in filteredUsers" :key="user.id" class="flex items-center space-x-2">
                        <Checkbox
													:id="`user-${user.id}`"
													:model-value="form.user_ids.includes(user.id)"
													@update:model-value="(checked) => toggleUser(user.id, checked as boolean)"
                        />
                        <label
                            :for="`user-${user.id}`"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                        >
                            {{ user.name }}
                            <span class="text-xs text-muted-foreground ml-1">({{ user.email }})</span>
                        </label>
                    </div>
                </div>
            </ScrollArea>
        </div>
        <div class="text-xs text-muted-foreground text-right">
            Selected: {{ form.user_ids.length }} users
        </div>
      </div>
    </div>

    <DialogFooter>
      <DialogClose as-child>
        <Button variant="outline" type="button">Cancel</Button>
      </DialogClose>
      <Button type="submit" @click="onSubmit" :disabled="form.processing">
        {{ form.processing ? 'Saving...' : 'Save Changes' }}
      </Button>
    </DialogFooter>
  </DialogContent>
</template>
