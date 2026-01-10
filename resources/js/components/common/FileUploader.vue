<script setup lang="ts">
import { ref, watch, onUnmounted } from 'vue'
import { UploadCloud, X, File as FileIcon, Eye, Trash2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
	Dialog,
	DialogContent,
	DialogTitle,
	DialogDescription
} from '@/components/ui/dialog'

const props = defineProps<{
	modelValue?: File[] | File | string | null
	multiple?: boolean
	accept?: string
	maxSize?: number // in bytes
	displayUrl?: string | null // Signed URL for displaying existing image
}>()

const emit = defineEmits(['update:modelValue', 'change', 'error'])

const internalFiles = ref<Array<{ file?: File; preview?: string; id: string; name?: string; storagePath?: string }>>([])
const isDragging = ref(false)
const previewImage = ref<string | null>(null)
const isPreviewOpen = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const errorState = ref<string | null>(null)

// Helper to generate unique ID
const generateId = () => Math.random().toString(36).substring(2, 9)

// Helper to create preview URL
const createPreview = (file: File) => {
	if (file.type.startsWith('image/')) {
		return URL.createObjectURL(file)
	}
	return undefined
}

// Watch for external model changes
watch(
	[() => props.modelValue, () => props.displayUrl],
	([newVal, displayUrl]) => {
		if (!newVal || (Array.isArray(newVal) && newVal.length === 0)) {
			if (internalFiles.value.length > 0) {
				internalFiles.value = []
			}
		} else if (typeof newVal === 'string') {
			// newVal is a storage path, use displayUrl for preview
			const previewSrc = displayUrl || newVal
			if (internalFiles.value.length === 0 || internalFiles.value[0].storagePath !== newVal) {
				internalFiles.value = [{
					id: generateId(),
					preview: previewSrc,
					storagePath: newVal,
					name: 'Existing Image'
				}]
			} else if (internalFiles.value[0].preview !== previewSrc) {
				// Update preview URL if displayUrl changed
				internalFiles.value[0].preview = previewSrc
			}
		}
	},
	{ immediate: true }
)

const handleDrop = (e: DragEvent) => {
	isDragging.value = false
	const droppedFiles = e.dataTransfer?.files
	if (droppedFiles) {
		processFiles(Array.from(droppedFiles))
	}
}

const handleFileSelect = (e: Event) => {
	const input = e.target as HTMLInputElement
	if (input.files) {
		processFiles(Array.from(input.files))
	}
	if (input) input.value = '' // Reset input
}

const processFiles = (files: File[]) => {
	const validFiles: File[] = []
	const rejectedFiles: File[] = []

	files.forEach(file => {
		if (props.maxSize && file.size > props.maxSize) {
			rejectedFiles.push(file)
		} else {
			validFiles.push(file)
		}
	})

	if (rejectedFiles.length > 0) {
		emit('error', { type: 'size', files: rejectedFiles })
		errorState.value = `File size exceeds the limit of ${props.maxSize ? props.maxSize / 1024 / 1024 : 0}MB`
	} else {
		errorState.value = null
	}

	const newFiles = validFiles.map((file) => ({
		file,
		preview: createPreview(file),
		id: generateId(),
	}))

	if (props.multiple) {
		internalFiles.value = [...internalFiles.value, ...newFiles]
		emit('update:modelValue', internalFiles.value.map(f => f.file).filter(Boolean))
	} else {
		// Single file mode - replace existing
		// Cleanup old preview if exists (only for blob URLs)
		if (internalFiles.value.length > 0 && internalFiles.value[0].preview?.startsWith('blob:')) {
			URL.revokeObjectURL(internalFiles.value[0].preview)
		}

		if (newFiles.length > 0) {
			internalFiles.value = [newFiles[0]]
			emit('update:modelValue', newFiles[0].file)
		}
	}
}

const removeFile = (id: string) => {
	const index = internalFiles.value.findIndex((f) => f.id === id)
	if (index !== -1) {
		const file = internalFiles.value[index]
		// Only revoke blob URLs
		if (file.preview?.startsWith('blob:')) {
			URL.revokeObjectURL(file.preview)
		}
		internalFiles.value.splice(index, 1)

		if (props.multiple) {
			emit('update:modelValue', internalFiles.value.map(f => f.file))
		} else {
			emit('update:modelValue', null)
		}
	}
}

