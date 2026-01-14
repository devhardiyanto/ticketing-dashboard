<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import {
	Field,
	FieldContent,
	FieldError,
	FieldLabel,
} from '@/components/ui/field';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';
import userRoute from '@/routes/user';
import { Loader2, Lock } from 'lucide-vue-next';
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

const props = defineProps<{
	initialData?: any | null;
	organizations?: { id: string; name: string }[];
	roles?: { id: number; name: string; display_name: string; permissions: string[] }[];
	availablePermissions?: { id: number; name: string }[];

	isLockedOrganization?: boolean;
	lockedOrganizationId?: string;
	lockedOrganizationName?: string;
}>();

const emit = defineEmits(['success']);

const isEditMode = computed(() => !!props.initialData);

const form = useForm({
	name: props.initialData?.name || '',
	email: props.initialData?.email || '',
	password: '',
	organization_id: props.initialData?.organization_id || props.lockedOrganizationId || '__none__',
	role_id: props.initialData?.role_id || null,
	phone_number: props.initialData?.phone_number || '',
	status: props.initialData?.status || 'active',
	permissions: props.initialData?.permissions?.map((p: any) => p.name) || [],
});

const showOrgWarning = ref(false);

const inheritedPermissions = computed(() => {
	if (!form.role_id) return [];
	const role = props.roles?.find(r => r.id === form.role_id);
	return role ? role.permissions : [];
});

const isPermissionInherited = (permissionName: string) => {
	return inheritedPermissions.value.includes(permissionName);
};

const isPermissionChecked = (permissionName: string) => {
	return isPermissionInherited(permissionName) || form.permissions.includes(permissionName);
};

const togglePermission = (permissionName: string, checked: boolean) => {
	if (isPermissionInherited(permissionName)) return; // Cannot toggle inherited

	if (checked) {
		if (!form.permissions.includes(permissionName)) {
			form.permissions.push(permissionName);
		}
	} else {
		form.permissions = form.permissions.filter((p: string) => p !== permissionName);
	}
};

const submit = () => {
	if (!isEditMode.value && form.organization_id && form.organization_id !== '__none__' && !props.isLockedOrganization) {
		showOrgWarning.value = true;
		return;
	}
	executeSubmit();
};

const confirmCreate = () => {
	showOrgWarning.value = false;
	executeSubmit();
};

const groupedPermissions = computed(() => {
	if (!props.availablePermissions) return {};

	const groups: Record<string, typeof props.availablePermissions> = {};

	props.availablePermissions.forEach(permission => {
		let group = 'System'; // Default group

		if (permission.name.includes('event')) group = 'Event';
		else if (permission.name.includes('ticket')) group = 'Ticket';
		else if (permission.name.includes('order')) group = 'Order';
		else if (permission.name.includes('user')) group = 'User';
		else if (permission.name.includes('banner')) group = 'Content';
		else if (permission.name.includes('dashboard')) group = 'System';

		if (!groups[group]) {
			groups[group] = [];
		}
		groups[group].push(permission);
	});

	// Sort keys for consistent ordering
	const orderedKeys = ['Event', 'Ticket', 'Order', 'User', 'Content', 'System'];
	const sortedGroups: Record<string, typeof props.availablePermissions> = {};

	orderedKeys.forEach(key => {
		if (groups[key]) sortedGroups[key] = groups[key];
	});

	// Add any remaining keys
	Object.keys(groups).forEach(key => {
		if (!orderedKeys.includes(key)) sortedGroups[key] = groups[key];
	});

	return sortedGroups;
});

const formatPermissionLabel = (name: string) => {
	// manage-users -> Manage Users
	return name
		.split('-')
		.map(word => word.charAt(0).toUpperCase() + word.slice(1))
		.join(' ');
};

const formatRoleName = (name: string) => {
	// super_admin -> Super Admin
	return name
		.split('_')
		.map(word => word.charAt(0).toUpperCase() + word.slice(1))
		.join(' ');
};

const shouldShowOrganization = computed(() => {
	if (!form.role_id) return false;
	const role = props.roles?.find(r => r.id === form.role_id);
	return role?.name === 'organization';
});

