<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ContentLayout from '@/layouts/ContentLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Loader2, RefreshCw } from 'lucide-vue-next';
import { computed, h, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import { createColumnHelper } from '@tanstack/vue-table';

import Combobox from '@/components/common/Combobox.vue';
import DataTable from '@/components/common/DataTable.vue';
import SalesChart from '@/components/analytics/SalesChart.vue';
import analyticsRoute from '@/routes/analytics';
import { BreadcrumbItem } from '@/types';
import { AnalyticsData, EventOption } from '@/types/analytics';
import { formatCurrency } from '@/lib/utils-general';

interface Props {
	events: EventOption[];
	currentEventId: string | null;
}

const props = defineProps<Props>();

const selectedEventId = ref(
	props.currentEventId ||
	(props.events.length > 0 ? props.events[0].id : null),
);
const analyticsData = ref<AnalyticsData | null>(null);
const isLoading = ref(false);
const isRefreshing = ref(false);

const fetchData = async () => {
	if (!selectedEventId.value) return;

	isLoading.value = true;
	try {
		const response = await axios.get(
			analyticsRoute.data ? analyticsRoute.data().url : '/analytics/data',
			{
				// Fallback if route helper not ready
				params: { event_id: selectedEventId.value },
			},
		);
		analyticsData.value = response.data;
	} catch (error) {
		console.error('Failed to fetch analytics:', error);
		toast.error('Failed to load analytics data.');
	} finally {
		isLoading.value = false;
	}
};

const handleRefresh = async () => {
	if (!selectedEventId.value) return;
	isRefreshing.value = true;
	try {
		const response = await axios.post(
			analyticsRoute.refresh
				? analyticsRoute.refresh().url
				: '/analytics/refresh',
			{
				event_id: selectedEventId.value,
			},
		);
		analyticsData.value = response.data; // Refresh endpoint now returns data
		toast.success('Data refreshed.');
	} catch (error) {
		console.error('Failed to refresh:', error);
		toast.error('Failed to refresh data.');
	} finally {
		isRefreshing.value = false;
	}
};

const handleEventChange = (value: string) => {
	selectedEventId.value = value;
	// Update URL without reload to persist state
	const url = new URL(window.location.href);
	url.searchParams.set('event_id', value);
	window.history.pushState({}, '', url.toString());

	fetchData();
};

onMounted(() => {
	if (selectedEventId.value) {
		fetchData();
	}
});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
	{
		title: 'Analytics',
		href: '/analytics',
	},
]);

// Define DataTable columns for ranking
type RankingData = {
	ticket_name: string;
	total_sold: number;
	total_revenue: number;
};

const columnHelper = createColumnHelper<RankingData>();

const rankingColumns = [
	columnHelper.display({
		id: 'rank',
		header: '#',
		cell: ({ row, table }) => {
			const pageIndex = table.getState().pagination.pageIndex;
			const pageSize = table.getState().pagination.pageSize;
			return pageIndex * pageSize + row.index + 1;
		},
		size: 50,
	}),
	columnHelper.accessor('ticket_name', {
		header: 'Ticket Type',
	}),
	columnHelper.accessor('total_sold', {
		header: () => h('div', { class: 'text-right' }, 'Sold'),
		cell: ({ getValue }) => h('div', { class: 'text-right' }, String(getValue())),
	}),
	columnHelper.accessor('total_revenue', {
		header: () => h('div', { class: 'text-right' }, 'Revenue'),
		cell: ({ getValue }) => {
			const formatted = formatCurrency(getValue());
			return h('div', { class: 'text-right' }, formatted);
		},
	}),
];
</script>

<template>
	<Head title="Analytics" />

	<ContentLayout title="Analytics" :breadcrumbs="breadcrumbs">
		<!-- Removed Header Slot as requested -->

		<div class="flex flex-col gap-6">
			<!-- Header Section inside Content -->
			<div
				class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
			>
				<div>
					<h2 class="text-3xl font-bold tracking-tight">Analytics</h2>
					<p class="text-muted-foreground">
						Overview of your event performance and sales revenue.
					</p>
				</div>

				<div class="flex items-center gap-2">
					<Combobox
						v-if="events.length > 0"
						:model-value="selectedEventId"
						:items="events"
						label="Event"
						@update:model-value="handleEventChange"
					/>
					<Button
						variant="outline"
						size="icon"
						@click="handleRefresh"
						:disabled="isRefreshing || !selectedEventId || isLoading
							"
					>
						<RefreshCw
							class="h-4 w-4"
							:class="{ 'animate-spin': isRefreshing }"
						/>
					</Button>
				</div>
			</div>

			<!-- Main Content -->
			<div
				v-if="isLoading && !analyticsData"
				class="flex items-center justify-center py-20"
			>
				<Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
			</div>

			<div
				v-else-if="!selectedEventId"
				class="rounded-xl border border-dashed py-20 text-center"
			>
				<h3 class="text-lg font-medium">No Event Selected</h3>
				<p class="text-muted-foreground">
					Please select or create an event to view analytics.
				</p>
			</div>

			<template v-else-if="analyticsData">
				<!-- Vertical Layout: Chart on top, Table below -->
				<div class="flex flex-col gap-4">
					<!-- Sales Chart with Platform Fee -->
					<SalesChart
						:data="analyticsData.chart"
						:platform-fee="analyticsData.overview.total_platform_fee"
					/>

					<!-- Ticket Sales Table -->
					<Card>
						<CardHeader>
							<CardTitle>Ticket Sales Ranking</CardTitle>
							<CardDescription>
								Performance ranking of ticket types by revenue and sales
							</CardDescription>
						</CardHeader>
						<CardContent>
							<DataTable
								:columns="rankingColumns"
								api-url="/analytics/ranking"
								:query-key="['analytics-ranking', selectedEventId]"
								:extra-params="{ event_id: selectedEventId }"
							/>
						</CardContent>
					</Card>
				</div>
			</template>
		</div>
	</ContentLayout>
</template>
