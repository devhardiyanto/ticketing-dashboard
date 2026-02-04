<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';

interface Props {
  title: string;
  value: number;
  total: number;
  variant?: 'default' | 'success' | 'warning';
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
});

const percentage = computed(() => {
  if (props.total === 0) return 0;
  return Math.round((props.value / props.total) * 100);
});

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'success':
      return 'text-green-600 dark:text-green-400';
    case 'warning':
      return 'text-yellow-600 dark:text-yellow-400';
    default:
      return 'text-primary';
  }
});
</script>

<template>
	<Card>
		<CardContent class="pt-6">
			<div class="flex items-center justify-between">
				<p class="text-muted-foreground text-sm font-medium">{{ title }}</p>
				<span :class="['text-2xl font-bold', variantClasses]">
					{{ value.toLocaleString() }}
				</span>
			</div>
			<div class="mt-3 space-y-1">
				<Progress :model-value="percentage" class="h-2" />
				<div class="text-muted-foreground flex justify-between text-xs">
					<span>{{ percentage }}%</span>
					<span>of {{ total.toLocaleString() }}</span>
				</div>
			</div>
		</CardContent>
	</Card>
</template>
