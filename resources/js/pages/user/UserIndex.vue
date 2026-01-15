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

const UserForm = defineAsyncComponent({
  loader: () => import('./UserForm.vue'),
  loadingComponent: Spinner,
});

const props = defineProps<{
  organizations: any[];
  roles: any[];
  availablePermissions: any[];
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

const columns = useColumns({ hideOrganization: false, canDelete: true }, openEdit, onActionSuccess);
</script>

<template>
  <ContentLayout title="Users" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 class="text-lg font-medium">All Users</h3>
    </div>

    <DataTable
      :columns="columns"
      :on-create="openCreate"
      create-label="Add User"
      :api-url="userRoute.data().url"
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
