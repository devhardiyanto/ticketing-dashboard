<script setup lang="ts">
import { ROLES } from '@/types/roles';
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
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
	Field,
	FieldContent,
	FieldError,
	FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import userRoute from '@/routes/user';
import { useForm } from '@inertiajs/vue3';
import { Loader2, Lock } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { formatRoleName } from '@/lib/utils-general';
import { z } from 'zod';

const props = defineProps<{
	initialData?: any | null;
	organizations?: { id: string; name: string }[];
	roles?: {
		id: number;
		name: string;
		display_name: string;
		permissions: string[];
	}[];
	// Updated type: Object grouped by string keys
	availablePermissions?: Record<string, { id: number; name: string; label: string }[]>;

	isLockedOrganization?: boolean;
	lockedOrganizationId?: string;
	lockedOrganizationName?: string;
	currentRole?: string;
}>();

const emit = defineEmits(['success']);

const userSchema = z.object({
	name: z.string().min(1, 'Name is required'),
	email: z.string().email('Invalid email address'),
	role_id: z.coerce
		.number()
		.min(1, 'Role is required'),
	organization_id: z.string().nullable().optional(),
	phone_number: z.string().optional(),
	status: z.string(),
});

const isEditMode = computed(() => !!props.initialData);

const form = useForm({
	name: props.initialData?.name || '',
	email: props.initialData?.email || '',
	password: '',
	organization_id:
		props.initialData?.organization_id ||
		props.lockedOrganizationId ||
		null,
	role_id: props.initialData?.role_id || props.initialData?.roles?.[0]?.id || null,
	phone_number: props.initialData?.phone_number || '',
	status: props.initialData?.status || 'active',
	permissions: props.initialData?.permissions?.map((p: any) => p.name) || [],
});

const selectedPermissions = ref<string[]>(
	props.initialData?.permissions?.map((p: any) => p.name) || [],
);

const showOrgWarning = ref(false);

const inheritedPermissions = computed(() => {
	if (!form.role_id) return [];
	// Use loose equality (==) to handle string/number mismatch for role_id
	const role = props.roles?.find((r) => r.id == form.role_id);
	return role ? role.permissions : [];
});

const isPermissionInherited = (permissionName: string) => {
	return inheritedPermissions.value.includes(permissionName);
};

const isPermissionChecked = (permissionName: string) => {
	return (
		isPermissionInherited(permissionName) ||
		selectedPermissions.value.includes(permissionName)
	);
};

const togglePermission = (permissionName: string, checked: boolean) => {
	if (isPermissionInherited(permissionName)) return;

	if (checked) {
		if (!selectedPermissions.value.includes(permissionName)) {
			selectedPermissions.value.push(permissionName);
		}
	} else {
		selectedPermissions.value = selectedPermissions.value.filter(
			(p: string) => p !== permissionName,
		);
	}
};

const filteredPermissions = computed(() => {
	if (!props.availablePermissions) return {};

	const groups = props.availablePermissions;
	const filtered: Record<string, { id: number; name: string; label: string }[]> = {};

	// Creator Restrictions (Logged in user restrictions)
	const isOrgAdminCreator = props.currentRole === ROLES.ORG_ADMIN;

	Object.keys(groups).forEach((groupName) => {
		const permissions = groups[groupName];

		// 1. Filter based on CREATOR (Who is logged in) - STRICT RESTRICTION
		if (isOrgAdminCreator) {
			// Org Admin can ONLY see Event, Ticket, Order
			if (!['Event Management', 'Ticket Management', 'Order Management'].includes(groupName)) {
				return;
			}
		}

		filtered[groupName] = permissions;
	});

	return filtered;
});

const shouldShowOrganization = computed(() => {
	if (!form.role_id) return false;
	const role = props.roles?.find((r) => r.id === form.role_id);
	// Check for standard org roles
	return role?.name === ROLES.ORG_ADMIN || role?.name === ROLES.ORG_STAFF;
});

