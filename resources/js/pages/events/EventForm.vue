<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Field,
  FieldContent,
  FieldError,
  FieldLabel,
} from '@/components/ui/field';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import type { Event } from '@/types/event';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import DateTimeRangePicker from '@/components/common/DateTimeRangePicker.vue';
import { useTimezones } from '@/composables/useTimezones';
import { onMounted } from 'vue';

const props = defineProps<{
  initialData?: Event | null;
}>();

const emit = defineEmits(['success']);

const { timezones, fetchTimezones } = useTimezones();

onMounted(() => {
  fetchTimezones();
});

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString); // VueDatePicker works best with Date objects
};

const form = useForm({
  name: props.initialData?.name || '',
  description: props.initialData?.description || '',
  start_date: props.initialData?.start_date ? formatDate(props.initialData.start_date) : '',
  end_date: props.initialData?.end_date ? formatDate(props.initialData.end_date) : '',
  location: props.initialData?.location || '',
  organization_id: props.initialData?.organization_id || '1',
  timezone: 'UTC', // Default to UTC or infer from browser
});

const submit = () => {
  if (props.initialData) {
    form.put(route('events.update', props.initialData.id), {
      onSuccess: () => emit('success'),
    });
  } else {
    form.post(route('events.store'), {
      onSuccess: () => emit('success'),
    });
  }
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <Field name="name" :invalid="!!form.errors.name">
      <FieldLabel>Name</FieldLabel>
      <FieldContent>
          <Input v-model="form.name" />
      </FieldContent>
      <FieldError>{{ form.errors.name }}</FieldError>
    </Field>

    <Field name="description" :invalid="!!form.errors.description">
      <FieldLabel>Description</FieldLabel>
      <FieldContent>
        <Input v-model="form.description" />
      </FieldContent>
      <FieldError>{{ form.errors.description }}</FieldError>
    </Field>

    <div class="space-y-2">
      <FieldLabel>Timezone</FieldLabel>
      <Select v-model="form.timezone">
        <SelectTrigger>
          <SelectValue placeholder="Select Timezone" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem v-for="tz in timezones" :key="tz" :value="tz">
            {{ tz }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>

    <DateTimeRangePicker
        v-model:startDate="form.start_date"
        v-model:endDate="form.end_date"
        :timezone="form.timezone"
        :error="form.errors.start_date || form.errors.end_date"
    />

    <Field name="location" :invalid="!!form.errors.location">
      <FieldLabel>Location</FieldLabel>
      <FieldContent>
          <Input v-model="form.location" />
      </FieldContent>
      <FieldError>{{ form.errors.location }}</FieldError>
    </Field>

    <div class="flex justify-end">
      <Button type="submit" :disabled="form.processing">
        {{ initialData ? 'Update' : 'Create' }}
      </Button>
    </div>
  </form>
</template>
