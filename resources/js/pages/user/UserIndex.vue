<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns } from './columns';
import UserForm from './UserForm.vue';
import { ref, computed } from 'vue';
import { BreadcrumbItem } from '@/types';
import userRoute from '@/routes/user';

const props = defineProps<{
  users: {
    data: any[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
  };
  organizations: any[];
  roles: any[];
  filters?: {
    search?: string;
    limit?: number;
    status?: string;
    organization_id?: string;
    role_id?: string;
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Users',
    href: userRoute.index().url,
  },
];

const columns = useColumns({ hideOrganization: false, canDelete: true });

const isDialogOpen = ref(false);
const selectedItem = ref<any>(null);

const openCreate = () => {
  selectedItem.value = null;
  isDialogOpen.value = true;
};

const openEdit = (item: any) => {
  selectedItem.value = item;
  isDialogOpen.value = true;
};

// Add onEdit handler to data rows
const tableData = computed(() =>
  props.users.data.map((item) => ({
    ...item,
    onEdit: openEdit,
  }))
);
</script>

<template>
  <ContentLayout title="Users" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 class="text-lg font-medium">All Users</h3>
    </div>

    <DataTable
      :columns="columns"
      :data="tableData"
      :filters="filters"
      :pagination="users"
      :on-create="openCreate"
      create-label="Add User"
    />

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit User' : 'Create User'"
    >
      <UserForm
        :initial-data="selectedItem"
        :organizations="organizations"
        :roles="roles"
        @success="isDialogOpen = false"
      />
    </BaseDialog>
  </ContentLayout>
</template>
