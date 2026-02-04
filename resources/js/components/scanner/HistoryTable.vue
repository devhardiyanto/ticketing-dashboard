<script setup lang="ts">
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

interface ScanLog {
	id: string;
	ticketCode: string;
	attendeeName: string;
	ticketType: string;
	status: 'success' | 'duplicate' | 'invalid';
	scannedAt: string;
	scanLocation?: string;
	activityType?: 'check-in' | 'check-out' | 'redeem';
}

interface Props {
	data: ScanLog[];
	isLoading?: boolean;
}

withDefaults(defineProps<Props>(), {
	isLoading: false,
});

const formatTime = (isoString: string) => {
	return new Date(isoString).toLocaleString('id-ID', {
		dateStyle: 'short',
		timeStyle: 'medium',
	});
};

const getStatusBadge = (status: string) => {
	switch (status) {
		case 'success':
			return { variant: 'default' as const, label: 'Valid' };
		case 'duplicate':
			return { variant: 'secondary' as const, label: 'Duplicate' };
		case 'invalid':
			return { variant: 'destructive' as const, label: 'Invalid' };
		default:
			return { variant: 'outline' as const, label: status };
	}
};

const getActivityBadge = (type?: string) => {
	switch (type) {
		case 'check-in':
			return { variant: 'default' as const, label: 'Check-In' };
		case 'check-out':
			return { variant: 'secondary' as const, label: 'Check-Out' };
		case 'redeem':
			return { variant: 'outline' as const, label: 'Redeem' };
		default:
			return { variant: 'outline' as const, label: type || 'Check-In' };
	}
};
</script>

<template>
	<div class="rounded-md border">
		<Table>
			<TableHeader>
				<TableRow>
					<TableHead>Ticket Code</TableHead>
					<TableHead>Activity</TableHead>
					<TableHead>Attendee</TableHead>
					<TableHead>Ticket Type</TableHead>
					<TableHead>Status</TableHead>
					<TableHead>Location</TableHead>
					<TableHead>Scanned At</TableHead>
				</TableRow>
			</TableHeader>
			<TableBody>
				<!-- Loading state -->
				<TableRow v-if="isLoading">
					<TableCell colspan="6" class="text-muted-foreground h-24 text-center">
						<div class="flex items-center justify-center">
							<div class="border-primary h-6 w-6 animate-spin rounded-full border-2 border-t-transparent" />
						</div>
					</TableCell>
				</TableRow>

				<!-- Empty state -->
				<TableRow v-else-if="data.length === 0">
					<TableCell colspan="6" class="text-muted-foreground h-24 text-center">
						No scan history found
					</TableCell>
				</TableRow>

				<!-- Data rows -->
				<TableRow v-else v-for="log in data" :key="log.id">
					<TableCell class="font-mono text-sm">{{ log.ticketCode }}</TableCell>
					<TableCell>
						<Badge :variant="getActivityBadge(log.activityType).variant">
							{{ getActivityBadge(log.activityType).label }}
						</Badge>
					</TableCell>
					<TableCell>{{ log.attendeeName }}</TableCell>
					<TableCell>{{ log.ticketType }}</TableCell>
					<TableCell>
						<Badge :variant="getStatusBadge(log.status).variant">
							{{ getStatusBadge(log.status).label }}
						</Badge>
					</TableCell>
					<TableCell>{{ log.scanLocation || '-' }}</TableCell>
					<TableCell class="text-muted-foreground text-sm">
						{{ formatTime(log.scannedAt) }}
					</TableCell>
				</TableRow>
			</TableBody>
		</Table>
	</div>
</template>
