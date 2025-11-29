import { Button } from '@/components/ui/button';
import type { Event } from '@/types/event';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';
import EventActions from './EventActions.vue';

export const columns: ColumnDef<Event>[] = [
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
    accessorKey: 'location',
    header: 'Location',
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
];
