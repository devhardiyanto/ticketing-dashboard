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
import { Checkbox } from '@/components/ui/checkbox';
import { useForm, usePage } from '@inertiajs/vue3';
import { store, update } from "@/actions/App/Http/Controllers/EventController";
import { defineAsyncComponent } from 'vue';
import { Spinner } from '@/components/ui/spinner';

const DateTimeRangePicker = defineAsyncComponent({
  loader: () => import('@/components/common/DateTimeRangePicker.vue'),
  loadingComponent: Spinner,
});
const QuillEditor = defineAsyncComponent({
  loader: () => import('@/components/common/QuillEditor.vue'),
  loadingComponent: Spinner,
});
const FileUploader = defineAsyncComponent({
  loader: () => import('@/components/common/FileUploader.vue'),
  loadingComponent: Spinner,
});

import currencyData from '@/data/currencies.json';
import { useTimezones } from '@/composables/useTimezones';
import { ref, watch } from 'vue';
import axios from 'axios';
import { useDebounceFn } from '@vueuse/core';

const currencies = currencyData;
import type { Event, Organization } from '@/types/dashboard';
import { toast } from 'vue-sonner'

const props = defineProps<{
  initialData?: Event | null;
  organizations: Organization[];
  parentEvent?: Event | null;
}>();

const emit = defineEmits(['success']);

const { timezones } = useTimezones();

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString); // VueDatePicker works best with Date objects
};

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
  name: props.initialData?.name || '',
  slug: props.initialData?.slug || '',
  description: props.initialData?.description || '',
  start_date: props.initialData?.start_date ? formatDate(props.initialData.start_date) : '',
  end_date: props.initialData?.end_date ? formatDate(props.initialData.end_date) : '',
  location: props.initialData?.location || '',
  organization_id: props.initialData?.organization_id || props.parentEvent?.organization_id || '',
  timezone: 'UTC', // Default to UTC or infer from browser
  image_url: props.initialData?.image_url || (null as File | string | null),
  venue_map_url: props.initialData?.venue_map_url || (null as File | string | null),
  address: props.initialData?.address || '',
  status: props.initialData?.status || 'draft',
  currency: props.initialData?.currency || 'IDR',
  is_parent: props.initialData?.is_parent || false,
  parent_event_id: props.initialData?.parent_event_id || props.parentEvent?.id || '',
});

// Hydrate content with fresh signed URLs
import { useContentHydration } from '@/composables/useContentHydration';
import { onMounted } from 'vue';

const { hydrateContent } = useContentHydration();

onMounted(async () => {
  if (form.description) {
    const hydrated = await hydrateContent(form.description);
    if (hydrated !== form.description) {
      form.description = hydrated;
      // Update defaults so the form isn't considered dirty
      form.defaults('description', hydrated);
    }
  }
});


// Slug auto-generation logic
const slugManuallyEdited = ref(!!props.initialData?.slug);
const isCheckingSlug = ref(false);
const slugAvailable = ref<boolean | null>(null);

// Generate slug from name
const generateSlug = (name: string): string => {
  return name
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '') // Remove special characters
    .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
};

// Debounced slug availability check
const checkSlugAvailability = useDebounceFn(async (slug: string) => {
  if (!slug) {
    slugAvailable.value = null;
    return;
  }

  isCheckingSlug.value = true;
  try {
    const response = await axios.get('/event/check-slug', {
      params: {
        slug,
        exclude_id: props.initialData?.id || undefined,
      },
    });
    slugAvailable.value = response.data.available;
  } catch (error) {
    console.error('Error checking slug availability:', error);
    slugAvailable.value = null;
  } finally {
    isCheckingSlug.value = false;
  }
}, 500);

// Watch for name changes to auto-generate slug
watch(() => form.name, (newName) => {
  // Only auto-generate if slug hasn't been manually edited and both fields were empty initially
  if (!slugManuallyEdited.value) {
    const newSlug = generateSlug(newName);
    form.slug = newSlug;
    checkSlugAvailability(newSlug);
  }
});

// Watch for slug changes to check availability
watch(() => form.slug, (newSlug) => {
  checkSlugAvailability(newSlug);
});

// Handle manual slug edit
const onSlugInput = () => {
  slugManuallyEdited.value = true;
};

