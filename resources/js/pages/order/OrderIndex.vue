<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns } from './columns';
import type { BreadcrumbItem } from '@/types';
import orderRoute from '@/routes/order';

import Combobox from '@/components/common/Combobox.vue';
import { ref } from 'vue';

defineProps<{
	events?: { id: string; name: string }[]
}>();

const columns = useColumns();
const selectedEventId = ref<string | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Orders',
		href: orderRoute.index().url,
	},
];
</script>

<template>
  <ContentLayout title="Orders" :breadcrumbs="breadcrumbs">
    <div class="mb-4">
      <h3 class="text-lg font-medium">Order List</h3>
    </div>

    <DataTable
      :columns="columns"
      :api-url="orderRoute.data().url"
      :query-key="['orders', { event_id: selectedEventId }]"
      :extra-params="{ event_id: selectedEventId }"
    >
        <template #filter>
             <Combobox
                v-if="events && events.length > 0"
                :model-value="selectedEventId"
                :items="events"
                label="Filter by Event"
                class="w-[200px]"
                @update:model-value="(val) => selectedEventId = val"
             />
        </template>
    </DataTable>
  </ContentLayout>
</template>
