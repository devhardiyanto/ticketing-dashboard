<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { computed, ref } from 'vue';
import { useColumns } from './columns';
import TicketTypeForm from './TicketTypeForm.vue';

import type { TicketType, Event } from '@/types/dashboard';
import event_route from '@/routes/event';
import { BreadcrumbItem } from '@/types';

const props = defineProps<{
	ticket_types: {
		data: TicketType[];
		current_page: number;
		per_page: number;
		total: number;
		last_page: number;
		from: number;
		to: number;
	};
	event: Event;
	filters?: {
		search?: string;
		limit?: number;
	};
}>();

const columns = useColumns();

// const getOrg = () => {

// }

const isDialogOpen = ref(false);
const selectedItem = ref<TicketType | null>(null);

const openCreate = () => {
	selectedItem.value = null;
	isDialogOpen.value = true;
};

const openEdit = (item: TicketType) => {
	selectedItem.value = item;
	isDialogOpen.value = true;
};

const tableData = computed(() =>
	props.ticket_types.data.map((item) => ({
		...item,
		onEdit: openEdit,
	})),
);

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Events',
		href: event_route.index().url,
	},
	{
		title: props.event.name,
		href: event_route.index(props.event.parent_event_id || props.event.id).url,
	},
	{
		title: 'Ticket Types',
		href: '#'
	}
];
</script>

<template>
  <ContentLayout title="Ticket Types" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 class="text-lg font-normal">Ticket Types for Event <b>{{ event.name }}</b></h3>
    </div>

    <DataTable
      :columns="columns"
      :data="tableData"
      :filters="filters"
      :pagination="ticket_types"
      :on-create="openCreate"
    />

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit TicketType' : 'Create TicketType'"
    >
      <TicketTypeForm
        :initial-data="selectedItem"
        :event-id="event.id"
        @success="isDialogOpen = false"
      />
    </BaseDialog>
  </ContentLayout>
</template>
