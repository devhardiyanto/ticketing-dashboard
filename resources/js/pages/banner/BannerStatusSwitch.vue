<script setup lang="ts">
import { Switch } from '@/components/ui/switch';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import bannerRoute from '@/routes/banner';

const props = defineProps<{
	bannerId: string | number;
	status: string;
}>();

// State management
const isChecked = ref(props.status === 'active');
const isLoading = ref(false); // Biar user ga spam klik

// Sync kalau data table refresh
watch(() => props.status, (newVal) => {
	isChecked.value = newVal === 'active';
});

const form = useForm({});

// Handler logic
const onCheckedChange = (checked: boolean) => {
	// Guard clause: Kalau lagi loading, ignore click
	if (isLoading.value) return;

	// 1. Optimistic Update
	const previousState = isChecked.value;
	isChecked.value = checked;
	isLoading.value = true;

	// 2. Hit API
	form.patch(bannerRoute.toggle(props.bannerId).url, {
		preserveScroll: true,
		onSuccess: () => {
			isLoading.value = false;
			toast.success(`Banner ${checked ? 'activated' : 'deactivated'}`);
		},
		onError: () => {
			// 3. Rollback
			isChecked.value = previousState;
			isLoading.value = false;
			toast.error('Failed to update status');
		}
	});
};
</script>

<template>
  <div class="flex items-center space-x-2" @click.stop>
    <Switch
      :model-value="isChecked"
      :disabled="isLoading || form.processing"
      @update:model-value="onCheckedChange"
    />
  </div>
</template>
