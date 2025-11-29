import { Button } from '@/components/ui/button';
import type { Event } from '@/types/dashboard/event';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { h } from 'vue';
import EventActions from './EventActions.vue';

const page = usePage();
const user = page.props.auth.user;

let base_columns: ColumnDef<Event>[] = [
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
        onEdit: (e: Event) => (event as any).onEdit?.(e),
      });
    },
  },
]

base_columns = !user.organization_id ? base_columns : base_columns.filter((column) => column.header !== 'Organization');
console.log('base_columns', base_columns)
export const columns: ColumnDef<Event>[] = [
  ...base_columns
];
