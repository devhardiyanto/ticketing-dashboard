<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HistoryTable from '@/components/scanner/HistoryTable.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { RefreshCw, Filter, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {
	Pagination,
	PaginationContent,
	PaginationNext,
	PaginationPrevious,
} from '@/components/ui/pagination';

interface ScanLog {
	id: string;
	ticketCode: string;
	attendeeName: string;
	ticketType: string;
	status: 'success' | 'duplicate' | 'invalid';
	scannedAt: string;
	scanLocation?: string;
}

interface Event {
	id: string;
	name: string;
}

interface PaginationMeta {
	currentPage: number;
	totalPages: number;
	totalItems: number;
	itemsPerPage: number;
}

const breadcrumbs = [
	{ title: 'Scanner', href: '/scanner' },
	{ title: 'History', href: '/scanner/history' },
];

const events = ref<Event[]>([]);
const scanLogs = ref<ScanLog[]>([]);
const pagination = ref<PaginationMeta>({
	currentPage: 1,
	totalPages: 1,
	totalItems: 0,
	itemsPerPage: 20,
});

const isLoading = ref(false);
const showFilters = ref(false);

// Filters
const filters = ref({
	eventId: '',
	status: '',
	startDate: '',
	endDate: '',
});

const fetchEvents = async () => {
	try {
		const response = await fetch('/event/data?limit=100');
		const data = await response.json();
		if (data.data) {
			events.value = data.data.map((e: any) => ({
				id: e.id,
				name: e.name,
			}));
		}
	} catch (e) {
		console.error('Failed to fetch events:', e);
	}
};

const fetchHistory = async (page = 1) => {
	isLoading.value = true;

	try {
		const params = new URLSearchParams({
			page: String(page),
			limit: String(pagination.value.itemsPerPage),
		});

		if (filters.value.eventId) params.set('eventId', filters.value.eventId);
		if (filters.value.status) params.set('status', filters.value.status);
		if (filters.value.startDate) params.set('startDate', filters.value.startDate);
		if (filters.value.endDate) params.set('endDate', filters.value.endDate);

		const response = await fetch(`/scanner/history/data?${params.toString()}`);
		const data = await response.json();

		if (data.success) {
			scanLogs.value = data.data || [];
			pagination.value = {
				currentPage: data.meta?.currentPage || page,
				totalPages: data.meta?.totalPages || 1,
				totalItems: data.meta?.totalItems || 0,
				itemsPerPage: data.meta?.itemsPerPage || 20,
			};
		}
	} catch (e) {
		console.error('Failed to fetch history:', e);
	} finally {
		isLoading.value = false;
	}
};

const goToPage = (page: number) => {
	if (page >= 1 && page <= pagination.value.totalPages) {
		fetchHistory(page);
	}
};

const applyFilters = () => {
	fetchHistory(1);
};

const clearFilters = () => {
	filters.value = {
		eventId: '',
		status: '',
		startDate: '',
		endDate: '',
	};
	fetchHistory(1);
};

const refresh = () => {
	fetchHistory(pagination.value.currentPage);
};

onMounted(() => {
	fetchEvents();
	fetchHistory();
});
</script>

<template>
	<Head title="Scan History" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex h-full flex-1 flex-col gap-4 p-4">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Scan History</h1>
				<div class="flex items-center gap-2">
					<Button
						variant="outline"
						size="sm"
						@click="showFilters = !showFilters"
					>
						<Filter class="mr-2 h-4 w-4" />
						Filters
					</Button>
					<Button
						variant="outline"
						size="icon"
						@click="refresh"
						:disabled="isLoading"
					>
						<RefreshCw :class="['h-4 w-4', { 'animate-spin': isLoading }]" />
					</Button>
				</div>
			</div>

			<!-- Filters -->
			<Card v-if="showFilters">
				<CardContent class="pt-6">
					<div class="grid gap-4 md:grid-cols-4">
						<div class="space-y-2">
							<Label>Event</Label>
							<Select v-model="filters.eventId">
								<SelectTrigger>
									<SelectValue placeholder="All events" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="">All events</SelectItem>
									<SelectItem
										v-for="event in events"
										:key="event.id"
										:value="event.id"
									>
										{{ event.name }}
									</SelectItem>
								</SelectContent>
							</Select>
						</div>

						<div class="space-y-2">
							<Label>Status</Label>
							<Select v-model="filters.status">
								<SelectTrigger>
									<SelectValue placeholder="All statuses" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="">All statuses</SelectItem>
									<SelectItem value="success">Valid</SelectItem>
									<SelectItem value="duplicate">Duplicate</SelectItem>
									<SelectItem value="invalid">Invalid</SelectItem>
								</SelectContent>
							</Select>
						</div>

						<div class="space-y-2">
							<Label>Start Date</Label>
							<Input
								type="date"
								v-model="filters.startDate"
							/>
						</div>

						<div class="space-y-2">
							<Label>End Date</Label>
							<Input
								type="date"
								v-model="filters.endDate"
							/>
						</div>
					</div>

					<div class="mt-4 flex justify-end gap-2">
						<Button variant="outline" @click="clearFilters">
							Clear
						</Button>
						<Button @click="applyFilters">
							Apply Filters
						</Button>
					</div>
				</CardContent>
			</Card>

			<!-- Results info -->
			<div class="text-muted-foreground text-sm">
				Showing {{ scanLogs.length }} of {{ pagination.totalItems }} records
			</div>

			<!-- History table -->
			<HistoryTable :data="scanLogs" :is-loading="isLoading" />

			<!-- Pagination -->
			<div v-if="pagination.totalPages > 1" class="flex justify-center">
				<Pagination
					:total="pagination.totalItems"
					:items-per-page="pagination.itemsPerPage"
					:sibling-count="1"
					show-edges
					:default-page="pagination.currentPage"
				>
					<PaginationContent>
						<div class="mr-4">
							<p class="text-muted-foreground text-sm">
								Page {{ pagination.currentPage }} of {{ pagination.totalPages }}
							</p>
						</div>
						<PaginationPrevious
							@click="goToPage(pagination.currentPage - 1)"
							:disabled="pagination.currentPage <= 1"
							class="cursor-pointer"
						>
							<ChevronLeft class="h-4 w-4" />
						</PaginationPrevious>
						<PaginationNext
							@click="goToPage(pagination.currentPage + 1)"
							:disabled="pagination.currentPage >= pagination.totalPages"
							class="cursor-pointer"
						>
							<ChevronRight class="h-4 w-4" />
						</PaginationNext>
					</PaginationContent>
				</Pagination>
			</div>
		</div>
	</AppLayout>
</template>
