import { Button } from '@/components/ui/button';
import type { Role } from '@/types/dashboard';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';
import RoleActions from './RoleActions.vue';

export const useColumns = (
  onEdit?: (role: Role) => void
) => {
  const columns: ColumnDef<Role>[] = [
    {
      accessorKey: 'name',
      header: ({ column }) => {
        return h(
          Button,
          {
            variant: 'ghost',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
          },
          () => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
        );
      },
      cell: ({ row }) => {
        // Show Label if exists, otherwise Name
        const role = row.original;
        return h('div', { class: 'flex flex-col' }, [
          h('span', { class: 'font-medium' }, role.label || role.name),
          role.label ? h('span', { class: 'text-xs text-muted-foreground' }, role.name) : null,
        ]);
      }
    },
    {
      accessorKey: 'users_count',
      header: 'Users Assigned',
      cell: ({ row }) => {
        return h('div', { class: 'pl-4' }, row.original.users_count || 0);
      },
    },
    {
      id: 'actions',
      enableHiding: false,
      cell: ({ row }) => {
        const role = row.original;
        return h(RoleActions, {
          role,
          onEdit: (r: Role) => onEdit?.(r),
        });
      },
    },
  ];

  return columns;
};
