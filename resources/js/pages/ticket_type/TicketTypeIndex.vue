<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns } from './columns';
import TicketTypeForm from './TicketTypeForm.vue';

import Combobox from '@/components/common/Combobox.vue';
import { computed, ref } from 'vue';

import type { TicketType, Event } from '@/types/dashboard';
import { BreadcrumbItem } from '@/types';

import event from '@/routes/event';
import ticket_type from '@/routes/ticket_type';

const props = defineProps<{
	events: Event[];
	event_model?: Event;
	ticket_types: {
		data: TicketType[];
		current_page: number;
		per_page: number;
		total: number;
		last_page: number;
		from: number;
		to: number;
	};
	filters?: {
		search?: string;
		limit?: number;
	};
}>();

const event_id = computed(() => props.event_model?.id);

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Events',
		href: event.index().url,
	},
	{
		title: 'Ticket Types',
		href: ticket_type.index().url,
	}
];

const eventItems = computed(() => props.events.map(event => ({
	...event,
	url: `/ticket_type?event_id=${event.id}`
})));

const columns = useColumns();

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
</script>

<template>
  <ContentLayout title="Ticket Types" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 v-if="!event_model" class="text-lg font-medium">Ticket Types</h3>
			<h3 v-if="event_model" class="text-lg font-normal">Ticket Types for Event <b>{{ event_model.name }}</b></h3>
    </div>

		<div class="mb-4">
			<Combobox
				label="Event"
				auto-navigate
				:items="eventItems"
				v-model="event_id"
			/>
		</div>

		<DataTable
			v-if="event_model"
      :columns="columns"
      :data="tableData"
      :filters="filters"
      :pagination="ticket_types"
      :on-create="openCreate"
	  create-label="Add Ticket Type"
    />

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit TicketType' : 'Create TicketType'"
    >
      <TicketTypeForm
        :initial-data="selectedItem"
        :event-id="event_model?.id"
        @success="isDialogOpen = false"
      />
    </BaseDialog>
  </ContentLayout>
</template>