const openPreview = (url: string) => {
	previewImage.value = url
	isPreviewOpen.value = true
}

// Cleanup object URLs
onUnmounted(() => {
	internalFiles.value.forEach((f) => {
		if (f.preview?.startsWith('blob:')) URL.revokeObjectURL(f.preview)
	})
})
</script>

<template>
  <div class="w-full space-y-4">
    <!-- Drop Zone -->
    <div
      class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors hover:bg-muted/50"
      :class="{ 'border-primary bg-muted/50': isDragging, 'border-muted-foreground/25': !isDragging && !errorState, 'border-destructive bg-destructive/10': errorState }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="fileInput?.click()"
    >
      <input
        ref="fileInput"
        type="file"
        class="hidden"
        :multiple="multiple"
        :accept="accept"
        @change="handleFileSelect"
      />
      <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
        <UploadCloud class="w-8 h-8 mb-2 text-muted-foreground" />
        <p class="text-sm text-muted-foreground">
          <span class="font-semibold">Click to upload</span> or drag and drop
        </p>
        <p class="text-xs text-muted-foreground mt-1" v-if="accept">
          {{ accept === 'image/*' ? 'Images only (PNG, JPG, GIF)' : accept }}
        </p>
        <p class="text-xs text-destructive mt-1" v-if="errorState">
            {{ errorState }}
        </p>
      </div>
    </div>

    <!-- File List -->
    <div v-if="internalFiles.length > 0" class="grid gap-4" :class="multiple ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4' : 'grid-cols-1'">
      <div
        v-for="file in internalFiles"
        :key="file.id"
        class="group relative flex flex-col items-center justify-center p-2 border rounded-lg bg-background overflow-hidden"
      >
        <!-- Image Preview -->
        <div v-if="file.preview" class="relative w-full aspect-video rounded-md overflow-hidden bg-muted">
          <img :src="file.preview" class="object-cover w-full h-full" alt="Preview" />

          <!-- Overlay Actions -->
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            <Button type="button" size="icon" variant="secondary" class="h-8 w-8" @click.stop="openPreview(file.preview)">
              <Eye class="w-4 h-4" />
            </Button>
            <Button type="button" size="icon" variant="destructive" class="h-8 w-8" @click.stop="removeFile(file.id)">
              <Trash2 class="w-4 h-4" />
            </Button>
          </div>
        </div>

        <!-- Document Preview -->
        <div v-else class="flex flex-col items-center justify-center w-full aspect-square bg-muted rounded-md p-4 relative group">
           <FileIcon class="w-10 h-10 text-muted-foreground mb-2" />
           <span class="text-xs text-center break-all line-clamp-2 px-2">{{ file.file?.name || file.name }}</span>

            <!-- Overlay Actions -->
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            <Button type="button" size="icon" variant="destructive" class="h-8 w-8" @click.stop="removeFile(file.id)">
              <Trash2 class="w-4 h-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview Dialog -->
    <Dialog v-model:open="isPreviewOpen">
      <DialogContent class="w-full p-0 overflow-hidden bg-transparent border-none shadow-none">
				<DialogTitle class="sr-only">Preview</DialogTitle>
				<DialogDescription class="sr-only">Full preview of the file</DialogDescription>
         <div class="relative w-full h-full flex items-center justify-center pointer-events-none">
             <!-- Wrapper for content to capture pointer events -->
            <div class="relative pointer-events-auto">
                <img v-if="previewImage" :src="previewImage" class="size-fit object-contain rounded-lg shadow-lg" alt="Full Preview" />
                <Button
                    type="button"
                    variant="secondary"
                    size="icon"
                    class="absolute -top-2 -right-2 rounded-full shadow-md"
                    @click="isPreviewOpen = false"
                >
                    <X class="w-4 h-4" />
                </Button>
            </div>
         </div>
      </DialogContent>
    </Dialog>
  </div>
</template>
