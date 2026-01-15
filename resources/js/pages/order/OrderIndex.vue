<script setup lang="ts">
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { useColumns } from './columns';
import type { BreadcrumbItem } from '@/types';
import orderRoute from '@/routes/order';

const columns = useColumns();

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
      :query-key="['orders']"
    />
  </ContentLayout>
</template>
