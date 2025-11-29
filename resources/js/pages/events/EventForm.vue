<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Event } from '@/types/event';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    initialData?: Event | null;
}>();

const emit = defineEmits(['success']);

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const form = useForm({
    name: props.initialData?.name || '',
    description: props.initialData?.description || '',
    start_date: formatDate(props.initialData?.start_date || ''),
    end_date: formatDate(props.initialData?.end_date || ''),
    location: props.initialData?.location || '',
    organization_id: props.initialData?.organization_id || '1', // Default or select
});

const submit = () => {
    if (props.initialData) {
        form.put(route('events.update', props.initialData.id), {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post(route('events.store'), {
            onSuccess: () => emit('success'),
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" required />
            <div v-if="form.errors.name" class="text-sm text-red-500">
                {{ form.errors.name }}
            </div>
        </div>

        <div class="space-y-2">
            <Label for="description">Description</Label>
            <Input id="description" v-model="form.description" />
            <div v-if="form.errors.description" class="text-sm text-red-500">
                {{ form.errors.description }}
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="start_date">Start Date</Label>
                <Input
                    id="start_date"
                    type="datetime-local"
                    v-model="form.start_date"
                    required
                />
                <div v-if="form.errors.start_date" class="text-sm text-red-500">
                    {{ form.errors.start_date }}
                </div>
            </div>

            <div class="space-y-2">
                <Label for="end_date">End Date</Label>
                <Input
                    id="end_date"
                    type="datetime-local"
                    v-model="form.end_date"
                    required
                />
                <div v-if="form.errors.end_date" class="text-sm text-red-500">
                    {{ form.errors.end_date }}
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <Label for="location">Location</Label>
            <Input id="location" v-model="form.location" required />
            <div v-if="form.errors.location" class="text-sm text-red-500">
                {{ form.errors.location }}
            </div>
        </div>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ initialData ? 'Update' : 'Create' }}
            </Button>
        </div>
    </form>
</template>
