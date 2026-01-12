<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns } from './columns';
import type { Order } from './columns';
import type { BreadcrumbItem } from '@/types';

defineProps<{
	orders: {
		data: Order[];
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
		status?: string;
		date_from?: string;
		date_to?: string;
	};
}>();

const columns = useColumns();

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Orders',
		href: '/order',
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
      :data="orders.data"
      :filters="filters"
      :pagination="orders"
      :show-create="false"
    />
  </ContentLayout>
</template>
