<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns, type Organization } from './columns';
import OrganizationForm from './OrganizationForm.vue';
import { ref } from 'vue';
import { BreadcrumbItem } from '@/types';
import organizationRoute from '@/routes/organization';
import { useQueryClient } from '@tanstack/vue-query';
import { usePermission } from '@/composables/usePermission';
import axios from 'axios';
import { toast } from 'vue-sonner';

const queryClient = useQueryClient();
const { can } = usePermission();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Organizations',
    href: organizationRoute.index().url,
  },
];

const isDialogOpen = ref(false);
const selectedItem = ref<Organization | null>(null);
const isLoadingEdit = ref(false);

const openCreate = () => {
  selectedItem.value = null;
  isDialogOpen.value = true;
};

const openEdit = async (item: Organization) => {
  try {
    isLoadingEdit.value = true;
    const response = await axios.get(organizationRoute.show(item.id).url);
    selectedItem.value = response.data;
    isDialogOpen.value = true;
  } catch (error) {
    toast.error('Failed to load organization data');
    console.error(error);
  } finally {
    isLoadingEdit.value = false;
  }
};

const onActionSuccess = () => {
  queryClient.invalidateQueries({ queryKey: ['organizations'] });
  isDialogOpen.value = false;
};

const columns = useColumns({
  canDelete: can('organizations.delete'),
  canEdit: can('organizations.update')
}, openEdit, onActionSuccess);

</script>

<template>
  <ContentLayout title="Organizations" :breadcrumbs="breadcrumbs">
    <DataTable
      :columns="columns"
      :on-create="can('organizations.create') ? openCreate : undefined"
      create-label="Add Organization"
      :api-url="organizationRoute.data().url"
      :query-key="['organizations']"
      :loading="isLoadingEdit"
    />

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit Organization' : 'Create Organization'"
    >
      <OrganizationForm
        :initial-data="selectedItem"
        @success="onActionSuccess"
      />
    </BaseDialog>
  </ContentLayout>
</template>
