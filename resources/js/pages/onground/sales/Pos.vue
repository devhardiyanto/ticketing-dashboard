<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { ShoppingCart, Plus, Minus, CreditCard, Banknote, User, Mail, Phone, ShoppingBag, Loader2, CheckCircle } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';

interface Event {
    id: string;
    name: string;
}

interface Item {
    id: string;
    title: string;
    price: number;
    description?: string;
    quantity?: number;
}

interface CartItem {
    item: Item;
    quantity: number;
}

defineProps<{
    events: Event[];
}>();

const breadcrumbs = [
    { title: 'Onground', href: '#' },
    { title: 'Sales', href: '/onground/sales' },
    { title: 'POS', href: '/onground/sales/pos' },
];

const selectedEventId = ref<string>('');
const items = ref<Item[]>([]);
const cart = ref<CartItem[]>([]);
const isLoadingItems = ref(false);
const isProcessing = ref(false);

const guest = ref({
    name: '',
    email: '',
    phone: '',
});

const paymentMethod = ref('cash');
const showSuccessDialog = ref(false);
const lastOrder = ref<any>(null);

// Fetch ticket types when event changes
watch(selectedEventId, async (newId) => {
    if (!newId) {
        items.value = [];
        return;
    }

    isLoadingItems.value = true;
    try {
        const response = await fetch(`/onground/events/${newId}/items`);
        const data = await response.json();
        if (data.success) {
            items.value = data.data;
        } else {
            toast.error('Failed to load items');
        }
    } catch (error) {
        console.error(error);
        toast.error('Network error loading items');
    } finally {
        isLoadingItems.value = false;
    }

    // Clear cart when event changes
    cart.value = [];
});

const addToCart = (product: Item) => {
    const existing = cart.value.find(cartItem => cartItem.item.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({ item: product, quantity: 1 });
    }
};

const updateQuantity = (index: number, delta: number) => {
    const item = cart.value[index];
    const newQty = item.quantity + delta;
    if (newQty <= 0) {
        cart.value.splice(index, 1);
    } else {
        item.quantity = newQty;
    }
};



const subtotal = computed(() => {
    return cart.value.reduce((sum, cartItem) => sum + (cartItem.item.price * cartItem.quantity), 0);
});

const total = computed(() => subtotal.value); // Add tax/fees if needed

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const canCheckout = computed(() => {
    return cart.value.length > 0 &&
        guest.value.name.trim().length > 0 &&
        guest.value.email.trim().length > 0 &&
        selectedEventId.value;
});

const processCheckout = async () => {
    if (!canCheckout.value) return;

    isProcessing.value = true;
    try {
        const response = await fetch('/onground/sales', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                eventId: selectedEventId.value,
                items: cart.value.map(cartItem => ({
                    itemId: cartItem.item.id,
                    quantity: cartItem.quantity
                })),
                guestName: guest.value.name,
                guestEmail: guest.value.email,
                guestPhoneNumber: guest.value.phone,
                paymentMethod: paymentMethod.value
            })
        });

        const data = await response.json();

        if (data.success) {
            lastOrder.value = data.order;
            showSuccessDialog.value = true;
            // Reset form
            cart.value = [];
            guest.value = { name: '', email: '', phone: '' };
        } else {
            toast.error(data.message || 'Checkout failed');
        }
    } catch (error) {
        console.error(error);
        toast.error('An error occurred during checkout');
    } finally {
        isProcessing.value = false;
    }
};

const printTicket = () => {
    // Placeholder for printing logic
    window.print();
};

const startNewSale = () => {
    showSuccessDialog.value = false;
    lastOrder.value = null;
};
</script>

