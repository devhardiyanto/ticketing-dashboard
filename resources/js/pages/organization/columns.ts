import { Button } from '@/components/ui/button';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';
import OrganizationActions from './OrganizationActions.vue'; // We will create this
import { Badge } from '@/components/ui/badge';

// Define Organization type interface
export interface Organization {
  id: string;
  name: string;
  business_type: string;
  email: string;
  phone_number: string;
  status: 'active' | 'inactive';
}

export const useColumns = (
  options: { canDelete?: boolean; canEdit?: boolean } = {},
  openEdit?: (org: Organization) => void,
  onSuccess?: () => void
) => {
  const { canDelete = true, canEdit = true } = options;

  const columns: ColumnDef<Organization>[] = [
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
      accessorKey: 'business_type',
      header: 'Business Type',
      cell: ({ row }) => row.getValue('business_type'),
    },
    {
      accessorKey: 'email',
      header: 'Email',
      cell: ({ row }) => row.getValue('email'),
    },
    {
      accessorKey: 'phone_number',
      header: 'Phone',
      cell: ({ row }) => row.getValue('phone_number') || '-',
    },
    {
      accessorKey: 'status',
      header: 'Status',
      cell: ({ row }) => {
        const status = row.getValue('status') as string;
        return h(Badge, { variant: status === 'active' ? 'default' : 'secondary' }, () => status);
      }
    },
    {
      id: 'actions',
      enableHiding: false,
      cell: ({ row }) => {
        const organization = row.original;
        return h(OrganizationActions, {
          organization,
          canDelete,
          canEdit,
          onEdit: () => openEdit?.(organization),
          onSuccess,
        });
      },
    },
  ];

  return columns;
};
