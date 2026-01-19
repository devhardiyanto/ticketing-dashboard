<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
	Field,
	FieldContent,
	FieldError,
	FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import organizationRoute from '@/routes/organization';
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { z } from 'zod';

const props = defineProps<{
	initialData?: any;
	onSuccess?: () => void;
}>();

const isEditing = computed(() => !!props.initialData);

// Validation Schema
const schema = z
	.object({
		// Organization Details
		name: z.string().min(2, 'Name must be at least 2 characters'),
		business_type: z.string().min(2, 'Business Type is required'),
		email: z.string().email('Invalid email address'),
		phone_number: z.string().optional(),
		address: z.string().optional(),
		tax_id: z.string().optional(),
		status: z.enum(['active', 'inactive']).default('active'),

		// Admin User Details (Only for Create)
		// We make them optional here and refine later or just check conditionally
		admin_name: z.string().optional(),
		admin_email: z.string().optional(),
		password: z.string().optional(),
		password_confirmation: z.string().optional(),
	})
	.superRefine((data, ctx) => {
		if (!props.initialData) {
			// Create Mode
			if (!data.admin_name || data.admin_name.length < 2) {
				ctx.addIssue({
					code: z.ZodIssueCode.custom,
					message: 'Admin Name is required',
					path: ['admin_name'],
				});
			}
			if (
				!data.admin_email ||
				!z.string().email().safeParse(data.admin_email).success
			) {
				ctx.addIssue({
					code: z.ZodIssueCode.custom,
					message: 'Valid Admin Email is required',
					path: ['admin_email'],
				});
			}
			if (!data.password || data.password.length < 8) {
				ctx.addIssue({
					code: z.ZodIssueCode.custom,
					message: 'Password must be at least 8 characters',
					path: ['password'],
				});
			}
			if (data.password !== data.password_confirmation) {
				ctx.addIssue({
					code: z.ZodIssueCode.custom,
					message: "Passwords don't match",
					path: ['password_confirmation'],
				});
			}
		}
	});

const form = useForm({
	name: props.initialData?.name || '',
	business_type: props.initialData?.business_type || '',
	email: props.initialData?.email || '',
	phone_number: props.initialData?.phone_number || '',
	address: props.initialData?.address || '',
	tax_id: props.initialData?.tax_id || '',
	status: props.initialData?.status || 'active',

	// Admin User (Create only)
	admin_name: '',
	admin_email: '',
	password: '',
	password_confirmation: '',
});

const submit = () => {
	// 1. Client-side Validation using Zod
	const result = schema.safeParse(form.data());

	if (!result.success) {
		result.error.issues.forEach((issue) => {
			form.setError(issue.path[0] as any, issue.message);
		});
		toast.error('Please fix form errors.');
		return;
	}

	if (isEditing.value) {
		form.put(organizationRoute.update(props.initialData.id).url, {
			onSuccess: () => {
				toast.success('Organization updated successfully');
				props.onSuccess?.();
			},
			onError: () => toast.error('Failed to update organization'),
		});
	} else {
		form.post(organizationRoute.store().url, {
			onSuccess: () => {
				toast.success('Organization created successfully');
				props.onSuccess?.();
			},
			onError: () => toast.error('Failed to create organization'),
		});
	}
};
</script>

