<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns } from './columns';
import { Spinner } from '@/components/ui/spinner';
import { defineAsyncComponent } from 'vue';

const ItemForm = defineAsyncComponent({
	loader: () => import('./Form.vue'),
	loadingComponent: Spinner,
});

import Combobox from '@/components/common/Combobox.vue';
import { computed, ref } from 'vue';

import type { Item, Event } from '@/types/dashboard';
import { BreadcrumbItem } from '@/types';

import event from '@/routes/event';
import item from '@/routes/items';
import { useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { toast } from 'vue-sonner';

const props = defineProps<{
	events: Event[];
	event_model?: Event;
}>();

const queryClient = useQueryClient();
const event_id = computed(() => props.event_model?.id);

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Events',
		href: event.index().url,
	},
	{
		title: 'Ticket Types',
		href: item.index().url,
	}
];

const eventItems = computed(() => props.events.map(evt => ({
	...evt,
	url: `/items?event_id=${evt.id}`
})));

const isDialogOpen = ref(false);
const selectedItem = ref<Item | null>(null);
const isLoadingEdit = ref(false);

const openCreate = () => {
	selectedItem.value = null;
	isDialogOpen.value = true;
};

const openEdit = async (selected: Item) => {
	try {
		isLoadingEdit.value = true;
		const response = await axios.get(item.show(selected.id).url);
		selectedItem.value = response.data;
		isDialogOpen.value = true;
	} catch (error) {
		toast.error('Failed to load item data');
		console.error(error);
	} finally {
		isLoadingEdit.value = false;
	}
};

const onActionSuccess = () => {
	queryClient.invalidateQueries({ queryKey: ['items', event_id.value] });
	isDialogOpen.value = false;
};

import { usePermission } from '@/composables/usePermission';
const { can } = usePermission();

const columns = useColumns(openEdit, onActionSuccess, {
	canEdit: can('tickets.update'),
	canDelete: can('tickets.delete')
});

// Compute API URL with event_id filter
const apiUrl = computed(() => {
	if (!event_id.value) return '';
	return item.data({ query: { event_id: event_id.value } }).url;
});
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
			v-if="event_model && apiUrl"
      :columns="columns"
      :on-create="can('tickets.create') ? openCreate : undefined"
	    create-label="Add Item"
      :api-url="apiUrl"
      :query-key="['items', event_id]"
    />

    <BaseDialog
      v-model:open="isDialogOpen"
      :title="selectedItem ? 'Edit Item' : 'Create Item'"
    >
      <ItemForm
        :initial-data="selectedItem"
        :event-id="props.event_model?.id"
        @success="onActionSuccess"
      />
    </BaseDialog>
  </ContentLayout>
</template>
