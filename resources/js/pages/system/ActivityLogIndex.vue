<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import activityLogs from '@/routes/activity-logs';

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Activity Logs',
		href: activityLogs.index().url,
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
      :api-url="activityLogs.data().url"
      :query-key="['activity_logs']"
    />
  </ContentLayout>
</template>
