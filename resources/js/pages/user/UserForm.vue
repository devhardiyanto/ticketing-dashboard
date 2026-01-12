<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Field,
  FieldContent,
  FieldError,
  FieldLabel,
} from '@/components/ui/field';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';
import userRoute from '@/routes/user';
import { Loader2, Lock } from 'lucide-vue-next';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const props = defineProps<{
  initialData?: any | null;
  organizations?: { id: string; name: string }[];
  roles?: { id: number; display_name: string }[];

  isLockedOrganization?: boolean;
  lockedOrganizationId?: string;
  lockedOrganizationName?: string;
}>();

const emit = defineEmits(['success']);

const isEditMode = computed(() => !!props.initialData);

const form = useForm({
  name: props.initialData?.name || '',
  email: props.initialData?.email || '',
  password: '',
  organization_id: props.initialData?.organization_id || props.lockedOrganizationId || '',
  role_id: props.initialData?.role_id || null,
  phone_number: props.initialData?.phone_number || '',
  status: props.initialData?.status || 'active',
});

const showOrgWarning = ref(false);

const submit = () => {
  // Logic: If Create Mode AND Organization ID is set manually (and not locked/pre-filled?) -> Show Warning?
  // Use case: Creating user via "Menu Users" and selecting an Org.
  // Warning says: "User will be assigned to Org... cannot be changed."

  if (!isEditMode.value && form.organization_id && !props.isLockedOrganization) {
    showOrgWarning.value = true;
    return;
  }

  executeSubmit();
};

const confirmCreate = () => {
  showOrgWarning.value = false;
  executeSubmit();
};

const executeSubmit = () => {
  if (isEditMode.value) {
    form.put(userRoute.update(props.initialData.id).url, {
      onSuccess: () => {
        toast.success('User updated successfully');
        emit('success');
      },
      onError: () => toast.error('Failed to update user'),
    });
  } else {
    form.post(userRoute.store().url, {
      onSuccess: () => {
        toast.success('User created successfully');
        emit('success');
      },
      onError: () => toast.error('Failed to create user'),
    });
  }
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4 pt-4">
    <!-- Name -->
    <Field name="name" :invalid="!!form.errors.name">
      <FieldLabel>Name</FieldLabel>
      <FieldContent>
        <Input v-model="form.name" placeholder="John Doe" />
      </FieldContent>
      <FieldError>{{ form.errors.name }}</FieldError>
    </Field>

    <!-- Email -->
    <Field name="email" :invalid="!!form.errors.email">
      <FieldLabel>Email</FieldLabel>
      <FieldContent>
        <Input v-model="form.email" type="email" placeholder="john@example.com" />
      </FieldContent>
      <FieldError>{{ form.errors.email }}</FieldError>
    </Field>

    <!-- Password -->
    <Field name="password" :invalid="!!form.errors.password">
      <FieldLabel>{{ isEditMode ? 'New Password (Optional)' : 'Password' }}</FieldLabel>
      <FieldContent>
        <Input v-model="form.password" type="password" placeholder="********" />
      </FieldContent>
      <FieldError>{{ form.errors.password }}</FieldError>
    </Field>

    <!-- Organization -->
    <Field name="organization_id" :invalid="!!form.errors.organization_id">
      <FieldLabel class="flex items-center gap-2">
         Organization
         <Lock v-if="isEditMode || isLockedOrganization" class="w-3 h-3 text-muted-foreground" />
      </FieldLabel>
      <FieldContent>
        <Select v-model="form.organization_id" :disabled="isEditMode || isLockedOrganization">
          <SelectTrigger>
            <SelectValue placeholder="Select Organization" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">None (System Admin)</SelectItem> <!-- Empty string for null? needs handling in backend -->
            <SelectItem v-for="org in organizations" :key="org.id" :value="org.id">
              {{ org.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </FieldContent>
      <FieldError>{{ form.errors.organization_id }}</FieldError>
      <p v-if="isEditMode" class="text-xs text-muted-foreground mt-1">
        Organization cannot be changed after assignment.
      </p>
    </Field>

    <!-- Role -->
    <Field name="role_id" :invalid="!!form.errors.role_id">
      <FieldLabel>Role</FieldLabel>
      <FieldContent>
        <Select v-model="form.role_id"> <!-- Shadcn Select v-model binds to value -->
          <SelectTrigger>
            <SelectValue placeholder="Select Role" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.display_name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </FieldContent>
      <FieldError>{{ form.errors.role_id }}</FieldError>
    </Field>

    <!-- Phone Number -->
    <Field name="phone_number" :invalid="!!form.errors.phone_number">
      <FieldLabel>Phone Number</FieldLabel>
      <FieldContent>
        <Input v-model="form.phone_number" placeholder="+123..." />
      </FieldContent>
      <FieldError>{{ form.errors.phone_number }}</FieldError>
    </Field>

    <!-- Status -->
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

    <div class="flex justify-end pt-4">
      <Button type="submit" :disabled="form.processing">
        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
        {{ isEditMode ? 'Update User' : 'Create User' }}
      </Button>
    </div>
  </form>

  <AlertDialog v-model:open="showOrgWarning">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>⚠️ Assigning Organization</AlertDialogTitle>
        <AlertDialogDescription>
          You are about to assign this user to an organization.
          <br/><br/>
          <b>Important:</b> Once assigned, the organization <b>cannot be changed</b>.
          Are you sure you want to proceed?
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="showOrgWarning = false">Cancel</AlertDialogCancel>
        <AlertDialogAction @click="confirmCreate">Yes, Create User</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
