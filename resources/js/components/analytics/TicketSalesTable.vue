<script setup lang="ts">
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { TicketSalesRank } from '@/types/dashboard/analytics';

interface Props {
	data: TicketSalesRank[];
}

defineProps<Props>();

const formatCurrency = (value: number) => {
	return new Intl.NumberFormat('id-ID', {
		style: 'currency',
		currency: 'IDR',
		minimumFractionDigits: 0,
		maximumFractionDigits: 0,
	}).format(value);
};

const getRowClass = (index: number, total: number) => {
	if (index < 2) {
		return 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-900 dark:text-yellow-100 font-medium';
	}
	if (index >= total - 3 && total >= 5) { // Only show red if enough items
		return 'bg-red-50 dark:bg-red-900/20 text-red-900 dark:text-red-100';
	}
	return '';
};
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[50px]">#</TableHead>
                    <TableHead>Ticket Type</TableHead>
                    <TableHead class="text-right">Sold</TableHead>
                    <TableHead class="text-right">Revenue</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="(item, index) in data"
                    :key="item.ticket_name"
                    :class="getRowClass(index, data.length)"
                >
                    <TableCell>{{ index + 1 }}</TableCell>
                    <TableCell>{{ item.ticket_name }}</TableCell>
                    <TableCell class="text-right">{{ item.total_sold }}</TableCell>
                    <TableCell class="text-right">{{ formatCurrency(item.total_revenue) }}</TableCell>
                </TableRow>
                <TableRow v-if="data.length === 0">
                    <TableCell colspan="4" class="text-center h-24 text-muted-foreground">
                        No ticket sales data available.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
