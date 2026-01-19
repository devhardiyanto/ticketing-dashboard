<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { usePermission } from '@/composables/usePermission';
import ContentLayout from '@/layouts/ContentLayout.vue';
import userRoute from '@/routes/user';
import { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed, defineAsyncComponent, ref } from 'vue';
import { toast } from 'vue-sonner';
import { useColumns, type User } from './columns';
import { formatRoleName } from '@/lib/utils-general';

const page = usePage();
const user = page.props.auth.user;

const UserForm = defineAsyncComponent({
	loader: () => import('./UserForm.vue'),
	loadingComponent: Spinner,
});

const props = defineProps<{
	organizations: any[];
	roles: any[];
	availablePermissions: any;
}>();

const queryClient = useQueryClient();

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Users',
		href: userRoute.index().url,
	},
];

const isDialogOpen = ref(false);
const selectedItem = ref<User | null>(null);
const isLoadingEdit = ref(false);

const openCreate = () => {
	selectedItem.value = null;
	isDialogOpen.value = true;
};

const openEdit = async (item: User) => {
	try {
		isLoadingEdit.value = true;
		// We already have the item ID, so we use it to fetch fresh data
		const response = await axios.get(userRoute.show(item.id).url);
		selectedItem.value = response.data;
		isDialogOpen.value = true;
	} catch (error) {
		toast.error('Failed to load user data');
		console.error(error);
	} finally {
		isLoadingEdit.value = false;
	}
};

const onActionSuccess = () => {
	queryClient.invalidateQueries({ queryKey: ['users'] });
	isDialogOpen.value = false;
};

const { can } = usePermission();

const columns = useColumns(
	{
		hideOrganization: false,
		canDelete: can('users.delete'),
		canEdit: can('users.update'),
	},
	openEdit,
	onActionSuccess,
);

const selectedRole = ref<string>('all');

const roleOptions = computed(() => {
	const roles =
		props.roles?.map((r) => ({
			label: formatRoleName(r.name),
			value: String(r.id),
		})) || [];
	return [{ label: 'All Roles', value: 'all' }, ...roles];
});
</script>

<template>
	<ContentLayout title="Users" :breadcrumbs="breadcrumbs">
		<div class="mb-4 flex justify-between">
			<h3 class="text-lg font-medium">All Users</h3>
		</div>

		<DataTable
			:columns="columns"
			:on-create="can('users.create') ? openCreate : undefined"
			create-label="Add User"
			:api-url="userRoute.data({
				query: { organization_id: user?.organization_id },
			}).url
				"
			:query-key="['users']"
			:extra-params="{
				role_id: selectedRole === 'all' ? undefined : selectedRole,
			}"
		>
			<template #filter>
				<Select v-model="selectedRole">
					<SelectTrigger class="h-8 w-[150px]">
						<SelectValue placeholder="All Roles" />
					</SelectTrigger>
					<SelectContent>
						<SelectItem
							v-for="role in roleOptions"
							:key="role.value"
							:value="role.value"
						>
							{{ role.label }}
						</SelectItem>
					</SelectContent>
				</Select>
			</template>
		</DataTable>

		<BaseDialog
			v-model:open="isDialogOpen"
			:title="selectedItem ? 'Edit User' : 'Create User'"
		>
			<UserForm
				:initial-data="selectedItem"
				:organizations="props.organizations"
				:roles="props.roles"
				:available-permissions="props.availablePermissions"
				@success="onActionSuccess"
			/>
		</BaseDialog>
	</ContentLayout>
</template>
