<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
  title?: string;
}>();
</script>

<template>
  <Head :title="title" v-if="title" />

  <AppLayout>
    <template #header v-if="$slots.header || title">
      <slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          {{ title }}
        </h2>
      </slot>
    </template>

    <div class="py-12">
      <div class="mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <slot />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
