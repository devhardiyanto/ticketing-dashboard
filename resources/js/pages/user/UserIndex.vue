<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns, type User } from './columns';
import { ref, defineAsyncComponent } from 'vue';
import { BreadcrumbItem } from '@/types';
import userRoute from '@/routes/user';
import { useQueryClient } from '@tanstack/vue-query';
import { Spinner } from '@/components/ui/spinner';
import { usePage } from '@inertiajs/vue3';

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

const openCreate = () => {
	selectedItem.value = null;
	isDialogOpen.value = true;
};

const openEdit = (item: User) => {
	selectedItem.value = item;
	isDialogOpen.value = true;
};

const onActionSuccess = () => {
	queryClient.invalidateQueries({ queryKey: ['users'] });
	isDialogOpen.value = false;
};

import { usePermission } from '@/composables/usePermission';

const { can } = usePermission();

const columns = useColumns({
	hideOrganization: false,
	canDelete: can('dashboard_users.delete'),
	canEdit: can('dashboard_users.update')
}, openEdit, onActionSuccess);
</script>

<template>
  <ContentLayout title="Users" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 class="text-lg font-medium">All Users</h3>
    </div>

    <DataTable
      :columns="columns"
      :on-create="can('dashboard_users.create') ? openCreate : undefined"
      create-label="Add User"
      :api-url="userRoute.data({ query: { organization_id: user?.organization_id } }).url"
      :query-key="['users']"
    />

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
