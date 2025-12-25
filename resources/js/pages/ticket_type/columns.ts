import { Button } from '@/components/ui/button';
import type { TicketType } from '@/types/dashboard';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { h } from 'vue';
import TicketTypeActions from './TicketTypeActions.vue';

export const useColumns = () => {
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
			accessorKey: 'start_date',
			header: 'Start Date',
			cell: ({ row }) => {
				const date = new Date(row.getValue('start_date'));
				return date.toLocaleDateString();
			},
		},
		{
			accessorKey: 'organization.name',
			header: 'Organization',
		},
		{
			accessorKey: 'location',
			header: 'Location',
		},
		{
			accessorKey: 'timezone',
			header: 'Timezone',
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
					onEdit: (e: TicketType) => (ticket_type as any).onEdit?.(e),
				});
			},
		},
	];

	if (user.organization_id) {
		return columns.filter((column) => column.header !== 'Organization');
	}

	return columns;
};
