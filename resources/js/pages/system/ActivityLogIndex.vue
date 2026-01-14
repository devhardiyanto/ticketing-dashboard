<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';

defineProps<{
	logs: {
		data: any[];
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
		event?: string;
		causer_id?: number;
	};
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Activity Logs',
		href: '/activity-logs',
	},
];

const columns: ColumnDef<any>[] = [
	{
		accessorKey: 'description',
		header: 'Description',
		cell: ({ row }) => row.original.description,
	},
	{
		accessorKey: 'causer',
		header: 'User',
		cell: ({ row }) => row.original.causer?.name || 'System',
	},
	{
		accessorKey: 'event',
		header: 'Action',
		cell: ({ row }) => row.original.event,
	},
	{
		accessorKey: 'created_at',
		header: 'Time',
		cell: ({ row }) => {
			const date = new Date(row.original.created_at);
			return date.toLocaleString();
		}
	}
];
</script>

<template>
  <ContentLayout title="Activity Logs" :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex justify-between">
      <h3 class="text-lg font-medium">System Activity Logs</h3>
    </div>

    <DataTable
      :columns="columns"
      :data="logs.data"
      :filters="filters"
      :pagination="logs"
      create-label=""
    />
  </ContentLayout>
</template>
