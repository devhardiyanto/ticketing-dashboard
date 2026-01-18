<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import { Dialog } from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import ContentLayout from '@/layouts/ContentLayout.vue';
import roleRoute from '@/routes/role';
import type { CandidateUser, Role } from '@/types/dashboard';
import { useQueryClient } from '@tanstack/vue-query';
import { defineAsyncComponent, h, ref } from 'vue';
import { useColumns } from './columns';

const RoleForm = defineAsyncComponent({
	loader: () => import('./RoleForm.vue'),
	loadingComponent: Spinner,
});

defineProps<{
	users: CandidateUser[]; // We still need users for the modal
}>();

const queryClient = useQueryClient();
const isDialogOpen = ref(false);
const selectedRole = ref<Role | null>(null);

const openEdit = (role: Role) => {
	selectedRole.value = role;
	isDialogOpen.value = true;
};

const onActionSuccess = () => {
	isDialogOpen.value = false;
	queryClient.invalidateQueries({ queryKey: ['roles'] });
};

import { usePermission } from '@/composables/usePermission';
const { can } = usePermission();

// Use the columns definition we created earlier
const columns = useColumns(openEdit, {
	canEdit: can('roles.update'),
});

const breadcrumbs = [
	{
		title: 'User Management',
		href: '#',
	},
	{
		title: 'Roles',
		href: roleRoute.index().url,
	},
];

// Title Component for Layout
const titleRole = () => {
	return h('h3', { class: 'text-lg font-medium' }, 'Role Management');
};
</script>

<template>
	<ContentLayout :breadcrumbs="breadcrumbs">
		<div class="mb-4 flex justify-between">
			<component :is="titleRole" />
		</div>

		<DataTable
			:columns="columns"
			:api-url="roleRoute.data().url"
			:query-key="['roles']"
			:hide-search="true"
		/>

		<Dialog v-model:open="isDialogOpen">
			<RoleForm
				v-if="selectedRole"
				:role="selectedRole"
				:users="users"
				@success="onActionSuccess"
			/>
		</Dialog>
	</ContentLayout>
</template>
