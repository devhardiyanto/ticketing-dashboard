<script setup lang="ts">
import {
	Select,
	SelectContent,
	SelectGroup,
	SelectItem,
	SelectLabel,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select'
import type { PropType } from 'vue';

interface ComboboxItem {
	id: string | number;
	name: string;
	url?: string;
	[key: string]: any;
}

const props = defineProps({
	modelValue: {
		type: [String, Number] as PropType<string | number | null>,
		default: null,
	},
	autoNavigate: {
		type: Boolean,
		default: false,
	},
	label: {
		type: String,
		default: null,
	},
	items: {
		type: Array as PropType<ComboboxItem[]>,
		default: () => [],
	},
});

const emit = defineEmits(['update:modelValue']);

import { router } from '@inertiajs/vue3';

const handleValueChange = (value: any) => {
	emit('update:modelValue', value);

	if (!value) return;

	if (props.autoNavigate) {
		const item = props.items.find((i) => i.id.toString() === value);
		if (item && item.url) {
			router.visit(item.url);
		}
	}
};
</script>

<template>
  <Select :model-value="modelValue?.toString()" @update:modelValue="handleValueChange">
    <SelectTrigger class="w-[180px]">
      <SelectValue :placeholder="`Select a ${label}`" />
    </SelectTrigger>
    <SelectContent>
      <SelectGroup>
        <SelectLabel v-if="label">{{ label }}</SelectLabel>
        <SelectItem v-for="item in items" :key="item.id" :value="item.id.toString()">
          {{ item.name }}
        </SelectItem>
      </SelectGroup>
    </SelectContent>
  </Select>
</template>