<template>
    <Head title="POS System" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-4rem)] flex-col gap-4 p-4 lg:flex-row overflow-hidden">
            <!-- Left Panel: Selection -->
            <div class="flex flex-col flex-1 gap-4 h-full overflow-hidden">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold tracking-tight">Point of Sale</h1>
                    <div class="w-64">
                        <Select v-model="selectedEventId">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Event" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="event in events" :key="event.id" :value="event.id">
                                    {{ event.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div v-if="!selectedEventId" class="flex-1 flex items-center justify-center border-2 border-dashed rounded-lg bg-muted/50">
                    <div class="text-center">
                        <ShoppingBag class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
                        <h3 class="mt-4 text-lg font-semibold">Select an event to start selling</h3>
                        <p class="text-muted-foreground">Choose an active event from the dropdown above.</p>
                    </div>
                </div>

                <div v-else class="flex-1 overflow-y-auto pr-2">
                    <div v-if="isLoadingItems" class="flex justify-center py-12">
                        <Loader2 class="h-8 w-8 animate-spin text-primary" />
                    </div>

                    <div v-else-if="items.length === 0" class="text-center py-12 text-muted-foreground">
                        No items available for this event.
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Card v-for="product in items" :key="product.id" class="cursor-pointer hover:border-primary transition-colors" @click="addToCart(product)">
                            <CardHeader class="pb-2">
                                <CardTitle class="text-lg">{{ product.title }}</CardTitle>
                                <CardDescription class="line-clamp-2">{{ product.description || 'No description' }}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-primary">
                                    {{ formatCurrency(product.price) }}
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Button class="w-full" variant="secondary">
                                    <Plus class="mr-2 h-4 w-4" /> Add to Cart
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Cart & Checkout -->
            <Card class="w-full lg:w-[400px] flex flex-col h-full border-l shadow-lg">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ShoppingCart class="h-5 w-5" />
                        Current Order
                    </CardTitle>
                </CardHeader>

                <CardContent class="flex-1 overflow-y-auto">
                    <div v-if="cart.length === 0" class="text-center py-8 text-muted-foreground">
                        Cart is empty
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="(cartItem, index) in cart" :key="index" class="flex items-center justify-between gap-2 p-2 rounded-lg border bg-card">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ cartItem.item.title }}</div>
                                <div class="text-sm text-muted-foreground">{{ formatCurrency(cartItem.item.price) }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button variant="ghost" size="icon" class="h-8 w-8" @click="updateQuantity(index, -1)">
                                    <Minus class="h-3 w-3" />
                                </Button>
                                <span class="w-8 text-center font-medium">{{ cartItem.quantity }}</span>
                                <Button variant="ghost" size="icon" class="h-8 w-8" @click="updateQuantity(index, 1)">
                                    <Plus class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>

                <div class="p-6 bg-muted/30 border-t space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <Separator />
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span>{{ formatCurrency(total) }}</span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div class="grid gap-2">
                            <Label>Guest Details</Label>
                            <div class="relative">
                                <User class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="guest.name" placeholder="Full Name" class="pl-8" />
                            </div>
                            <div class="relative">
                                <Mail class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="guest.email" type="email" placeholder="Email Address" class="pl-8" />
                            </div>
                            <div class="relative">
                                <Phone class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="guest.phone" placeholder="Phone (Optional)" class="pl-8" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Payment Method</Label>
                            <div class="grid grid-cols-2 gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    :class="{ 'border-primary bg-primary/5': paymentMethod === 'cash' }"
                                    @click="paymentMethod = 'cash'"
                                >
                                    <Banknote class="mr-2 h-4 w-4" /> Cash
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    :class="{ 'border-primary bg-primary/5': paymentMethod === 'qris' }"
                                    @click="paymentMethod = 'qris'"
                                >
                                    <CreditCard class="mr-2 h-4 w-4" /> QRIS
                                </Button>
                            </div>
                        </div>
                    </div>

                    <Button class="w-full" size="lg" :disabled="!canCheckout || isProcessing" @click="processCheckout">
                        <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isProcessing ? 'Processing...' : 'Complete Sale' }}
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Success Dialog -->
        <Dialog :open="showSuccessDialog" @update:open="val => !val && startNewSale()">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 mb-4">
                        <CheckCircle class="h-6 w-6 text-green-600" />
                    </div>
                    <DialogTitle class="text-center">Order Confirmed!</DialogTitle>
                    <DialogDescription class="text-center">
                        Order #{{ lastOrder?.orderCode }} has been successfully created and paid.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="rounded-lg border p-4 bg-muted/50 text-center">
                        <div class="text-sm text-muted-foreground">Guest</div>
                        <div class="font-medium">{{ lastOrder?.guestName }}</div>
                        <div class="text-xs text-muted-foreground">{{ lastOrder?.guestEmail }}</div>
                    </div>

                    <div class="flex justify-center">
                        <!-- In real app, render QR codes here or provide link -->
                        <div class="text-center">
                            <p class="text-sm font-medium mb-2">Scan Ticket QR</p>
                            <div class="bg-white p-4 rounded border inline-block">
                                <img
                                    v-if="lastOrder?.items?.[0]?.tickets?.[0]?.qrPayload"
                                    :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${lastOrder.items[0].tickets[0].qrPayload}`"
                                    alt="QR Code"
                                />
                                <div v-else class="h-[150px] w-[150px] bg-gray-100 flex items-center justify-center text-xs text-muted-foreground">
                                    QR Not Available
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground mt-2" v-if="lastOrder?.items?.length > 1">
                                + {{ lastOrder.items.length - 1 }} more tickets
                            </p>
                        </div>
                    </div>
                </div>

                <DialogFooter class="sm:justify-between gap-2">
                    <Button variant="secondary" class="w-full" @click="printTicket">
                        Print Receipt
                    </Button>
                    <Button class="w-full" @click="startNewSale">
                        New Sale
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
