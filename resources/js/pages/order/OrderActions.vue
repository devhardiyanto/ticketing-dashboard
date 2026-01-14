<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { MoreHorizontal, Eye } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import type { Order } from './columns';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import orderRoute from '@/routes/order';

const props = defineProps<{
  order: Order;
}>();

const viewDetail = () => {
  router.visit(orderRoute.show(props.order.id).url);
};
</script>

<template>
  <div class="flex items-center justify-end">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
          <span class="sr-only">Open menu</span>
          <MoreHorizontal class="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuItem class="hover:cursor-pointer" @click="viewDetail">
          <Eye class="mr-2 h-4 w-4" />
          View Details
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
