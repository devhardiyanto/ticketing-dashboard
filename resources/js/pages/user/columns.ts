import { Button } from '@/components/ui/button';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';
import UserActions from './UserActions.vue';
import UserStatusSwitch from './UserStatusSwitch.vue';
import { formatRoleName } from '@/lib/utils-general';

// Define User type interface temporarily or import
export interface User {
	id: string;
	name: string;
	email: string;
	organization?: { id: string; name: string };
	roles?: Roles[];
	status: 'active' | 'inactive';
}

export interface Roles {
	id: number
	name: string
	guard_name: string
	created_at: string
	updated_at: string
	pivot: Pivot
}

export interface Pivot {
	model_type: string
	model_id: number
	role_id: number
}


export const useColumns = (
	options: { hideOrganization?: boolean; canDelete?: boolean; canEdit?: boolean } = {},
	openEdit?: (user: User) => void,
	onSuccess?: () => void
) => {
	const { hideOrganization = false, canDelete = true, canEdit = true } = options;

	const columns: ColumnDef<User>[] = [
		{
			accessorKey: 'name',
			header: ({ column }) => {
				return h(
					Button,
					{
						variant: 'ghost',
						onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
					},
					() => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]
				);
			},
			cell: ({ row }) => row.getValue('name'),
		},
		{
			accessorKey: 'email',
			header: 'Email',
			cell: ({ row }) => row.getValue('email'),
		},
		// Conditionally add Organization column
		...(hideOrganization ? [] : [{
			accessorKey: 'organization.name',
			header: 'Organization',
			cell: ({ row }: { row: { original: User } }) => row.original.organization?.name || '-',
		} as ColumnDef<User>]),
		{
			accessorKey: 'role.display_name',
			header: 'Role',
			cell: ({ row }) => formatRoleName(row.original.roles?.[0].name || '') || '-',
		},
		{
			accessorKey: 'status',
			header: 'Status',
			cell: ({ row }) => {
				const user = row.original;
				return h(UserStatusSwitch, {
					userId: user.id,
					status: user.status,
					onSuccess,
					disabled: !canEdit
				});
			},
		},
		{
			id: 'actions',
			enableHiding: false,
			cell: ({ row }) => {
				const user = row.original;
				return h(UserActions, {
					user,
					canDelete,
					canEdit,
					onEdit: () => openEdit?.(user),
					onSuccess,
				});
			},
		},
	];

	return columns;
};
