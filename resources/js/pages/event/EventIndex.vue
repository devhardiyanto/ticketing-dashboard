<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { computed, ref, h } from 'vue';
import { useColumns } from './columns';
import EventForm from './EventForm.vue';

import type { Event, Organization } from '@/types/dashboard';
import event from '@/routes/event';
import { BreadcrumbItem } from '@/types';

const props = defineProps<{
	parent_event?: Event | null;
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

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
	const items: BreadcrumbItem[] = [
		{
			title: 'Events',
			href: event.index().url,
		},
	];

	if (props.parent_event) {
		items.push({
			title: props.parent_event.name,
			href: event.index(props.parent_event.id).url,
		});
	}

	return items;
});

const titleEvent = () => {
	return h(
		'h3',
		{ class: props.parent_event ? 'text-lg font-normal' : 'text-lg font-medium' },
		props.parent_event
			? ['Events for ', h('b', props.parent_event.name)]
			: 'Event List',
	);
}
</script>

<template>
  <ContentLayout title="" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
			<component :is="titleEvent" />
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
				:parent-event="parent_event"
        :organizations="organizations"
        @success="isDialogOpen = false"
      />
    </BaseDialog>
  </ContentLayout>
</template>
