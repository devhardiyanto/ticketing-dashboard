<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { computed, ref } from 'vue';
import { useColumns } from './columns';
import EventForm from './EventForm.vue';

import type { Event, Organization } from '@/types/dashboard';
import event from '@/routes/event';
import { BreadcrumbItem } from '@/types';

const props = defineProps<{
	events: {
		data: Event[];
		current_page: number;
		per_page: number;
		total: number;
		last_page: number;
		from: number;
		to: number;
	};
	organizations: Organization[];
	filters?: {
		search?: string;
		limit?: number;
	};
}>();

const columns = useColumns();

// const getOrg = () => {

// }

const isDialogOpen = ref(false);
const selectedItem = ref<Event | null>(null);

const openCreate = () => {
	selectedItem.value = null;
	isDialogOpen.value = true;
};

const openEdit = (item: Event) => {
	selectedItem.value = item;
	isDialogOpen.value = true;
};

const tableData = computed(() =>
	props.events.data.map((event) => ({
		...event,
		onEdit: openEdit,
	})),
);

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Events',
		href: event.index().url,
	},
];
</script>

<template>
  <ContentLayout title="Events" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 class="text-lg font-medium">Event List</h3>
    </div>

    <DataTable
      :columns="columns"
      :data="tableData"
      :filters="filters"
      :pagination="events"
      :on-create="openCreate"
    />

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit Event' : 'Create Event'"
    >
      <EventForm
        :initial-data="selectedItem"
        :organizations="organizations"
        @success="isDialogOpen = false"
      />
    </BaseDialog>
  </ContentLayout>
</template>
