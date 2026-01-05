<script setup lang="ts">
import { ref } from 'vue'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { Camera } from 'lucide-vue-next'

const props = defineProps<{
  src?: string | null
  alt: string
}>()

const isOpen = ref(false)

const openPreview = () => {
  if (props.src) {
    isOpen.value = true
  }
}
</script>

<template>
  <div
    class="cursor-pointer hover:opacity-80 transition-opacity"
    @click="openPreview"
  >
    <img
      v-if="src"
      :src="src"
      :alt="alt"
      class="w-12 h-12 rounded object-cover border border-border"
      loading="lazy"
    />
    <div
      v-else
      class="w-12 h-12 rounded bg-muted flex items-center justify-center border border-border"
    >
      <Camera class="w-6 h-6 text-muted-foreground" />
    </div>
  </div>

  <Dialog v-model:open="isOpen">
    <DialogContent class="max-w-4xl">
      <div class="flex items-center justify-center">
        <img
          v-if="src"
          :src="src"
          :alt="alt"
          class="w-full h-auto max-h-[80vh] object-contain rounded"
        />
      </div>
    </DialogContent>
  </Dialog>
</template>
