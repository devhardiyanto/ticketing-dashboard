<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import { useDebounceFn } from '@vueuse/core';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
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
import { toast } from 'vue-sonner';
import { Search } from 'lucide-vue-next';

interface Attendee {
    id: string;
    order_id: string;
    ticket_code: string;
    attendee_name: string;
    status: string;
    ticketType: {
        name: string;
        event: {
            id: string;
            name: string;
        }
    };
    order: {
        guest_name: string;
        guest_email: string;
    };
}

const props = defineProps<{
    attendees: {
        data: Attendee[];
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
    { title: 'Events', href: '/events' },
    { title: 'Attendees', href: '/attendees' },
];

const search = ref(props.filters.search || '');
const eventId = ref(props.filters.event_id || 'all');
const status = ref(props.filters.status || 'all');
const isLoading = ref(false);

const updateFilters = useDebounceFn(() => {
    isLoading.value = true;
    router.get('/attendees', {
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

// Realtime Updates
if (props.filters.event_id) {
    useEchoPublic(
        `scanner.${props.filters.event_id}`,
        '.ticket.scanned',
        (event: any) => {
            // Find attendee in list
            const attendee = props.attendees.data.find(a => a.ticket_code === event.ticketCode);
            if (attendee) {
                attendee.status = event.status === 'success' ? 'checkedin' : attendee.status;
                toast.success(`${attendee.attendee_name} checked in!`);
            }
        }
    );
}

const getStatusColor = (status: string) => {
    switch (status) {
        case 'checkedin': return 'default';
        case 'received': return 'secondary';
        default: return 'outline';
    }
};

const checkIn = (id: string, name: string) => {
    router.post(`/attendees/${id}/check-in`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success(`${name} checked in`),
        onError: () => toast.error('Failed to check in'),
    });
};
</script>

<template>
    <Head title="Attendees" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Attendees</h1>
                    <p class="text-muted-foreground">Manage event attendees and monitor check-ins.</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                    <CardDescription>Filter attendees by event, status, or search.</CardDescription>
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
                                    placeholder="Search name, ticket code..."
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
                                    <SelectItem value="received">Not Checked In</SelectItem>
                                    <SelectItem value="checkedin">Checked In</SelectItem>
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
                                <TableHead>Ticket Code</TableHead>
                                <TableHead>Attendee</TableHead>
                                <TableHead>Event</TableHead>
                                <TableHead>Ticket Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="attendee in attendees.data" :key="attendee.id">
                                <TableCell class="font-mono text-xs">{{ attendee.ticket_code }}</TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ attendee.attendee_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ attendee.order?.guest_email }}</div>
                                </TableCell>
                                <TableCell>{{ attendee.ticketType?.event?.name }}</TableCell>
                                <TableCell>{{ attendee.ticketType?.name }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusColor(attendee.status)">
                                        {{ attendee.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button
                                        v-if="attendee.status !== 'checkedin'"
                                        size="xs"
                                        variant="outline"
                                        @click="checkIn(attendee.id, attendee.attendee_name)"
                                    >
                                        Check In
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="attendees.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No attendees found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
                <div v-if="attendees.links.length > 3" class="flex items-center justify-between px-4 py-4 border-t">
                    <div class="text-xs text-muted-foreground">
                        Showing {{ attendees.from }} to {{ attendees.to }} of {{ attendees.total }} results
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in attendees.links" :key="i">
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
