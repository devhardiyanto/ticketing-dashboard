import { Button } from '@/components/ui/button';
import type { TicketType } from '@/types/dashboard';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { h } from 'vue';
import TicketTypeActions from './TicketTypeActions.vue';

export const useColumns = (
	openEdit?: (ticketType: TicketType) => void,
	onSuccess?: () => void,
	options: { canEdit?: boolean; canDelete?: boolean } = {}
) => {
	const { canEdit = true, canDelete = true } = options;
	const page = usePage();
	const user = page.props.auth.user;

	const columns: ColumnDef<TicketType>[] = [
		{
			accessorKey: 'name',
			header: ({ column }) => {
				return h(
					Button,
					{
						variant: 'ghost',
						onClick: () =>
							column.toggleSorting(column.getIsSorted() === 'asc'),
					},
					() => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
				);
			},
		},
		{
			accessorKey: 'start_sale_date',
			header: 'Start Sale Date',
			cell: ({ row }) => {
				const value = row.getValue('start_sale_date') as string | null | undefined;
				if (!value) return '-';
				const date = new Date(value);
				return isNaN(date.getTime()) ? String(value) : date.toLocaleString();
			},
		},
		{
			accessorKey: 'end_sale_date',
			header: 'End Sale Date',
			cell: ({ row }) => {
				const value = row.getValue('end_sale_date') as string | null | undefined;
				if (!value) return '-';
				const date = new Date(value);
				return isNaN(date.getTime()) ? String(value) : date.toLocaleString();
			},
		},
		{
			accessorKey: 'quantity_available',
			header: 'Quantity Available',
			cell: ({ row }) => {
				const value = row.getValue('quantity_available') as number | null | undefined;
				return value ?? '-';
			},
		},
		{
			id: 'quantity_sold',
			header: 'Quantity Sold',
			cell: ({ row }) => {
				const original = row.original;
				const qty = typeof original.quantity === 'number' ? original.quantity : 0;
				const available = typeof original.quantity_available === 'number' ? original.quantity_available : null;
				if (available === null) return '-';
				return Math.max(0, qty - available);
			},
		},
		{
			accessorKey: 'price',
			header: 'Price',
			cell: ({ row }) => {
				const value = row.getValue('price') as number | string | null | undefined;
				if (value === null || value === undefined || value === '') return '-';
				const num = typeof value === 'number' ? value : Number(value);
				return Number.isFinite(num) ? num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : String(value);
			},
		},
		{
			accessorKey: 'status',
			header: 'Status',
		},
		{
			accessorKey: 'category',
			header: 'Category',
			cell: ({ row }) => row.getValue('category') || '-',
		},
		{
			accessorKey: 'is_hidden',
			header: 'Hidden',
			cell: ({ row }) => row.getValue('is_hidden') ? 'Yes' : 'No',
		},
		{
			accessorKey: 'sort_order',
			header: 'Order',
		},
		{
			id: 'actions',
			size: 10, // Make column slimmer
			enableSorting: false,
			enableHiding: false,
			cell: ({ row }) => {
				const ticket_type = row.original;
				return h(TicketTypeActions, {
					ticket_type,
					canEdit,
					canDelete,
					onEdit: () => openEdit?.(ticket_type),
					onSuccess,
				});
			},
		},
	];

	if (user.organization_id) {
		return columns.filter((column) => column.header !== 'Organization');
	}

	return columns;
};
