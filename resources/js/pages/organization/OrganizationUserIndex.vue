<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns } from '@/pages/user/columns'; // Reuse columns
import UserForm from '@/pages/user/UserForm.vue'; // Reuse form
import Combobox from '@/components/common/Combobox.vue';
import { computed, ref } from 'vue';
import { BreadcrumbItem } from '@/types';

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
  organization_model?: any; // The selected org context
  filters?: {
    search?: string;
    limit?: number;
    // ...
  };
  availablePermissions: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Organization Users',
    href: '/organization-users', // Hardcoded or use route helper if available e.g. route('organization.user.index')
  },
];

// Configure columns: Hide Organization column, Disable Delete
const columns = useColumns({ hideOrganization: true, canDelete: false });

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

const tableData = computed(() =>
  props.users.data.map((item) => ({
    ...item,
    onEdit: openEdit,
  }))
);

// Combobox items
const orgId = computed(() => props.organization_model?.id);
const orgItems = computed(() => props.organizations.map(org => ({
  id: org.id,
  label: org.name, // Combobox usually expects label/value or matches logic
  // Check Combobox implementation if it needs specific keys.
  // TicketTypeIndex uses ...event which has name/id.
  name: org.name,
  url: `/organization-users?organization_id=${org.id}`
})));

</script>

<template>
  <ContentLayout title="Organization Users" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 v-if="!organization_model" class="text-lg font-medium">Select an Organization</h3>
      <h3 v-if="organization_model" class="text-lg font-normal">Users for Organization <b>{{ organization_model.name }}</b></h3>
    </div>

    <div class="mb-4">
      <Combobox
        label="Organization"
        auto-navigate
        :items="orgItems"
        v-model="orgId"
      />
    </div>

    <DataTable
      v-if="organization_model"
      :columns="columns"
      :data="tableData"
      :filters="filters"
      :pagination="users"
      :on-create="openCreate"
      :create-label="`Add User to Orgs`"
    />

    <div v-else class="text-center py-10 text-muted-foreground border rounded-lg bg-muted/10">
      Please select an organization to view and manage users.
    </div>

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit User' : `Add User to ${organization_model?.name}`"
    >
      <UserForm
        :initial-data="selectedItem"
        :organizations="organizations"
        :roles="roles"
        :is-locked-organization="true"
        :locked-organization-id="organization_model?.id"
        :locked-organization-name="organization_model?.name"
        :available-permissions="availablePermissions"
        @success="isDialogOpen = false"
      />
    </BaseDialog>
  </ContentLayout>
</template>
