import { Button } from '@/components/ui/button';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';
import BannerActions from './BannerActions.vue';
import BannerStatusSwitch from './BannerStatusSwitch.vue';
import ImagePreview from '@/components/common/ImagePreview.vue';
import type { Banner } from '@/types/dashboard/banner';

export const useColumns = () => {
	const columns: ColumnDef<Banner>[] = [
		{
			accessorKey: 'image_signed_url',
			header: 'Image',
			size: 80,
			enableSorting: false,
			cell: ({ row }) => {
				const signedUrl = row.getValue('image_signed_url') as string | null;
				return h(ImagePreview, {
					src: signedUrl,
					alt: row.original.title,
					class: "h-10 w-16 object-cover rounded"
				});
			},
		},
		{
			accessorKey: 'title',
			header: ({ column }) => {
				return h(
					Button,
					{
						variant: 'ghost',
						onClick: () =>
							column.toggleSorting(column.getIsSorted() === 'asc'),
					},
					() => ['Title', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
				);
			},
		},
		{
			accessorKey: 'event.name',
			header: 'Event',
			cell: ({ row }) => row.original.event?.name || '-',
		},
		{
			accessorKey: 'status',
			header: 'Status',
			cell: ({ row }) => {
				const banner = row.original;
				return h(BannerStatusSwitch, {
					bannerId: banner.id,
					status: banner.status,
				});
			}
		},
		{
			id: 'actions',
			size: 10,
			enableSorting: false,
			enableHiding: false,
			cell: ({ row }) => {
				const banner = row.original;
				return h(BannerActions, {
					banner,
					onEdit: (b: any) => (banner as any).onEdit?.(b),
					onSuccess: () => (banner as any).onSuccess?.(),
				});
			},
		},
	];

	return columns;
};
