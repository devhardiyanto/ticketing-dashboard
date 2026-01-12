<script setup lang="ts">
import ContentLayout from '@/layouts/ContentLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { Order, OrderItem } from './columns';
import type { BreadcrumbItem } from '@/types';
import orderRoutes from '@/routes/order';

const props = defineProps<{
	order: Order & { items: OrderItem[] };
}>();

const statusColors: Record<string, string> = {
	reserved: 'bg-yellow-100 text-yellow-800 border-yellow-300',
	pending_payment: 'bg-orange-100 text-orange-800 border-orange-300',
	paid: 'bg-green-100 text-green-800 border-green-300',
	cancelled: 'bg-red-100 text-red-800 border-red-300',
	expired: 'bg-gray-100 text-gray-800 border-gray-300',
	refunded: 'bg-purple-100 text-purple-800 border-purple-300',
};

const formatCurrency = (amount: number | string) => {
	const value = typeof amount === 'string' ? parseFloat(amount) : amount;
	return new Intl.NumberFormat('id-ID', {
		style: 'currency',
		currency: 'IDR',
		minimumFractionDigits: 0,
	}).format(value);
};

const formatDate = (dateStr: string | null) => {
	if (!dateStr) return '-';
	return new Date(dateStr).toLocaleDateString('id-ID', {
		day: '2-digit',
		month: 'long',
		year: 'numeric',
		hour: '2-digit',
		minute: '2-digit',
	});
};

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Orders',
		href: orderRoutes.index().url,
	},
	{
		title: props.order.order_code,
		href: orderRoutes.show(props.order.id).url,
	},
];
</script>

<template>
  <ContentLayout title="Order Details" :breadcrumbs="breadcrumbs">
    <div class="grid gap-6">
      <!-- Order Info Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="text-xl">{{ props.order.order_code }}</CardTitle>
            <Badge
              :class="statusColors[props.order.status] || 'bg-gray-100'"
              variant="outline"
            >
              {{ props.order.status.replace('_', ' ') }}
            </Badge>
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-500">Guest Name</label>
                <p class="text-sm">{{ props.order.guest_name || '-' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Guest Email</label>
                <p class="text-sm">{{ props.order.guest_email || '-' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Event</label>
                <p class="text-sm">{{ props.order.event_name || 'N/A' }}</p>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-500">Total Amount</label>
                <p class="text-lg font-semibold text-green-600">
                  {{ formatCurrency(props.order.total_amount) }}
                </p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Platform Fee</label>
                <p class="text-sm">{{ formatCurrency(props.order.platform_fee_amount || 0) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Payment Method</label>
                <p class="text-sm capitalize">{{ props.order.payment_method || '-' }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t grid gap-4 md:grid-cols-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Created At</label>
              <p class="text-sm">{{ formatDate(props.order.created_at) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Expires At</label>
              <p class="text-sm">{{ formatDate(props.order.expires_at) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Payment Deadline</label>
              <p class="text-sm">{{ formatDate(props.order.payment_deadline) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Order Items Table -->
      <Card>
        <CardHeader>
          <CardTitle>Order Items</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-3 px-2">Ticket Type</th>
                  <th class="text-center py-3 px-2">Qty</th>
                  <th class="text-right py-3 px-2">Price</th>
                  <th class="text-right py-3 px-2">Subtotal</th>
                  <th class="text-left py-3 px-2">Ticket Code</th>
                  <th class="text-left py-3 px-2">Attendee</th>
                  <th class="text-center py-3 px-2">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.order.items" :key="item.id" class="border-b hover:bg-gray-50">
                  <td class="py-3 px-2">{{ item.ticket_type?.name || 'N/A' }}</td>
                  <td class="py-3 px-2 text-center">{{ item.quantity }}</td>
                  <td class="py-3 px-2 text-right">{{ formatCurrency(item.price_per_ticket) }}</td>
                  <td class="py-3 px-2 text-right font-medium">{{ formatCurrency(item.subtotal) }}</td>
                  <td class="py-3 px-2 font-mono text-xs">{{ item.ticket_code || '-' }}</td>
                  <td class="py-3 px-2">
                    <div v-if="item.attendee_name || item.attendee_email">
                      <p class="text-sm">{{ item.attendee_name || '-' }}</p>
                      <p class="text-xs text-gray-500">{{ item.attendee_email || '-' }}</p>
                    </div>
                    <span v-else>-</span>
                  </td>
                  <td class="py-3 px-2 text-center">
                    <Badge variant="outline" class="text-xs">{{ item.status }}</Badge>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  </ContentLayout>
</template>