const executeSubmit = () => {
	const submitData = {
		...form.data(),
		organization_id: form.organization_id === '__none__' ? null : form.organization_id,
	};

	if (isEditMode.value) {
		form.transform(() => submitData).put(userRoute.update(props.initialData.id).url, {
			onSuccess: () => {
				toast.success('User updated successfully');
				emit('success');
			},
			onError: () => toast.error('Failed to update user'),
		});
	} else {
		form.transform(() => submitData).post(userRoute.store().url, {
			onSuccess: () => {
				toast.success('User created successfully');
				emit('success');
			},
			onError: () => toast.error('Failed to create user'),
		});
	}
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4 pt-4">
    <Tabs default-value="profile" class="w-full">
      <TabsList class="grid w-full grid-cols-2">
        <TabsTrigger value="profile">Profile</TabsTrigger>
        <TabsTrigger value="permissions">Permissions</TabsTrigger>
      </TabsList>

      <TabsContent value="profile" class="space-y-4 pt-4">
            <!-- Name -->
            <Field name="name" :invalid="!!form.errors.name">
            <FieldLabel>Name</FieldLabel>
            <FieldContent>
                <Input v-model="form.name" placeholder="John Doe" />
            </FieldContent>
            <FieldError>{{ form.errors.name }}</FieldError>
            </Field>

            <!-- Email -->
            <Field name="email" :invalid="!!form.errors.email">
            <FieldLabel>Email</FieldLabel>
            <FieldContent>
                <Input v-model="form.email" type="email" placeholder="john@example.com" />
            </FieldContent>
            <FieldError>{{ form.errors.email }}</FieldError>
            </Field>

            <!-- Password (Edit Mode Only) -->
            <Field v-if="isEditMode" name="password" :invalid="!!form.errors.password">
            <FieldLabel>New Password (Optional)</FieldLabel>
            <FieldContent>
                <Input v-model="form.password" type="password" placeholder="********" />
            </FieldContent>
            <FieldError>{{ form.errors.password }}</FieldError>
            </Field>

            <!-- Role -->
            <Field name="role_id" :invalid="!!form.errors.role_id">
            <FieldLabel>Role</FieldLabel>
            <FieldContent>
                <Select v-model="form.role_id">
                <SelectTrigger>
                    <SelectValue placeholder="Select Role" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="role in roles" :key="role.id" :value="role.id">
                    {{ formatRoleName(role.name) }}
                    </SelectItem>
                </SelectContent>
                </Select>
            </FieldContent>
            <FieldError>{{ form.errors.role_id }}</FieldError>
            </Field>

            <!-- Organization -->
            <Field v-if="shouldShowOrganization" name="organization_id" :invalid="!!form.errors.organization_id">
            <FieldLabel class="flex items-center gap-2">
                Organization
                <Lock v-if="isEditMode || isLockedOrganization" class="w-3 h-3 text-muted-foreground" />
            </FieldLabel>
            <FieldContent>
                <Select v-model="form.organization_id" :disabled="isEditMode || isLockedOrganization">
                <SelectTrigger>
                    <SelectValue placeholder="Select Organization" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="__none__">None (System Admin)</SelectItem>
                    <SelectItem v-for="org in organizations" :key="org.id" :value="org.id">
                    {{ org.name }}
                    </SelectItem>
                </SelectContent>
                </Select>
            </FieldContent>
            <FieldError>{{ form.errors.organization_id }}</FieldError>
            <p v-if="isEditMode" class="text-xs text-muted-foreground mt-1">
                Organization cannot be changed after assignment.
            </p>
            </Field>

            <!-- Phone Number -->
            <Field name="phone_number" :invalid="!!form.errors.phone_number">
            <FieldLabel>Phone Number</FieldLabel>
            <FieldContent>
                <Input v-model="form.phone_number" placeholder="+123..." />
            </FieldContent>
            <FieldError>{{ form.errors.phone_number }}</FieldError>
            </Field>

            <!-- Status -->
            <Field name="status" :invalid="!!form.errors.status">
            <FieldLabel>Status</FieldLabel>
            <FieldContent>
                <Select v-model="form.status">
                <SelectTrigger>
                    <SelectValue placeholder="Select Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="inactive">Inactive</SelectItem>
                </SelectContent>
                </Select>
            </FieldContent>
            <FieldError>{{ form.errors.status }}</FieldError>
            </Field>
      </TabsContent>

      <TabsContent value="permissions" class="space-y-6 pt-4">
          <div v-if="!availablePermissions || availablePermissions.length === 0" class="text-muted-foreground text-sm">
              No permissions available.
          </div>
          <div v-else class="space-y-6">
              <div v-for="(group, groupName) in groupedPermissions" :key="groupName" class="border rounded-lg p-4 bg-card">
                  <h4 class="text-sm font-medium mb-3 text-foreground capitalize">{{ groupName }} Management</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div v-for="permission in group" :key="permission.id" class="flex items-center gap-2">
                          <Checkbox
                                :id="`perm-${permission.id}`"
                                :checked="isPermissionChecked(permission.name)"
                                :disabled="isPermissionInherited(permission.name)"
                                @update:checked="(checked: boolean) => togglePermission(permission.name, checked)"
                          />
                          <Label :for="`perm-${permission.id}`" class="text-sm cursor-pointer select-none" :class="{ 'text-muted-foreground': isPermissionInherited(permission.name) }">
                              {{ formatPermissionLabel(permission.name) }}
                              <span v-if="isPermissionInherited(permission.name)" class="text-xs ml-1 bg-muted px-1.5 py-0.5 rounded text-muted-foreground font-medium">(Inherited)</span>
                          </Label>
                      </div>
                  </div>
              </div>
          </div>
          <p class="text-xs text-muted-foreground mt-4">
              Permissions inherit from the selected Role. Inherited permissions cannot be manually unchecked.
          </p>
      </TabsContent>
    </Tabs>

    <div class="flex justify-end pt-4">
      <Button type="submit" :disabled="form.processing">
        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
        {{ isEditMode ? 'Update User' : 'Create User' }}
      </Button>
    </div>
  </form>

  <AlertDialog v-model:open="showOrgWarning">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>⚠️ Assigning Organization</AlertDialogTitle>
        <AlertDialogDescription>
          You are about to assign this user to an organization.
          <br/><br/>
          <b>Important:</b> Once assigned, the organization <b>cannot be changed</b>.
          Are you sure you want to proceed?
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="showOrgWarning = false">Cancel</AlertDialogCancel>
        <AlertDialogAction @click="confirmCreate">Yes, Create User</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
