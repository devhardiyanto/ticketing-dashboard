import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
	user: User;
}

export interface BreadcrumbItem {
	title: string;
	href: string;
}

export interface NavItem {
	title: string;
	href: NonNullable<InertiaLinkProps['href']>;
	icon?: LucideIcon;
	isActive?: boolean;
	permission?: string;
	items?: NavItem[]; // recursive for submenus if needed
}

export interface NavGroup {
	label: string;
	items: NavItem[];
}

export type AppPageProps<
	T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
	name: string;
	quote: { message: string; author: string };
	auth: Auth;
	sidebarOpen: boolean;
};

export interface User {
	id: number
	name: string
	avatar?: string;
	email?: string;
	phone_number: string
	phone_number: string
	organization_id: any
	status: string
	permissions: string[]
	roles: Roles[]
	email_verified_at: string
	last_login_at: string
	created_at: string
	updated_at: string
	deleted_at: any
}

export interface Roles {
	id: number
	name: string
	guard_name: string
	created_at: string
	updated_at: string
	pivot: Pivot
}

export interface Pivot {
	model_type: string
	model_id: number
	role_id: number
}

export type BreadcrumbItemType = BreadcrumbItem;
