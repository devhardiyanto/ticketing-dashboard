<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
	Field,
	FieldContent,
	FieldError,
	FieldLabel,
} from '@/components/ui/field';
import { useForm } from '@inertiajs/vue3';
import { store, update } from "@/actions/App/Http/Controllers/TicketTypeController";
import DateTimeRangePicker from '@/components/common/DateTimeRangePicker.vue';
import type { TicketType } from '@/types/dashboard';
import { toast } from 'vue-sonner';

const props = defineProps<{
	initialData?: TicketType | null;
	eventId: string | undefined;
}>();

const emit = defineEmits(['success']);

const form = useForm({
	event_id: props.eventId,
	name: props.initialData?.name || '',
	description: props.initialData?.description || '',
	price: props.initialData?.price || 0,
	quantity: props.initialData?.quantity || 0,
	start_sale_date: props.initialData?.start_sale_date || '',
	end_sale_date: props.initialData?.end_sale_date || '',
});

const submit = () => {
	if (props.initialData) {
		form.submit(update(props.initialData.id), {
			onSuccess: () => {
				emit('success');
				toast.success('Ticket type updated successfully');
			},
		})
	} else {
		form.submit(store(), {
			onSuccess: () => {
				emit('success');
				toast.success('Ticket type created successfully');
			},
		})
	}
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="space-y-4">
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
        <div class="grid grid-cols-2 gap-4">
           <Field name="price" :invalid="!!form.errors.price">
            <FieldLabel>Price <span class="text-red-500">*</span></FieldLabel>
            <FieldContent>
              <Input type="number" v-model="form.price" min="0" step="0.01" />
            </FieldContent>
            <FieldError>{{ form.errors.price }}</FieldError>
          </Field>

          <Field name="quantity" :invalid="!!form.errors.quantity">
            <FieldLabel>Quantity <span class="text-red-500">*</span></FieldLabel>
            <FieldContent>
              <Input type="number" v-model="form.quantity" min="0" />
            </FieldContent>
            <FieldError>{{ form.errors.quantity }}</FieldError>
          </Field>
        </div>
      </div>

      <div class="space-y-2">
        <DateTimeRangePicker
          v-model:startDate="form.start_sale_date"
          v-model:endDate="form.end_sale_date"
          :error="form.errors.start_sale_date || form.errors.end_sale_date"
        />
      </div>
    </div>

    <div class="flex justify-end pt-4">
      <Button type="submit" :disabled="form.processing">
        {{ initialData ? 'Update' : 'Create' }}
      </Button>
    </div>
  </form>
</template>
