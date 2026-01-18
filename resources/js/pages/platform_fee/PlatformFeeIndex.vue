<script setup lang="ts">
import ContentLayout from '@/layouts/ContentLayout.vue';
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import platformFeeRoute from '@/routes/platform_fee';

interface PlatformFeeConfig {
	id: string;
	percentage_fee: number;
	fixed_fee: number;
	is_active: boolean;
	updated_at: string | null;
}

const props = defineProps<{
	config: PlatformFeeConfig | null;
}>();

// Form setup
const form = useForm({
	percentage_fee: props.config?.percentage_fee ?? 5,
	fixed_fee: props.config?.fixed_fee ?? 2500,
	is_active: props.config?.is_active ?? true,
});

// Preview calculation
const previewSubtotal = ref(1000000); // Rp 1.000.000
const previewFee = computed(() => {
	if (!form.is_active) return 0;
	const percentageAmount = (previewSubtotal.value * form.percentage_fee) / 100;
	return Math.round(percentageAmount + form.fixed_fee);
});

const previewTotal = computed(() => {
	return previewSubtotal.value + previewFee.value;
});

// Format currency helper
const formatCurrency = (value: number) => {
	return new Intl.NumberFormat('id-ID', {
		style: 'currency',
		currency: 'IDR',
		minimumFractionDigits: 0,
	}).format(value);
};

// Submit handler
const handleSubmit = () => {
	form.put(platformFeeRoute.update().url, {
		preserveScroll: true,
		onSuccess: () => {
			toast.success('Platform fee configuration updated successfully');
		},
		onError: () => {
			toast.error('Failed to update platform fee configuration');
		}
	});
};

const breadcrumbs = computed(() => [
	{ title: 'Platform Fee', href: platformFeeRoute.index().url }
]);

import { usePermission } from '@/composables/usePermission';
const { can } = usePermission();
</script>

<template>
	<Head title="Platform Fee Configuration" />
	<ContentLayout title="Platform Fee" :breadcrumbs="breadcrumbs">
		<div class="max-w-2xl">
			<Card>
				<CardHeader>
					<CardTitle>Platform Fee Configuration</CardTitle>
					<CardDescription>
						Configure the platform fee that will be applied to each order.
						The fee is calculated as: (subtotal × percentage%) + fixed amount
					</CardDescription>
				</CardHeader>
				<CardContent>
					<form @submit.prevent="handleSubmit" class="space-y-6">
						<!-- Is Active Toggle -->
						<div class="flex items-center justify-between rounded-lg border p-4">
							<div class="space-y-0.5">
								<Label class="text-base">Enable Platform Fee</Label>
								<p class="text-sm text-muted-foreground">
									When disabled, no platform fee will be charged
								</p>
							</div>
							<Switch v-model:checked="form.is_active" />
						</div>

						<!-- Percentage Fee -->
						<div class="space-y-2">
							<Label for="percentage_fee">Percentage Fee (%)</Label>
							<div class="relative">
								<Input
									id="percentage_fee"
									type="number"
									v-model.number="form.percentage_fee"
									:disabled="!form.is_active"
									min="0"
									max="100"
									step="0.01"
									class="pr-8"
								/>
								<span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">%</span>
							</div>
							<p class="text-sm text-muted-foreground">
								Percentage of order subtotal (e.g., 5 = 5%)
							</p>
							<p v-if="form.errors.percentage_fee" class="text-sm text-destructive">
								{{ form.errors.percentage_fee }}
							</p>
						</div>

						<!-- Fixed Fee -->
						<div class="space-y-2">
							<Label for="fixed_fee">Fixed Fee (IDR)</Label>
							<div class="relative">
								<span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">Rp</span>
								<Input
									id="fixed_fee"
									type="number"
									v-model.number="form.fixed_fee"
									:disabled="!form.is_active"
									min="0"
									step="100"
									class="pl-10"
								/>
							</div>
							<p class="text-sm text-muted-foreground">
								Fixed amount added to each order (e.g., 2500 = Rp 2.500)
							</p>
							<p v-if="form.errors.fixed_fee" class="text-sm text-destructive">
								{{ form.errors.fixed_fee }}
							</p>
						</div>

						<!-- Preview Section -->
						<div class="rounded-lg border bg-muted/50 p-4 space-y-3">
							<h4 class="font-medium text-sm">Fee Preview</h4>
							<div class="space-y-2 text-sm">
								<div class="flex justify-between">
									<span class="text-muted-foreground">Example Subtotal</span>
									<span>{{ formatCurrency(previewSubtotal) }}</span>
								</div>
								<div class="flex justify-between">
									<span class="text-muted-foreground">
										Platform Fee
										<span v-if="form.is_active">({{ form.percentage_fee }}% + {{ formatCurrency(form.fixed_fee) }})</span>
									</span>
									<span :class="{ 'text-muted-foreground': !form.is_active }">
										{{ form.is_active ? formatCurrency(previewFee) : 'Disabled' }}
									</span>
								</div>
								<div class="flex justify-between font-medium border-t pt-2">
									<span>Total</span>
									<span class="text-primary">{{ formatCurrency(previewTotal) }}</span>
								</div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="flex justify-end" v-if="can('settings.update')">
							<Button type="submit" :disabled="form.processing">
								{{ form.processing ? 'Saving...' : 'Save Configuration' }}
							</Button>
						</div>
					</form>
				</CardContent>
			</Card>
		</div>
	</ContentLayout>
</template>