const executeSubmit = () => {
	const submitData = {
		...form.data(),
		organization_id: form.organization_id,
		// Explicitly include permissions from local ref
		permissions: [...selectedPermissions.value],
	};

	if (isEditMode.value) {
		form.transform(() => submitData).put(
			userRoute.update(props.initialData.id).url,
			{
				onSuccess: () => {
					toast.success('User updated successfully');
					emit('success');
				},
				onError: () => toast.error('Failed to update user'),
			},
		);
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

const confirmCreate = () => {
	showOrgWarning.value = false;
	executeSubmit();
};

const submit = () => {
	// 1. Client-side Validation using Zod
	const result = userSchema.safeParse(form.data());

	if (!result.success) {
		result.error.issues.forEach((issue) => {
			form.setError(issue.path[0] as any, issue.message);
		});
		toast.error('Please fix form errors.');
		return;
	}

	// 2. Custom Verification: Organization Required for Org Roles
	const role = props.roles?.find((r) => r.id === form.role_id);
	if (
		role &&
		(role.name === ROLES.ORG_ADMIN || role.name === ROLES.ORG_STAFF)
	) {
		if (!form.organization_id || form.organization_id === '__none__') {
			form.setError(
				'organization_id',
				'Organization is required for this role.',
			);
			toast.error('Organization is required.');
			return;
		}
	}

	if (
		!isEditMode.value &&
		form.organization_id &&
		form.organization_id !== '__none__' &&
		!props.isLockedOrganization
	) {
		showOrgWarning.value = true;
		return;
	}
	executeSubmit();
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
					<FieldLabel>Name <span class="text-destructive">*</span></FieldLabel>
					<FieldContent>
						<Input v-model="form.name" placeholder="John Doe" />
					</FieldContent>
					<FieldError>{{ form.errors.name }}</FieldError>
				</Field>

				<!-- Email -->
				<Field name="email" :invalid="!!form.errors.email">
					<FieldLabel>Email <span class="text-destructive">*</span></FieldLabel>
					<FieldContent>
						<Input
							v-model="form.email"
							type="email"
							placeholder="john@example.com"
						/>
					</FieldContent>
					<FieldError>{{ form.errors.email }}</FieldError>
				</Field>

				<!-- Password (Edit Mode Only) -->
				<Field
					v-if="isEditMode"
					name="password"
					:invalid="!!form.errors.password"
				>
					<FieldLabel>New Password (Optional)</FieldLabel>
					<FieldContent>
						<Input
							v-model="form.password"
							type="password"
							placeholder="********"
						/>
					</FieldContent>
					<FieldError>{{ form.errors.password }}</FieldError>
				</Field>

				<!-- Role -->
				<Field name="role_id" :invalid="!!form.errors.role_id">
					<FieldLabel>Role <span class="text-destructive">*</span></FieldLabel>
					<FieldContent>
						<Select v-model="form.role_id">
							<SelectTrigger>
								<SelectValue placeholder="Select Role" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem
									v-for="role in roles"
									:key="role.id"
									:value="role.id"
								>
									{{ formatRoleName(role.name) }}
								</SelectItem>
							</SelectContent>
						</Select>
					</FieldContent>
					<FieldError>{{ form.errors.role_id }}</FieldError>
				</Field>

				<!-- Organization -->
				<Field
					v-if="shouldShowOrganization"
					name="organization_id"
					:invalid="!!form.errors.organization_id"
				>
					<FieldLabel class="flex items-center gap-2">
						Organization <span class="text-destructive">*</span>
						<Lock
							v-if="isEditMode || isLockedOrganization"
							class="h-3 w-3 text-muted-foreground"
						/>
					</FieldLabel>
					<FieldContent>
						<Select
							v-model="form.organization_id"
							:disabled="isEditMode || isLockedOrganization"
						>
							<SelectTrigger>
								<SelectValue
									placeholder="Select Organization"
								/>
							</SelectTrigger>
							<SelectContent>
								<SelectItem
									v-for="org in organizations"
									:key="org.id"
									:value="org.id"
								>
									{{ org.name }}
								</SelectItem>
							</SelectContent>
						</Select>
					</FieldContent>
					<FieldError>{{ form.errors.organization_id }}</FieldError>
					<p
						v-if="isEditMode"
						class="mt-1 text-xs text-muted-foreground"
					>
						Organization cannot be changed after assignment.
					</p>
				</Field>

				<!-- Phone Number -->
				<Field
					name="phone_number"
					:invalid="!!form.errors.phone_number"
				>
					<FieldLabel>Phone Number</FieldLabel>
					<FieldContent>
						<Input
							v-model="form.phone_number"
							placeholder="+123..."
						/>
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
								<SelectItem value="inactive"
									>Inactive</SelectItem
								>
							</SelectContent>
						</Select>
					</FieldContent>
					<FieldError>{{ form.errors.status }}</FieldError>
				</Field>
			</TabsContent>

			<TabsContent value="permissions" class="space-y-6 pt-4">
				<div
					v-if="
						!availablePermissions ||
						Object.keys(availablePermissions).length === 0
					"
					class="text-sm text-muted-foreground"
				>
					No permissions available.
				</div>
                <!-- ... -->
				<div v-else class="space-y-6">
					<div
						v-for="(group, groupName) in filteredPermissions"
						:key="groupName"
						class="rounded-lg border bg-card p-4"
					>
						<h4
							class="mb-3 text-sm font-medium text-foreground capitalize"
						>
							{{ groupName }}
						</h4>
						<div class="grid grid-cols-1 gap-3 md:grid-cols-2">
							<div
								v-for="permission in group"
								:key="permission.id"
								class="flex items-center gap-2"
							>
								<Checkbox
									:id="`perm-${permission.id}`"
									:model-value="isPermissionChecked(permission.name)"
									:disabled="isPermissionInherited(permission.name)"
									@update:model-value="(checked: any) => togglePermission(permission.name, checked)"
								/>
								<Label
									:for="`perm-${permission.id}`"
									class="cursor-pointer text-sm select-none"
									:class="{
										'text-muted-foreground':
											isPermissionInherited(
												permission.name,
											),
									}"
								>
									{{ permission.label }}
									<span
										v-if="
											isPermissionInherited(
												permission.name,
											)
										"
										class="ml-1 rounded bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground"
										>(Inherited)</span
									>
								</Label>
							</div>
						</div>
					</div>
				</div>

				<p class="mt-4 text-xs text-muted-foreground">
					Permissions inherit from the selected Role. Inherited
					permissions cannot be manually unchecked.
				</p>
			</TabsContent>
		</Tabs>

		<div class="flex justify-end pt-4">
			<Button type="submit" :disabled="form.processing">
				<Loader2
					v-if="form.processing"
					class="mr-2 h-4 w-4 animate-spin"
				/>
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
					<br /><br />
					<b>Important:</b> Once assigned, the organization
					<b>cannot be changed</b>. Are you sure you want to proceed?
				</AlertDialogDescription>
			</AlertDialogHeader>
			<AlertDialogFooter>
				<AlertDialogCancel @click="showOrgWarning = false"
					>Cancel</AlertDialogCancel
				>
				<AlertDialogAction @click="confirmCreate"
					>Yes, Create User</AlertDialogAction
				>
			</AlertDialogFooter>
		</AlertDialogContent>
	</AlertDialog>
</template>
