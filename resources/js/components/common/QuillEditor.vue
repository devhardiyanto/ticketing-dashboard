<script setup lang="ts">
import { QuillEditor as VueQuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { useVModel } from '@vueuse/core'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  modelValue?: string
  placeholder?: string
  height?: number | string
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string): void
}>()

const content = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: '',
})

const toolbarOptions = [
  ['bold', 'italic', 'underline', 'strike'],
  [{ 'list': 'ordered' }, { 'list': 'bullet' }],
  ['link'],
  ['clean'],
]

const editorHeight = typeof props.height === 'number' ? `${props.height}px` : (props.height || '200px')
</script>

<template>
  <div
    :class="cn(
      'quill-wrapper rounded-md border border-input bg-transparent shadow-xs transition-[color,box-shadow]',
      'focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]',
      props.class
    )"
  >
    <VueQuillEditor
      v-model:content="content"
      content-type="html"
      :placeholder="placeholder || 'Enter description...'"
      :toolbar="toolbarOptions"
      theme="snow"
    />
  </div>
</template>

<style scoped>
.quill-wrapper :deep(.ql-toolbar) {
  border: none;
  border-bottom: 1px solid hsl(var(--border));
  border-radius: calc(var(--radius) - 2px) calc(var(--radius) - 2px) 0 0;
}

.quill-wrapper :deep(.ql-container) {
  border: none;
  font-size: 0.875rem;
  min-height: v-bind(editorHeight);
}

.quill-wrapper :deep(.ql-editor) {
  min-height: v-bind(editorHeight);
  padding: 0.75rem;
}

.quill-wrapper :deep(.ql-editor.ql-blank::before) {
  color: hsl(var(--muted-foreground));
  font-style: normal;
}
</style>