const submit = () => {
  if (props.initialData) {
    if (form.image_url || form.venue_map_url) {
      const updateConfig = update(props.initialData.id);
      const url = typeof updateConfig === 'string' ? updateConfig : updateConfig.url;

      form.transform((data) => ({
        ...data,
        _method: 'PUT',
      })).post(url, {
        onSuccess: () => {
          emit('success');
          toast.success('Event updated successfully');
        },
      });
    } else {
      form.submit(update(props.initialData.id), {
        onSuccess: () => {
          emit('success');
          toast.success('Event updated successfully');
        },
      })
    }
  } else {
    form.submit(store(), {
      onSuccess: () => {
        emit('success');
        toast.success('Event created successfully');
      },
    })
  }
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="space-y-4">
      <div class="space-y-2">
        <Field name="name" :invalid="!!form.errors.name">
          <FieldLabel>Name <span class="text-red-500">*</span></FieldLabel>
          <FieldContent>
              <Input v-model="form.name" />
          </FieldContent>
          <FieldError>{{ form.errors.name }}</FieldError>
        </Field>
      </div>

      <div class="space-y-2">
        <Field name="slug" :invalid="!!form.errors.slug || slugAvailable === false">
          <FieldLabel>
            Slug <span class="text-red-500">*</span>
            <span v-if="isCheckingSlug" class="ml-2 text-xs text-muted-foreground">Checking...</span>
            <span v-else-if="slugAvailable === true" class="ml-2 text-xs text-green-600">✓ Available</span>
            <span v-else-if="slugAvailable === false" class="ml-2 text-xs text-red-500">✗ Already taken</span>
          </FieldLabel>
          <FieldContent>
              <Input v-model="form.slug" @input="onSlugInput" placeholder="auto-generated-from-name" />
          </FieldContent>
          <FieldError v-if="form.errors.slug">{{ form.errors.slug }}</FieldError>
          <p class="text-xs text-muted-foreground">URL-friendly identifier for the event. Auto-generated from name if left empty.</p>
        </Field>
      </div>

      <div class="space-y-2">
        <Field name="description" :invalid="!!form.errors.description">
          <FieldLabel>Description</FieldLabel>
          <FieldContent>
            <QuillEditor v-model="form.description" :height="200" />
          </FieldContent>
          <FieldError>{{ form.errors.description }}</FieldError>
        </Field>
      </div>

      <div class="space-y-2">
        <FieldLabel>Event Banner</FieldLabel>
        <FileUploader
          v-model="form.image_url"
          :display-url="initialData?.image_signed_url"
          accept="image/*"
          :max-size="5 * 1024 * 1024"
          @error="(err) => console.error(err)"
        />
        <FieldError>{{ form.errors.image_url }}</FieldError>
      </div>

      <div class="space-y-2">
        <FieldLabel>Venue Map</FieldLabel>
        <FileUploader
          v-model="form.venue_map_url"
          :display-url="initialData?.venue_map_url"
          accept="image/*"
          :max-size="5 * 1024 * 1024"
          @error="(err) => console.error(err)"
        />
        <FieldError>{{ form.errors.venue_map_url }}</FieldError>
        <p class="text-xs text-muted-foreground">Upload peta/layout venue untuk membantu pengunjung.</p>
      </div>

      <div class="space-y-2">
        <FieldLabel>Timezone</FieldLabel>
        <Select v-model="form.timezone">
          <SelectTrigger>
            <SelectValue placeholder="Select Timezone" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="tz in timezones" :key="tz" :value="tz">
              {{ tz }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="space-y-2" v-if="!user?.organization_id && !parentEvent && organizations?.length > 0">
        <FieldLabel>Organization</FieldLabel>
        <Select v-model="form.organization_id">
          <SelectTrigger>
            <SelectValue placeholder="Select Organization" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="org in organizations" :key="org.id" :value="org.id">
              {{ org.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="space-y-2">
        <DateTimeRangePicker
          v-model:startDate="form.start_date"
          v-model:endDate="form.end_date"
          :timezone="form.timezone"
          :error="form.errors.start_date || form.errors.end_date"
        />
      </div>

      <div class="space-y-2">
        <Field name="location" :invalid="!!form.errors.location">
          <FieldLabel>Location <span class="text-red-500">*</span></FieldLabel>
          <FieldContent>
            <Input v-model="form.location" />
          </FieldContent>
          <FieldError>{{ form.errors.location }}</FieldError>
        </Field>
      </div>

      <div class="space-y-2">
        <Field name="address" :invalid="!!form.errors.address">
          <FieldLabel>Address</FieldLabel>
          <FieldContent>
            <Input v-model="form.address" />
          </FieldContent>
          <FieldError>{{ form.errors.address }}</FieldError>
        </Field>
      </div>

      <div class="space-y-2">
        <FieldLabel>Status <span class="text-red-500">*</span></FieldLabel>
        <Select v-model="form.status">
          <SelectTrigger>
            <SelectValue placeholder="Select Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="draft">Draft</SelectItem>
            <SelectItem value="published">Published</SelectItem>
            <SelectItem value="archived">Archived</SelectItem>
          </SelectContent>
        </Select>
        <FieldError>{{ form.errors.status }}</FieldError>
      </div>

      <div class="space-y-2">
        <FieldLabel>Currency <span class="text-red-500">*</span></FieldLabel>
        <Select v-model="form.currency">
          <SelectTrigger>
            <SelectValue placeholder="Select Currency" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="(details, code) in currencies" :key="code" :value="details">
              {{ details.name }} ({{ details.symbol }})
            </SelectItem>
          </SelectContent>
        </Select>
        <FieldError>{{ form.errors.currency }}</FieldError>
      </div>

      <div class="flex items-center space-x-2" v-if="!parentEvent">
        <Checkbox id="is_parent" v-model="form.is_parent" value="1" :checked="form.is_parent" @update:checked="(val: boolean) => form.is_parent = val" />
        <label
          for="is_parent"
          class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
        >
          Is Parent Event
        </label>
      </div>
    </div>

    <div class="flex justify-end pt-4">
      <Button type="submit" :disabled="form.processing">
        {{ initialData ? 'Update' : 'Create' }}
      </Button>
    </div>
  </form>
</template>
