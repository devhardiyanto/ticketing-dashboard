<script setup lang="ts">
import ContentLayout from '@/layouts/ContentLayout.vue';
import Combobox from '@/components/common/Combobox.vue';
import { computed } from 'vue';

import type { Event } from '@/types/dashboard';
import { BreadcrumbItem } from '@/types';

import event from '@/routes/event';
import ticket_type from '@/routes/ticket_type';

const props = defineProps<{
	events: Event[];
}>();


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
	url: `/ticket_type/${event.id}`
})));
console.log(eventItems.value)
</script>

<template>
  <ContentLayout title="Ticket Types" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 class="text-lg font-medium">Ticket Types</h3>
    </div>

		<div class="mb-4">
			<Combobox
				label="Event"
				auto-navigate
				:items="eventItems"
			/>
		</div>
  </ContentLayout>
</template>
