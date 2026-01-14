<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Field,
    FieldContent,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import bannerRoute from '@/routes/banner';
import FileUploader from '@/components/common/FileUploader.vue';

const props = defineProps<{
    initialData?: any;
    events: any[];
}>();

const emit = defineEmits(['success']);

const form = useForm({
    title: props.initialData?.title || '',
    event_id: props.initialData?.event_id || '',
    status: props.initialData?.status || 'active',
    image: null as File | null,
    image_url: props.initialData?.image_signed_url || null,
});

const submit = () => {
    // Logic aligned with EventForm.vue
    const url = props.initialData
        ? bannerRoute.update(props.initialData.id).url
        : bannerRoute.store().url;

    if (props.initialData) {
        // For updates, we must use POST with _method="PUT" to handle file uploads in Laravel
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(url, {
            onSuccess: () => {
                toast.success('Banner updated successfully');
                emit('success');
            },
            onError: () => {
                toast.error('Failed to update banner');
            }
        });
    } else {
        // For creation, standard POST
        form.post(url, {
            onSuccess: () => {
                toast.success('Banner created successfully');
                emit('success');
            },
            onError: () => {
                toast.error('Failed to create banner');
            }
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="space-y-4">
            <div class="space-y-2">
                <Field name="title" :invalid="!!form.errors.title">
                    <FieldLabel>Title <span class="text-red-500">*</span></FieldLabel>
                    <FieldContent>
                        <Input v-model="form.title" placeholder="Banner Title" />
                    </FieldContent>
                    <FieldError>{{ form.errors.title }}</FieldError>
                </Field>
            </div>

            <div class="space-y-2">
                <Field name="event_id" :invalid="!!form.errors.event_id">
                    <FieldLabel>Event</FieldLabel>
                    <FieldContent>
                        <Select v-model="form.event_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Event" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="event in events" :key="event.id" :value="event.id">
                                    {{ event.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </FieldContent>
                    <FieldError>{{ form.errors.event_id }}</FieldError>
                </Field>
            </div>

            <div class="space-y-2">
                <Field name="status" :invalid="!!form.errors.status">
                    <FieldLabel>Status</FieldLabel>
                    <FieldContent>
                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </FieldContent>
                    <FieldError>{{ form.errors.status }}</FieldError>
                </Field>
            </div>

            <div class="space-y-2">
                <FieldLabel>Banner Image <span v-if="!initialData" class="text-red-500">*</span></FieldLabel>
                <FileUploader
                    v-model="form.image"
                    :display-url="form.image_url"
                    accept="image/*"
                    :max-size="5 * 1024 * 1024"
                    @update:modelValue="(file) => form.image = file"
                />
                <FieldError>{{ form.errors.image }}</FieldError>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <Button type="submit" :disabled="form.processing">
                {{ initialData ? 'Update' : 'Create' }}
            </Button>
        </div>
    </form>
</template>
