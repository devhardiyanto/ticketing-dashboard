<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LucideIcon } from 'lucide-vue-next';

interface Props {
	title: string;
	value: number;
	icon?: LucideIcon;
	isCurrency?: boolean;
}

const props = defineProps<Props>();

const formattedValue = computed(() => {
	if (props.isCurrency) {
		return new Intl.NumberFormat('id-ID', {
			style: 'currency',
			currency: 'IDR',
			minimumFractionDigits: 0,
			maximumFractionDigits: 0,
		}).format(props.value);
	}
	return new Intl.NumberFormat('id-ID').format(props.value);
});
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
                {{ title }}
            </CardTitle>
            <component :is="icon" v-if="icon" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
            <div class="text-2xl font-bold">{{ formattedValue }}</div>
        </CardContent>
    </Card>
</template>
