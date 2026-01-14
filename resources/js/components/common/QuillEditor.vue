<script setup lang="ts">
import { QuillEditor as VueQuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { useVModel } from '@vueuse/core'
import { ref, watch, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { useSignedUrl } from '@/composables/useSignedUrl'
import Quill from 'quill'

const props = defineProps<{
	modelValue?: string
	placeholder?: string
	height?: number | string
	class?: HTMLAttributes['class']
	folder?: string // Storage folder for uploaded images
}>()

const emits = defineEmits<{
	(e: 'update:modelValue', payload: string): void
}>()

const content = useVModel(props, 'modelValue', emits, {
	passive: true,
	defaultValue: '',
})

const quillRef = ref<InstanceType<typeof VueQuillEditor> | null>(null)
const { uploadFile, getSignedUrl, loading } = useSignedUrl()

// Toolbar with image button
const toolbarOptions = [
	['bold', 'italic', 'underline', 'strike'],
	[{ 'list': 'ordered' }, { 'list': 'bullet' }],
	['link', 'image'],
	['clean'],
]

const editorHeight = typeof props.height === 'number' ? `${props.height}px` : (props.height || '200px')

/**
 * Handle image upload when user clicks image button
 */
function handleImageUpload() {
	const input = document.createElement('input')
	input.type = 'file'
	input.accept = 'image/*'
	input.onchange = async () => {
		const file = input.files?.[0]
		if (!file) return

		// Validate file size (max 5MB)
		if (file.size > 5 * 1024 * 1024) {
			alert('Image size must be less than 5MB')
			return
		}

		const folder = props.folder || 'editor-images'
		const result = await uploadFile(file, folder)

		if (result.success && result.path) {
			insertImage(result.path)
		} else {
			alert(result.error || 'Failed to upload image')
		}
	}
	input.click()
}

/**
 * Insert image into editor with data-storage-path attribute
 */
function insertImage(path: string) {
	const quill = quillRef.value?.getQuill() as Quill | undefined
	if (!quill) return

	const range = quill.getSelection(true)

	// Get signed URL for display
	getSignedUrl(path).then((url) => {
		if (url) {
			// Insert image with custom attribute for storage path
			quill.insertEmbed(range.index, 'image', url)

			// Add data-storage-path attribute to the img element
			setTimeout(() => {
				const img = quill.root.querySelector(`img[src="${url}"]`)
				if (img) {
					img.setAttribute('data-storage-path', path)
				}
			}, 0)

			quill.setSelection(range.index + 1, 0)
		}
	})
}

/**
 * Setup Quill editor with custom image handler
 */
function onEditorReady(quill: Quill) {
	// Override default image handler
	quill.getModule('toolbar').addHandler('image', handleImageUpload)
}


// Custom Image Blot to support data-storage-path
const Image = Quill.import('formats/image')
const ImageFormatAttributesList = ['alt', 'height', 'width', 'style', 'data-storage-path']

class CustomImage extends Image {
	static formats(domNode: Element) {
		return ImageFormatAttributesList.reduce((formats: Record<string, any>, attribute) => {
			if (domNode.hasAttribute(attribute)) {
				formats[attribute] = domNode.getAttribute(attribute)
			}
			return formats
		}, {})
	}

	format(name: string, value: any) {
		if (ImageFormatAttributesList.includes(name)) {
			if (value) {
				this.domNode.setAttribute(name, value)
			} else {
				this.domNode.removeAttribute(name)
			}
		} else {
			super.format(name, value)
		}
	}
}

// Register the custom image blot
Quill.register(CustomImage, true)

// Watch for content changes and resolve URLs if needed (optional client-side resolution)
watch(content, async (newContent) => {
	if (newContent) {
		// No-op for now, hydration happens on load
	}
})
</script>

<template>
  <div
    :class="cn(
			'quill-wrapper rounded-md border border-input bg-transparent shadow-xs transition-[color,box-shadow]',
			'focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]',
			loading && 'opacity-50 pointer-events-none',
			props.class
		)"
  >
    <VueQuillEditor
      ref="quillRef"
      v-model:content="content"
      content-type="html"
      :placeholder="placeholder || 'Enter description...'"
      :toolbar="toolbarOptions"
      theme="snow"
      @ready="onEditorReady"
    />
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-background/50">
      <span class="text-sm text-muted-foreground">Uploading...</span>
    </div>
  </div>
</template>

<style scoped>
.quill-wrapper {
	position: relative;
}

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

.quill-wrapper :deep(.ql-editor img) {
	max-width: 100%;
	height: auto;
	border-radius: 0.375rem;
}
</style>
