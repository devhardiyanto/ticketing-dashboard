import { Button } from '@/components/ui/button';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';
import OrderActions from './OrderActions.vue';

export interface Order {
	id: string;
	order_code: string;
	guest_name: string | null;
	guest_email: string | null;
	total_amount: number;
	platform_fee_amount: number;
	status: string;
	payment_method: string | null;
	event_name: string | null;
	event_id: string | null;
	created_at: string;
	expires_at: string | null;
	payment_deadline: string | null;
	items?: OrderItem[];
}

export interface OrderItem {
	id: string;
	ticket_type_id: string;
	quantity: number;
	price_per_ticket: number;
	subtotal: number;
	ticket_code: string | null;
	attendee_name: string | null;
	attendee_email: string | null;
	status: string;
	ticket_type?: {
		name: string;
		event?: {
			name: string;
		};
	};
}

const statusColors: Record<string, string> = {
	reserved: 'bg-yellow-100 text-yellow-800',
	pending_payment: 'bg-orange-100 text-orange-800',
	paid: 'bg-green-100 text-green-800',
	cancelled: 'bg-red-100 text-red-800',
	expired: 'bg-gray-100 text-gray-800',
	refunded: 'bg-purple-100 text-purple-800',
};

export const useColumns = () => {
	const columns: ColumnDef<Order>[] = [
		{
			accessorKey: 'order_code',
			header: ({ column }) => {
				return h(
					Button,
					{
						variant: 'ghost',
						onClick: () =>
							column.toggleSorting(column.getIsSorted() === 'asc'),
					},
					() => ['Order Code', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
				);
			},
			cell: ({ row }) => {
				return h('span', { class: 'font-mono text-sm' }, row.getValue('order_code'));
			},
		},
		{
			accessorKey: 'guest_name',
			header: 'Guest Name',
			cell: ({ row }) => row.getValue('guest_name') || '-',
		},
		{
			accessorKey: 'guest_email',
			header: 'Email',
			cell: ({ row }) => row.getValue('guest_email') || '-',
		},
		{
			accessorKey: 'event_name',
			header: 'Event',
			cell: ({ row }) => row.getValue('event_name') || 'N/A',
		},
		{
			accessorKey: 'total_amount',
			header: 'Total',
			cell: ({ row }) => {
				const amount = parseFloat(row.getValue('total_amount') || '0');
				return new Intl.NumberFormat('id-ID', {
					style: 'currency',
					currency: 'IDR',
					minimumFractionDigits: 0,
				}).format(amount);
			},
		},
		{
			accessorKey: 'status',
			header: 'Status',
			cell: ({ row }) => {
				const status = row.getValue('status') as string;
				const colorClass = statusColors[status] || 'bg-gray-100 text-gray-800';
				return h(
					'span',
					{ class: `px-2 py-1 rounded-full text-xs font-medium ${colorClass}` },
					status.replace('_', ' '),
				);
			},
		},
		{
			accessorKey: 'created_at',
			header: 'Date',
			cell: ({ row }) => {
				const date = new Date(row.getValue('created_at'));
				return date.toLocaleDateString('id-ID', {
					day: '2-digit',
					month: 'short',
					year: 'numeric',
					hour: '2-digit',
					minute: '2-digit',
				});
			},
		},
		{
			id: 'actions',
			size: 10,
			enableSorting: false,
			enableHiding: false,
			cell: ({ row }) => {
				const order = row.original;
				return h(OrderActions, { order });
			},
		},
	];

	return columns;
};
