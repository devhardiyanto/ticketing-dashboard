<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns, type User } from '@/pages/user/columns'; // Reuse columns
import UserForm from '@/pages/user/UserForm.vue'; // Reuse form
import Combobox from '@/components/common/Combobox.vue';
import { computed, ref } from 'vue';
import { BreadcrumbItem } from '@/types';
import organization from '@/routes/organization';
import { useQueryClient } from '@tanstack/vue-query';

const props = defineProps<{
  organizations: any[];
  roles: any[];
  organization_model?: any; // The selected org context
  availablePermissions: any[];
}>();

const queryClient = useQueryClient();
const orgId = computed(() => props.organization_model?.id);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Organization Users',
    href: organization.user.index().url,
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
  queryClient.invalidateQueries({ queryKey: ['org_users', orgId.value] });
  isDialogOpen.value = false;
};

import { usePermission } from '@/composables/usePermission';
const { can } = usePermission();

// Configure columns: Hide Organization column, Disable Delete
const columns = useColumns({
  hideOrganization: true,
  canDelete: can('organizations.delete'),
  canEdit: can('organizations.update')
}, openEdit, onActionSuccess);

// Combobox items
const orgItems = computed(() => props.organizations.map(org => ({
  id: org.id,
  label: org.name,
  name: org.name,
  url: `/organization-users?organization_id=${org.id}`
})));

// Compute API URL with organization_id filter
const apiUrl = computed(() => {
  if (!orgId.value) return '';
  return organization.user.data({ query: { organization_id: orgId.value } }).url;
});

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
      v-if="organization_model && apiUrl"
      :columns="columns"
      :on-create="can('organizations.create') ? openCreate : undefined"
      :create-label="`Add User to ${organization_model.name}`"
      :api-url="apiUrl"
      :query-key="['org_users', orgId]"
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
        :organizations="props.organizations"
        :roles="props.roles"
        :is-locked-organization="true"
        :locked-organization-id="organization_model?.id"
        :locked-organization-name="organization_model?.name"
        :available-permissions="props.availablePermissions"
        @success="onActionSuccess"
      />
    </BaseDialog>
  </ContentLayout>
</template>
