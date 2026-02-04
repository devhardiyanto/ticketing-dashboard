<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Search } from 'lucide-vue-next';

interface OrderItem {
    id: string;
    ticketType: {
        name: string;
        event: {
            id: string;
            name: string;
        };
    };
}

interface Order {
    id: string;
    order_code: string;
    guest_name: string;
    guest_email: string;
    total_amount: number;
    status: string;
    created_at: string;
    user?: {
        name: string;
        email: string;
    };
    items: OrderItem[];
}

const props = defineProps<{
    orders: {
        data: Order[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
    events: { id: string; name: string }[];
    filters: {
        search?: string;
        event_id?: string;
        status?: string;
    };
}>();

const breadcrumbs = [
    { title: 'Onground', href: '#' },
    { title: 'OTS Sales', href: '/onground/sales' },
];

const search = ref(props.filters.search || '');
const eventId = ref(props.filters.event_id || 'all');
const status = ref(props.filters.status || 'all');
const isLoading = ref(false);

const updateFilters = useDebounceFn(() => {
    isLoading.value = true;
    router.get('/onground/sales', {
        search: search.value,
        event_id: eventId.value === 'all' ? null : eventId.value,
        status: status.value === 'all' ? null : status.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isLoading.value = false,
    });
}, 300);

watch([search, eventId, status], () => {
    updateFilters();
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'confirmed': return 'default'; // Success/Green
        case 'received': return 'secondary';
        case 'expired': return 'destructive';
        default: return 'outline';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getEventName = (order: Order) => {
    return order.items?.[0]?.ticketType?.event?.name || 'Unknown Event';
};
</script>

<template>
    <Head title="OTS Sales" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">On The Spot Sales</h1>
                    <p class="text-muted-foreground">Monitor sales/orders created manually or on-ground.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                    <CardDescription>Filter orders by event, status, or search.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 md:flex-row">
                        <div class="w-full md:w-1/3">
                            <Label class="mb-2 block text-sm font-medium">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="search"
                                    type="search"
                                    placeholder="Order #, Guest Name..."
                                    class="pl-8"
                                />
                            </div>
                        </div>
                        <div class="w-full md:w-1/3">
                            <Label class="mb-2 block text-sm font-medium">Event</Label>
                            <Select v-model="eventId">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Event" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Events</SelectItem>
                                    <SelectItem v-for="event in events" :key="event.id" :value="event.id">
                                        {{ event.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="w-full md:w-1/3">
                             <Label class="mb-2 block text-sm font-medium">Status</Label>
                             <Select v-model="status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="confirmed">Confirmed</SelectItem>
                                    <SelectItem value="received">Received</SelectItem>
                                    <SelectItem value="expired">Expired</SelectItem>
                                    <SelectItem value="canceled">Canceled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Order #</TableHead>
                                <TableHead>Guest</TableHead>
                                <TableHead>Event</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created By</TableHead>
                                <TableHead>Date</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="order in orders.data" :key="order.id">
                                <TableCell class="font-mono text-xs">{{ order.order_code }}</TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ order.guest_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ order.guest_email }}</div>
                                </TableCell>
                                <TableCell>{{ getEventName(order) }}</TableCell>
                                <TableCell>{{ formatCurrency(order.total_amount) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusColor(order.status)">
                                        {{ order.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div v-if="order.user" class="text-sm">
                                        {{ order.user.name }}
                                        <div class="text-xs text-muted-foreground">Admin</div>
                                    </div>
                                    <div v-else class="text-sm text-muted-foreground">Customer</div>
                                </TableCell>
                                <TableCell class="text-xs text-muted-foreground">
                                    {{ formatDate(order.created_at) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="orders.data.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                    No orders found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
                <div v-if="orders.links.length > 3" class="flex items-center justify-between px-4 py-4 border-t">
                    <div class="text-xs text-muted-foreground">
                        Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} results
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in orders.links" :key="i">
                             <div v-if="link.url === null" class="px-2 py-1 text-xs text-muted-foreground" v-html="link.label"></div>
                            <Link
                                v-else
                                :href="link.url"
                                class="px-2 py-1 text-xs rounded hover:bg-muted"
                                :class="{ 'bg-primary text-primary-foreground font-medium': link.active }"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </template>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
