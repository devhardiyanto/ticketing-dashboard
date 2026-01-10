<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import {
	Field,
	FieldContent,
	FieldError,
	FieldLabel,
} from '@/components/ui/field';
import { useForm } from '@inertiajs/vue3';
import { store, update } from "@/actions/App/Http/Controllers/TicketTypeController";
import DateTimeRangePicker from '@/components/common/DateTimeRangePicker.vue';
import QuillEditor from '@/components/common/QuillEditor.vue';
import CurrencyInput from '@/components/common/CurrencyInput.vue';
import NumberInput from '@/components/common/NumberInput.vue';
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
	category: props.initialData?.category || '',
	type: props.initialData?.type || 'PAID',
	description: props.initialData?.description || '',
	price: props.initialData?.price || 0,
	quantity: props.initialData?.quantity || 0,
	stock_adjustment: '',
	start_sale_date: props.initialData?.start_sale_date || '',
	end_sale_date: props.initialData?.end_sale_date || '',
	is_hidden: props.initialData?.is_hidden || false,
	inventory_status: props.initialData?.inventory_status ?? 0,
	sort_order: props.initialData?.sort_order || 0,
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
        <Field name="category" :invalid="!!form.errors.category">
          <FieldLabel>Category</FieldLabel>
          <FieldContent>
              <Input v-model="form.category" placeholder="e.g. General Admission, Free Entry, VIP" />
          </FieldContent>
          <FieldError>{{ form.errors.category }}</FieldError>
        </Field>
      </div>

      <div class="space-y-2">
        <Field name="description" :invalid="!!form.errors.description">
          <FieldLabel>Description</FieldLabel>
          <FieldContent>
            <QuillEditor v-model="form.description" :height="150" />
          </FieldContent>
          <FieldError>{{ form.errors.description }}</FieldError>
        </Field>
      </div>

      <div class="space-y-2">
        <FieldLabel>Ticket Type</FieldLabel>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <label
            class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-neutral-50"
            :class="{ 'border-primary-500 bg-primary-50 ring-1 ring-primary-500': form.type === 'PAID' }"
          >
            <input type="radio" v-model="form.type" value="PAID" class="w-4 h-4 accent-primary-600">
            <div>
              <div class="font-medium text-sm">Berbayar</div>
              <div class="text-xs text-neutral-500">Tiket dengan harga tetap</div>
            </div>
          </label>

          <label
            class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-neutral-50"
            :class="{ 'border-primary-500 bg-primary-50 ring-1 ring-primary-500': form.type === 'FREE' }"
          >
            <input type="radio" v-model="form.type" value="FREE" @change="form.price = 0" class="w-4 h-4 accent-primary-600">
            <div>
              <div class="font-medium text-sm">Gratis</div>
              <div class="text-xs text-neutral-500">Tiket tanpa biaya (Rp 0)</div>
            </div>
          </label>

            <label
            class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-neutral-50"
            :class="{ 'border-primary-500 bg-primary-50 ring-1 ring-primary-500': form.type === 'DONATION' }"
          >
            <input type="radio" v-model="form.type" value="DONATION" class="w-4 h-4 accent-primary-600">
            <div>
              <div class="font-medium text-sm">Donasi</div>
              <div class="text-xs text-neutral-500">Harga nominal sukarela</div>
            </div>
          </label>
        </div>
      </div>

      <div class="space-y-2">
        <div class="grid grid-cols-2 gap-4">
           <Field name="price" :invalid="!!form.errors.price" v-if="form.type !== 'FREE'">
            <FieldLabel>{{ form.type === 'DONATION' ? 'Minimum Donation' : 'Price' }} <span class="text-red-500">*</span></FieldLabel>
            <FieldContent>
              <CurrencyInput v-model="form.price" />
            </FieldContent>
            <FieldError>{{ form.errors.price }}</FieldError>
          </Field>

          <Field v-if="!initialData" name="quantity" :invalid="!!form.errors.quantity">
            <FieldLabel>Quantity <span class="text-red-500">*</span></FieldLabel>
            <FieldContent>
              <NumberInput v-model="form.quantity" :min="0" />
            </FieldContent>
            <FieldError>{{ form.errors.quantity }}</FieldError>
          </Field>

          <Field v-else name="quantity" :invalid="!!form.errors.quantity">
            <FieldLabel>Quantity</FieldLabel>
            <FieldContent>
              <Input type="number" v-model="form.quantity" min="0" disabled />
            </FieldContent>
            <FieldError>{{ form.errors.quantity }}</FieldError>
          </Field>
        </div>
      </div>

		<div v-if="initialData" class="space-y-2">
			<Field name="stock_adjustment" :invalid="!!form.errors.stock_adjustment">
				<FieldLabel>Stock Adjustment</FieldLabel>
				<FieldContent>
					<Input
						type="number"
						v-model="form.stock_adjustment"
						placeholder="e.g. 10 or -5"
					/>
				</FieldContent>
				<FieldError>{{ form.errors.stock_adjustment }}</FieldError>
			</Field>
		</div>

      <div class="space-y-2">
        <DateTimeRangePicker
          v-model:startDate="form.start_sale_date"
          v-model:endDate="form.end_sale_date"
          :error="form.errors.start_sale_date || form.errors.end_sale_date"
        />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <Field name="sort_order" :invalid="!!form.errors.sort_order">
          <FieldLabel>Sort Order</FieldLabel>
          <FieldContent>
            <NumberInput v-model="form.sort_order" :min="0" />
          </FieldContent>
          <FieldError>{{ form.errors.sort_order }}</FieldError>
        </Field>

        <Field name="is_hidden" :invalid="!!form.errors.is_hidden">
          <FieldLabel>Visibility</FieldLabel>
          <FieldContent>
            <div class="flex items-center gap-2 h-9">
              <Checkbox v-model:checked="form.is_hidden" id="is_hidden" />
              <label for="is_hidden" class="text-sm">Hidden (tidak tampil di public)</label>
            </div>
          </FieldContent>
          <FieldError>{{ form.errors.is_hidden }}</FieldError>
        </Field>
      </div>

      <div class="space-y-2">
        <Field name="inventory_status" :invalid="!!form.errors.inventory_status">
          <FieldLabel>Status Ketersediaan (Gimmick)</FieldLabel>
          <FieldContent>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <label
                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-neutral-50"
                :class="{ 'border-primary-500 bg-primary-50 ring-1 ring-primary-500': form.inventory_status === 0 }"
              >
                <input type="radio" v-model="form.inventory_status" :value="0" class="w-4 h-4 accent-primary-600">
                <div>
                  <div class="font-medium text-sm">Default</div>
                  <div class="text-xs text-neutral-500">Sesuai stok asli</div>
                </div>
              </label>

              <label
                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-neutral-50"
                :class="{ 'border-warning-500 bg-warning-50 ring-1 ring-warning-500': form.inventory_status === 1 }"
              >
                <input type="radio" v-model="form.inventory_status" :value="1" class="w-4 h-4 accent-warning-600">
                <div>
                  <div class="font-medium text-sm">Hampir Habis</div>
                  <div class="text-xs text-neutral-500">Tampilkan badge "Hampir Habis"</div>
                </div>
              </label>

              <label
                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-neutral-50"
                :class="{ 'border-red-500 bg-red-50 ring-1 ring-red-500': form.inventory_status === 2 }"
              >
                <input type="radio" v-model="form.inventory_status" :value="2" class="w-4 h-4 accent-red-600">
                <div>
                  <div class="font-medium text-sm">Sold Out</div>
                  <div class="text-xs text-neutral-500">Paksa tampil sebagai habis</div>
                </div>
              </label>
            </div>
          </FieldContent>
          <FieldError>{{ form.errors.inventory_status }}</FieldError>
        </Field>
      </div>
    </div>

    <div class="flex justify-end pt-4">
      <Button type="submit" :disabled="form.processing">
        {{ initialData ? 'Update' : 'Create' }}
      </Button>
    </div>
  </form>
</template>