<template>
	<form @submit.prevent="submit" class="space-y-6">
		<div class="grid grid-cols-1">
			<Field name="name" :invalid="!!form.errors.name">
				<FieldLabel
					>Name
					<span class="text-destructive">*</span></FieldLabel
				>
				<FieldContent>
					<Input
						v-model="form.name"
						placeholder="Acme Corp"
					/>
				</FieldContent>
				<FieldError>{{ form.errors.name }}</FieldError>
			</Field>

			<Field
				name="business_type"
				:invalid="!!form.errors.business_type"
			>
				<FieldLabel
					>Business Type
					<span class="text-destructive">*</span></FieldLabel
				>
				<FieldContent>
					<Input
						v-model="form.business_type"
						placeholder="Technology, Retail, etc."
					/>
				</FieldContent>
				<FieldError>{{ form.errors.business_type }}</FieldError>
			</Field>

			<Field name="email" :invalid="!!form.errors.email">
				<FieldLabel
					>Organization Email
					<span class="text-destructive">*</span></FieldLabel
				>
				<FieldContent>
					<Input
						v-model="form.email"
						type="email"
						placeholder="contact@acme.com"
					/>
				</FieldContent>
				<FieldError>{{ form.errors.email }}</FieldError>
			</Field>

			<Field
				name="phone_number"
				:invalid="!!form.errors.phone_number"
			>
				<FieldLabel>Phone Number</FieldLabel>
				<FieldContent>
					<Input
						v-model="form.phone_number"
						placeholder="+1234567890"
					/>
				</FieldContent>
				<FieldError>{{ form.errors.phone_number }}</FieldError>
			</Field>

			<Field name="tax_id" :invalid="!!form.errors.tax_id">
				<FieldLabel>Tax ID</FieldLabel>
				<FieldContent>
					<Input
						v-model="form.tax_id"
						placeholder="TAX-12345"
					/>
				</FieldContent>
				<FieldError>{{ form.errors.tax_id }}</FieldError>
			</Field>

			<Field
				name="status"
				:invalid="!!form.errors.status"
				v-if="isEditing"
			>
				<FieldLabel>Status</FieldLabel>
				<FieldContent>
					<Select v-model="form.status">
						<SelectTrigger>
							<SelectValue placeholder="Select status" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem value="active"
								>Active</SelectItem
							>
							<SelectItem value="inactive"
								>Inactive</SelectItem
							>
						</SelectContent>
					</Select>
				</FieldContent>
				<FieldError>{{ form.errors.status }}</FieldError>
			</Field>

			<Field name="address" :invalid="!!form.errors.address">
				<FieldLabel>Address</FieldLabel>
				<FieldContent>
					<Textarea
						v-model="form.address"
						placeholder="123 Main St..."
					/>
				</FieldContent>
				<FieldError>{{ form.errors.address }}</FieldError>
			</Field>

			<!-- Initial Admin User Section (Create Only) -->
			<div
				v-if="!isEditing"
				class="space-y-4 border-t pt-4 md:col-span-2"
			>
				<h3 class="text-lg font-medium">Initial Admin User</h3>
				<p class="text-sm text-muted-foreground">
					Create an admin user to manage this organization.
				</p>

				<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
					<Field
						name="admin_name"
						:invalid="!!form.errors.admin_name"
					>
						<FieldLabel
							>Admin Name
							<span class="text-destructive">*</span></FieldLabel
						>
						<FieldContent>
							<Input
								v-model="form.admin_name"
								placeholder="John Doe"
							/>
						</FieldContent>
						<FieldError>{{ form.errors.admin_name }}</FieldError>
					</Field>

					<Field
						name="admin_email"
						:invalid="!!form.errors.admin_email"
					>
						<FieldLabel
							>Admin Email
							<span class="text-destructive">*</span></FieldLabel
						>
						<FieldContent>
							<Input
								v-model="form.admin_email"
								type="email"
								placeholder="john@acme.com"
							/>
						</FieldContent>
						<FieldError>{{ form.errors.admin_email }}</FieldError>
					</Field>

					<Field name="password" :invalid="!!form.errors.password">
						<FieldLabel
							>Password
							<span class="text-destructive">*</span></FieldLabel
						>
						<FieldContent>
							<Input
								v-model="form.password"
								type="password"
								placeholder="********"
							/>
						</FieldContent>
						<FieldError>{{ form.errors.password }}</FieldError>
					</Field>

					<Field
						name="password_confirmation"
						:invalid="!!form.errors.password_confirmation"
					>
						<FieldLabel
							>Confirm Password
							<span class="text-destructive">*</span></FieldLabel
						>
						<FieldContent>
							<Input
								v-model="form.password_confirmation"
								type="password"
								placeholder="********"
							/>
						</FieldContent>
						<FieldError>{{
							form.errors.password_confirmation
						}}</FieldError>
					</Field>
				</div>
			</div>
		</div>

		<div class="flex justify-end space-x-2">
			<Button type="submit" :disabled="form.processing">
				<Loader2
					v-if="form.processing"
					class="mr-2 h-4 w-4 animate-spin"
				/>
				{{ isEditing ? 'Update Organization' : 'Create Organization' }}
			</Button>
		</div>
	</form>
</template>
