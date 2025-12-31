<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { ref, watch } from 'vue'

const props = defineProps<{
  modelValue?: number
  class?: HTMLAttributes['class']
  min?: number
  max?: number
  disabled?: boolean
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: number): void
}>()

// Format number with thousands separator (Indonesian locale: 1.000)
const formatNumber = (value: number | undefined): string => {
  if (value === undefined || value === null || isNaN(value)) return ''
  return value.toLocaleString('id-ID')
}

// Parse formatted string back to number
const parseNumber = (value: string): number => {
  const cleaned = value.replace(/\./g, '').replace(/,/g, '')
  const parsed = parseInt(cleaned, 10)
  return isNaN(parsed) ? 0 : parsed
}

const displayValue = ref(formatNumber(props.modelValue))

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  const formatted = formatNumber(newVal)
  if (parseNumber(displayValue.value) !== newVal) {
    displayValue.value = formatted
  }
})

const handleInput = (event: Event) => {
  const input = event.target as HTMLInputElement
  const cursorPos = input.selectionStart || 0
  const oldLength = input.value.length

  // Only allow digits
  const numericValue = input.value.replace(/[^\d]/g, '')
  const parsed = parseInt(numericValue, 10) || 0

  // Apply min/max constraints
  let constrained = parsed
  if (props.min !== undefined && parsed < props.min) constrained = props.min
  if (props.max !== undefined && parsed > props.max) constrained = props.max

  const formatted = formatNumber(constrained)
  displayValue.value = formatted

  // Emit raw number value
  emits('update:modelValue', constrained)

  // Adjust cursor position after formatting
  const newLength = formatted.length
  const diff = newLength - oldLength
  requestAnimationFrame(() => {
    input.setSelectionRange(cursorPos + diff, cursorPos + diff)
  })
}
</script>

<template>
  <input
    type="text"
    inputmode="numeric"
    :value="displayValue"
    :disabled="disabled"
    @input="handleInput"
    :class="cn(
      'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
      'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
      props.class
    )"
  />
</template>
