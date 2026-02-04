<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AttendanceCard from '@/components/scanner/AttendanceCard.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { RefreshCw, Users, Ticket, CheckCircle } from 'lucide-vue-next';
import { Progress } from '@/components/ui/progress';

interface TicketTypeStats {
	id: string;
	name: string;
	total: number;
	scanned: number;
}

interface AttendanceData {
	eventId: string;
	eventName: string;
	totalTickets: number;
	scannedTickets: number;
	remainingTickets: number;
	ticketTypes: TicketTypeStats[];
}

interface Event {
	id: string;
	name: string;
}

const breadcrumbs = [
	{ title: 'Scanner', href: '/scanner' },
	{ title: 'Attendance', href: '/scanner/attendance' },
];

const events = ref<Event[]>([]);
const selectedEventId = ref<string>('');
const attendanceData = ref<AttendanceData | null>(null);
const isLoading = ref(false);
const error = ref<string | null>(null);

const overallPercentage = computed(() => {
	if (!attendanceData.value || attendanceData.value.totalTickets === 0) return 0;
	return Math.round((attendanceData.value.scannedTickets / attendanceData.value.totalTickets) * 100);
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

const fetchAttendance = async () => {
	if (!selectedEventId.value) return;

	isLoading.value = true;
	error.value = null;

	try {
		const response = await fetch(`/scanner/attendance/data?eventId=${selectedEventId.value}`);
		const data = await response.json();

		if (data.success) {
			attendanceData.value = data.data;
		} else {
			error.value = data.message || 'Failed to load attendance data';
		}
	} catch {
		error.value = 'Network error. Please try again.';
	} finally {
		isLoading.value = false;
	}
};

const handleEventChange = (eventId: unknown) => {
	if (typeof eventId === 'string') {
		selectedEventId.value = eventId;
		fetchAttendance();
	}
};

const refresh = () => {
	fetchAttendance();
};

onMounted(() => {
	fetchEvents();
});
</script>

<template>
	<Head title="Attendance" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex h-full flex-1 flex-col gap-4 p-4">
			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Attendance Dashboard</h1>
				<div class="flex items-center gap-2">
					<Select :model-value="selectedEventId" @update:model-value="handleEventChange">
						<SelectTrigger class="w-[250px]">
							<SelectValue placeholder="Select an event" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem
								v-for="event in events"
								:key="event.id"
								:value="event.id"
							>
								{{ event.name }}
							</SelectItem>
						</SelectContent>
					</Select>
					<Button
						variant="outline"
						size="icon"
						@click="refresh"
						:disabled="isLoading || !selectedEventId"
					>
						<RefreshCw :class="['h-4 w-4', { 'animate-spin': isLoading }]" />
					</Button>
				</div>
			</div>

			<!-- No event selected -->
			<Card v-if="!selectedEventId" class="border-dashed">
				<CardContent class="flex min-h-[200px] flex-col items-center justify-center gap-2">
					<Users class="text-muted-foreground h-12 w-12" />
					<p class="text-muted-foreground">Select an event to view attendance</p>
				</CardContent>
			</Card>

			<!-- Error state -->
			<Card v-else-if="error" class="border-destructive">
				<CardContent class="flex min-h-[200px] items-center justify-center">
					<p class="text-destructive">{{ error }}</p>
				</CardContent>
			</Card>

			<!-- Attendance data -->
			<template v-else-if="attendanceData">
				<!-- Summary cards -->
				<div class="grid gap-4 md:grid-cols-3">
					<Card>
						<CardContent class="pt-6">
							<div class="flex items-center gap-4">
								<div class="bg-primary/10 rounded-full p-3">
									<Ticket class="text-primary h-6 w-6" />
								</div>
								<div>
									<p class="text-muted-foreground text-sm">Total Tickets</p>
									<p class="text-2xl font-bold">
										{{ attendanceData.totalTickets.toLocaleString() }}
									</p>
								</div>
							</div>
						</CardContent>
					</Card>

					<Card>
						<CardContent class="pt-6">
							<div class="flex items-center gap-4">
								<div class="rounded-full bg-green-500/10 p-3">
									<CheckCircle class="h-6 w-6 text-green-500" />
								</div>
								<div>
									<p class="text-muted-foreground text-sm">Scanned</p>
									<p class="text-2xl font-bold text-green-600 dark:text-green-400">
										{{ attendanceData.scannedTickets.toLocaleString() }}
									</p>
								</div>
							</div>
						</CardContent>
					</Card>

					<Card>
						<CardContent class="pt-6">
							<div class="flex items-center gap-4">
								<div class="rounded-full bg-yellow-500/10 p-3">
									<Users class="h-6 w-6 text-yellow-500" />
								</div>
								<div>
									<p class="text-muted-foreground text-sm">Remaining</p>
									<p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
										{{ attendanceData.remainingTickets.toLocaleString() }}
									</p>
								</div>
							</div>
						</CardContent>
					</Card>
				</div>

				<!-- Overall progress -->
				<Card>
					<CardHeader>
						<CardTitle>Overall Attendance</CardTitle>
						<CardDescription>{{ attendanceData.eventName }}</CardDescription>
					</CardHeader>
					<CardContent>
						<div class="space-y-2">
							<div class="flex justify-between text-sm">
								<span>{{ overallPercentage }}% checked in</span>
								<span class="text-muted-foreground">
									{{ attendanceData.scannedTickets }} / {{ attendanceData.totalTickets }}
								</span>
							</div>
							<Progress :model-value="overallPercentage" class="h-4" />
						</div>
					</CardContent>
				</Card>

				<!-- Per ticket type -->
				<Card v-if="attendanceData.ticketTypes?.length">
					<CardHeader>
						<CardTitle>By Ticket Type</CardTitle>
					</CardHeader>
					<CardContent>
						<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
							<AttendanceCard
								v-for="ticketType in attendanceData.ticketTypes"
								:key="ticketType.id"
								:title="ticketType.name"
								:value="ticketType.scanned"
								:total="ticketType.total"
								variant="success"
							/>
						</div>
					</CardContent>
				</Card>
			</template>

			<!-- Loading state -->
			<div v-else-if="isLoading" class="flex min-h-[200px] items-center justify-center">
				<div class="border-primary h-8 w-8 animate-spin rounded-full border-4 border-t-transparent" />
			</div>
		</div>
	</AppLayout>
</template>
