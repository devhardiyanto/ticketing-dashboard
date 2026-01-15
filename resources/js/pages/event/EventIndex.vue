<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { computed, ref, h, defineAsyncComponent } from 'vue';
import { useColumns } from './columns';
import { Spinner } from '@/components/ui/spinner';

const EventForm = defineAsyncComponent({
	loader: () => import('./EventForm.vue'),
	loadingComponent: Spinner,
});

import type { Event, Organization } from '@/types/dashboard';
import event from '@/routes/event';
import { BreadcrumbItem } from '@/types';
import { useQueryClient } from '@tanstack/vue-query';


const props = defineProps<{
	parent_event?: Event | null;
	organizations: Organization[];
}>();

const queryClient = useQueryClient();

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

const onActionSuccess = () => {
	queryClient.invalidateQueries({ queryKey: ['events'] });
	isDialogOpen.value = false;
};

const columns = useColumns(openEdit, onActionSuccess);



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
  <ContentLayout :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
			<component :is="titleEvent" />
    </div>

    <DataTable
      :columns="columns"
      :on-create="openCreate"
      create-label="Add Event"
      :api-url="event.data({ query: { parent_id: parent_event?.id } }).url"
      :query-key="['events', parent_event?.id]"
    />

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit Event' : 'Create Event'"
    >
      <EventForm
        :initial-data="selectedItem"
				:parent-event="parent_event"
        :organizations="organizations"
        @success="onActionSuccess"
      />
    </BaseDialog>
  </ContentLayout>
</template>
