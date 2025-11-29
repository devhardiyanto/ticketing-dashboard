<script setup lang="ts">
import BaseDialog from '@/components/common/BaseDialog.vue';
import DataTable from '@/components/common/DataTable.vue';
import ContentLayout from '@/layouts/ContentLayout.vue';
import type { Event } from '@/types/event';
import { computed, ref } from 'vue';
import { columns } from './columns';
import EventForm from './EventForm.vue';

const props = defineProps<{
  events: {
    data: Event[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
  };
  filters?: {
    search?: string;
    limit?: number;
  };
}>();

const isDialogOpen = ref(false);
const selectedItem = ref<Event | null>(null);

const openCreate = () => {
  selectedItem.value = null;
  isDialogOpen.value = true;
};

const openEdit = (item: Event) => {
  selectedItem.value = item;
  isDialogOpen.value = true;
};

const tableData = computed(() =>
  props.events.data.map((event) => ({
    ...event,
    onEdit: openEdit,
  })),
);
</script>

<template>
    <ContentLayout title="Events">
        <div class="mb-4 flex justify-between">
            <h3 class="text-lg font-medium">Event List</h3>
        </div>

        <DataTable
            :columns="columns"
            :data="tableData"
            :filters="filters"
            :pagination="events"
            :on-create="openCreate"
        />

        <BaseDialog
            v-model:open="isDialogOpen"
            :title="selectedItem ? 'Edit Event' : 'Create Event'"
        >
            <EventForm
                :initial-data="selectedItem"
                @success="isDialogOpen = false"
            />
        </BaseDialog>
    </ContentLayout>
</template>
