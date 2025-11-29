<script setup lang="ts">
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { computed } from 'vue';
import { Label } from '@/components/ui/label';

const props = defineProps<{
  startDate?: string | Date | null;
  endDate?: string | Date | null;
  timezone?: string;
  error?: string;
}>();

const emit = defineEmits(['update:startDate', 'update:endDate']);

const start = computed({
  get: () => props.startDate,
  set: (val) => emit('update:startDate', val),
});

const end = computed({
  get: () => props.endDate,
  set: (val) => emit('update:endDate', val),
});

const format = "yyyy-MM-dd HH:mm";
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
      <Label>Start Date</Label>
      <VueDatePicker
        v-model="start"
        :timezone="timezone"
        :format="format"
        auto-apply
        :enable-time-picker="true"
        placeholder="Select start date"
        class="dp-custom border rounded-md"
        :time-config="{ timePickerInline: true }"
        :dark="true"
      />
    </div>
    <div class="space-y-2">
      <Label>End Date</Label>
      <VueDatePicker
        v-model="end"
        :timezone="timezone"
        :format="format"
        auto-apply
        :enable-time-picker="true"
        :min-date="start || undefined"
        placeholder="Select end date"
        class="dp-custom border rounded-md"
        :time-config="{ timePickerInline: true }"
        :dark="true"
      />
    </div>
  </div>
  <p v-if="error" class="text-sm text-red-500 mt-1">{{ error }}</p>
</template>

<style>
.dp-custom {
  --dp-font-family: inherit;
  --dp-border-radius: 0.375rem;
  /* sm:rounded-lg match */
  --dp-input-padding: 0.5rem 0.75rem;
  --dp-font-size: 0.875rem;
  /* text-sm */

  /* Shadcn-like colors (approximate, adjust as needed) */
  --dp-background-color: hsl(var(--background));
  --dp-text-color: hsl(var(--foreground));
  --dp-hover-color: hsl(var(--muted));
  --dp-hover-text-color: hsl(var(--foreground));
  --dp-hover-icon-color: hsl(var(--foreground));
  --dp-primary-color: hsl(var(--primary));
  --dp-primary-text-color: hsl(var(--primary-foreground));
  --dp-secondary-color: hsl(var(--muted));
  --dp-border-color: hsl(var(--input));
  --dp-menu-border-color: hsl(var(--border));
  --dp-border-color-hover: hsl(var(--ring));
  --dp-disabled-color: hsl(var(--muted));
  --dp-scroll-bar-background: hsl(var(--muted));
  --dp-scroll-bar-color: hsl(var(--muted-foreground));
  --dp-success-color: hsl(var(--primary));
  --dp-success-color-disabled: hsl(var(--muted));
  --dp-icon-color: hsl(var(--muted-foreground));
  --dp-danger-color: hsl(var(--destructive));
  --dp-marker-color: hsl(var(--primary));
  --dp-tooltip-color: hsl(var(--popover));
}

/* Dark mode handling if class="dark" is on html/body */
.dark .dp-custom {
  --dp-background-color: hsl(var(--background));
  --dp-text-color: hsl(var(--foreground));
  --dp-border-color: hsl(var(--input));
  --dp-menu-border-color: hsl(var(--border));
}
</style>
