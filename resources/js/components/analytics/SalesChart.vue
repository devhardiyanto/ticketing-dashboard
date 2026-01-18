<script setup lang="ts">
import { ref, computed } from 'vue'
import { VisAxis, VisGroupedBar, VisXYContainer, VisCrosshair } from '@unovis/vue'
import {
	Card,
	CardContent,
	CardDescription,
	CardHeader,
	CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { ChartContainer, ChartTooltipContent, componentToString } from '@/components/ui/chart'
import { DaySalesData } from '@/types/analytics';

interface Props {
	data: DaySalesData[];
}

const props = defineProps<Props>();

// Map our data to the chart format
const chartData = computed(() => props.data);

// Define ChartConfig locally since it's missing export
type ChartConfig = Record<string, { label: string; color?: string }>;

const chartConfig: ChartConfig = {
	views: {
		label: "Overview",
		color: undefined,
	},
	total_revenue: {
		label: "Total Revenue",
		color: "hsl(var(--primary))",
	},
	total_sold: {
		label: "Total Sold",
		color: "hsl(var(--destructive))",
	},
}

const activeChart = ref<"total_revenue" | "total_sold">("total_revenue");

const total = computed(() => ({
	total_revenue: chartData.value.reduce((acc, curr) => acc + Number(curr.total_revenue), 0),
	total_sold: chartData.value.reduce((acc, curr) => acc + Number(curr.total_sold), 0),
}))

const formatCurrency = (value: number) => {
	return new Intl.NumberFormat('id-ID', {
		style: 'currency',
		currency: 'IDR',
		maximumFractionDigits: 0,
	}).format(value);
}

const formatNumber = (value: number) => {
	return new Intl.NumberFormat('id-ID').format(value);
}
</script>

<template>
  <Card class="py-4 sm:py-0">
    <CardHeader class="flex flex-col items-stretch border-b p-0! sm:flex-row">
      <div class="flex flex-1 flex-col justify-center gap-1 px-6 py-5 sm:py-6">
        <CardTitle>Sales Trend</CardTitle>
        <CardDescription>
          Daily sales performance for the event
        </CardDescription>
      </div>
      <div class="flex">
        <Button
          v-for="chart in ['total_revenue', 'total_sold'] as const"
          :key="chart"
          variant="ghost"
          :data-active="activeChart === chart"
          class="flex h-auto flex-1 flex-col justify-center gap-1 rounded-none border-t px-6 py-4 text-left data-[active=true]:bg-muted/50 even:border-l sm:border-l sm:border-t-0 sm:px-8 sm:py-6"
          @click="activeChart = chart"
        >
          <span class="text-xs text-muted-foreground">
            {{ chartConfig[chart].label }}
          </span>
          <span class="text-lg font-bold leading-none sm:text-xl">
             <template v-if="chart === 'total_revenue'">
                 {{ formatCurrency(total[chart]) }}
             </template>
             <template v-else>
                 {{ formatNumber(total[chart]) }}
             </template>
          </span>
        </Button>
      </div>
    </CardHeader>
    <CardContent class="px-2 sm:p-6">
      <ChartContainer :config="chartConfig" class="aspect-auto h-[250px] w-full">
        <VisXYContainer
          :data="chartData"
          :margin="{ left: 0, right: 0 }"
           :y-domain="[0, undefined]"
        >
          <VisGroupedBar
            :x="(d: DaySalesData) => new Date(d.date).getTime()"
            :y="(d: DaySalesData) => d[activeChart] as number"
            :color="chartConfig[activeChart].color"
            :bar-padding="0.1"
            :rounded-corners="4"
          />
          <VisAxis
            type="x"
            :x="(d: DaySalesData) => new Date(d.date).getTime()"
            :tick-line="false"
            :domain-line="false"
            :grid-line="false"
            :tick-format="(d: number) => {
							return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
						}"
          />
           <VisAxis
            type="y"
             :num-ticks="3"
            :tick-line="false"
             :domain-line="false"
             :grid-line="false"
              :tick-format="(d: number) => {
								if (activeChart === 'total_revenue') {
									return new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(d);
								}
								return d;
							}"
          />

          <VisCrosshair
            :template="componentToString(chartConfig, ChartTooltipContent, {
							labelFormatter(d) {
								return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })
							},
						})"
            color="rgba(0,0,0,0)"
          />
        </VisXYContainer>
      </ChartContainer>
    </CardContent>
  </Card>
</template>
