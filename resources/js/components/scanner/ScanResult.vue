<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckCircle, AlertTriangle, XCircle } from 'lucide-vue-next';

interface ScanResultData {
  status: 'success' | 'duplicate' | 'invalid';
  ticketCode?: string;
  attendeeName?: string;
  ticketType?: string;
  eventName?: string;
  firstScannedAt?: string;
  reason?: string;
}

interface Props {
  result: ScanResultData | null;
}

const props = defineProps<Props>();

const statusConfig = computed(() => {
  if (!props.result) return null;

  switch (props.result.status) {
    case 'success':
      return {
        icon: CheckCircle,
        bgColor: 'bg-green-500/10',
        borderColor: 'border-green-500',
        textColor: 'text-green-700 dark:text-green-400',
        iconColor: 'text-green-500',
        title: 'Valid Ticket',
      };
    case 'duplicate':
      return {
        icon: AlertTriangle,
        bgColor: 'bg-yellow-500/10',
        borderColor: 'border-yellow-500',
        textColor: 'text-yellow-700 dark:text-yellow-400',
        iconColor: 'text-yellow-500',
        title: 'Already Scanned',
      };
    case 'invalid':
      return {
        icon: XCircle,
        bgColor: 'bg-red-500/10',
        borderColor: 'border-red-500',
        textColor: 'text-red-700 dark:text-red-400',
        iconColor: 'text-red-500',
        title: 'Invalid Ticket',
      };
    default:
      return null;
  }
});

const formatTime = (isoString?: string) => {
  if (!isoString) return '';
  return new Date(isoString).toLocaleString('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
};
</script>

<template>
	<Card
		v-if="result && statusConfig"
		:class="[
      'border-2 transition-all duration-300',
      statusConfig.bgColor,
      statusConfig.borderColor,
    ]"
	>
		<CardHeader class="pb-2">
			<div class="flex items-center gap-3">
				<component
					:is="statusConfig.icon"
					:class="['h-8 w-8', statusConfig.iconColor]"
				/>
				<CardTitle :class="statusConfig.textColor">
					{{ statusConfig.title }}
				</CardTitle>
			</div>
		</CardHeader>

		<CardContent class="space-y-2">
			<!-- Success info -->
			<template v-if="result.status === 'success'">
				<div class="grid gap-1">
					<p class="text-lg font-semibold">{{ result.attendeeName }}</p>
					<p class="text-muted-foreground text-sm">
						{{ result.ticketType }}
					</p>
					<p class="text-muted-foreground text-xs">
						{{ result.ticketCode }}
					</p>
				</div>
			</template>

			<!-- Duplicate info -->
			<template v-else-if="result.status === 'duplicate'">
				<div class="grid gap-1">
					<p class="text-sm">
						This ticket was already scanned at:
					</p>
					<p class="font-semibold">
						{{ formatTime(result.firstScannedAt) }}
					</p>
					<p class="text-muted-foreground mt-2 text-xs">
						{{ result.ticketCode }}
					</p>
				</div>
			</template>

			<!-- Invalid info -->
			<template v-else-if="result.status === 'invalid'">
				<p class="text-sm">
					{{ result.reason || 'This QR code is not valid.' }}
				</p>
			</template>
		</CardContent>
	</Card>

	<!-- Empty state -->
	<Card v-else class="border-dashed">
		<CardContent class="flex min-h-[120px] items-center justify-center">
			<p class="text-muted-foreground text-sm">
				Scan a QR code to see the result
			</p>
		</CardContent>
	</Card>
</template>
