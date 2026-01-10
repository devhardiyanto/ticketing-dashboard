<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { useCurrencyInput } from 'vue-currency-input'
import { watch } from 'vue'

const props = defineProps<{
  modelValue?: number
  class?: HTMLAttributes['class']
  prefix?: string
  disabled?: boolean
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: number | null): void
}>()

const { inputRef, numberValue, formattedValue, setValue } = useCurrencyInput({
  currency: 'IDR',
  locale: 'id-ID',
  currencyDisplay: 'hidden' as const,
  precision: 0,
  hideCurrencySymbolOnFocus: true,
  hideGroupingSeparatorOnFocus: false,
  valueRange: { min: 0 },
})

// Sync initial value
if (props.modelValue !== undefined) {
  setValue(props.modelValue)
}

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (newVal !== numberValue.value) {
    setValue(newVal ?? null)
  }
})

// Emit changes
watch(numberValue, (newVal) => {
  emits('update:modelValue', newVal)
})
</script>

<template>
  <div class="relative">
    <span
      v-if="prefix !== ''"
      class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none"
    >
      {{ prefix || 'Rp' }}
    </span>
    <input
      ref="inputRef"
      :value="formattedValue"
      :disabled="disabled"
      :class="cn(
        'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        prefix !== '' ? 'pl-10 pr-3' : 'px-3',
        props.class
      )"
    />
  </div>
</template>
