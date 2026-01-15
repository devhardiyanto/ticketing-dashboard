import { Button } from '@/components/ui/button';
import type { Event } from '@/types/dashboard/event';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { h } from 'vue';
import EventActions from './EventActions.vue';
import ImagePreview from '@/components/common/ImagePreview.vue';

export const useColumns = (
	onEdit?: (event: Event) => void,
	onSuccess?: () => void
) => {
	const page = usePage();
	const user = page.props.auth.user;

	const columns: ColumnDef<Event>[] = [
		{
			accessorKey: 'image_signed_url',
			header: 'Image',
			size: 80,
			enableSorting: false,
			cell: ({ row }) => {
				const signedUrl = row.getValue('image_signed_url') as string | null;
				return h(ImagePreview, {
					src: signedUrl,
					alt: row.original.name,
				});
			},
		},
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
				const event = row.original;
				return h(EventActions, {
					event,
					onEdit: (e: Event) => onEdit?.(e),
					onSuccess: () => onSuccess?.(),
				});
			},
		},
	];

	if (user.organization_id) {
		return columns.filter((column) => column.header !== 'Organization');
	}

	return columns;
};
