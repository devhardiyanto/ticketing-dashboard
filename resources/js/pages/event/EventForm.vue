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
import { useForm, usePage } from '@inertiajs/vue3';
import { store, update } from "@/actions/App/Http/Controllers/EventController";
import DateTimeRangePicker from '@/components/common/DateTimeRangePicker.vue';
import { useTimezones } from '@/composables/useTimezones';
import type { Event, Organization } from '@/types/dashboard';

const props = defineProps<{
  initialData?: Event | null;
  organizations: Organization[];
}>();

const emit = defineEmits(['success']);

const { timezones } = useTimezones();

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString); // VueDatePicker works best with Date objects
};

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
  name: props.initialData?.name || '',
  description: props.initialData?.description || '',
  start_date: props.initialData?.start_date ? formatDate(props.initialData.start_date) : '',
  end_date: props.initialData?.end_date ? formatDate(props.initialData.end_date) : '',
  location: props.initialData?.location || '',
  organization_id: props.initialData?.organization_id || '',
  timezone: 'UTC', // Default to UTC or infer from browser
});

const submit = () => {
  if (props.initialData) {
    form.submit(update(props.initialData.id), {
      onSuccess: () => emit('success'),
    })
  } else {
    form.submit(store(), {
      onSuccess: () => emit('success'),
    })
  }
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="space-y-2">
      <Field name="name" :invalid="!!form.errors.name">
        <FieldLabel>Name <span class="text-red-500">*</span></FieldLabel>
        <FieldContent>
            <Input v-model="form.name" />
        </FieldContent>
        <FieldError>{{ form.errors.name }}</FieldError>
      </Field>
    </div>

    <div class="space-y-2">
      <Field name="description" :invalid="!!form.errors.description">
        <FieldLabel>Description</FieldLabel>
        <FieldContent>
          <Input v-model="form.description" />
        </FieldContent>
        <FieldError>{{ form.errors.description }}</FieldError>
      </Field>
    </div>

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

    <div class="space-y-2" v-if="!user?.organization_id && organizations?.length > 0">
      <FieldLabel>Organization</FieldLabel>
      <Select v-model="form.organization_id">
        <SelectTrigger>
          <SelectValue placeholder="Select Organization" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem v-for="org in organizations" :key="org.id" :value="org.id">
            {{ org.name }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>

    <div class="space-y-2">
      <DateTimeRangePicker
        v-model:startDate="form.start_date"
        v-model:endDate="form.end_date"
        :timezone="form.timezone"
        :error="form.errors.start_date || form.errors.end_date"
      />
    </div>

    <div class="space-y-2">
      <Field name="location" :invalid="!!form.errors.location">
        <FieldLabel>Location <span class="text-red-500">*</span></FieldLabel>
        <FieldContent>
          <Input v-model="form.location" />
        </FieldContent>
        <FieldError>{{ form.errors.location }}</FieldError>
      </Field>
    </div>

    <div class="flex justify-end">
      <Button type="submit" :disabled="form.processing">
        {{ initialData ? 'Update' : 'Create' }}
      </Button>
    </div>
  </form>
</template>
