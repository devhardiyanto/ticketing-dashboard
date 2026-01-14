<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
	Sidebar,
	SidebarContent,
	SidebarFooter,
	SidebarHeader,
	SidebarMenu,
	SidebarMenuButton,
	SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavGroup, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
	BookOpen,
	Building2,
	// Folder,
	Image,
	LayoutGrid,
	Settings,
	ShoppingCart,
	Tickets,
	Users,
	Activity,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

/* Routes */
import { dashboard } from '@/routes';
import banner from '@/routes/banner';
import event from '@/routes/event';
import order from '@/routes/order';
import organization from '@/routes/organization';
import platform_fee from '@/routes/platform_fee';
import ticket_type from '@/routes/ticket_type';
import user from '@/routes/user';

const page = usePage();
const userPermissions = computed(() => page.props.auth.user.permissions || []);
const userRoles = computed(() => page.props.auth.user.roles || []);

const hasPermission = (permission?: string) => {
	if (!permission) return true;
	return userPermissions.value.includes(permission);
};

const hasRole = (role: string) => {
	return userRoles.value.includes(role);
};

// Define menu items with mapped permissions
const rawNavGroups: NavGroup[] = [
	{
		label: 'Overview',
		items: [
			{
				title: 'Dashboard',
				href: dashboard(),
				icon: LayoutGrid,
				permission: 'view-dashboard',
			},
		],
	},
	{
		label: 'User Management',
		items: [
			{
				title: 'Users',
				href: user.index(),
				icon: Users,
				permission: 'manage-users',
			},
			{
				title: 'Organization',
				href: organization.user.index(),
				icon: Building2,
				permission: 'manage-users', // Or specific org perm if needed
			},
		],
	},
	{
		label: 'Event Management',
		items: [
			{
				title: 'Events',
				href: event.index(),
				icon: BookOpen,
				permission: 'view-event',
			},
			{
				title: 'Ticket Types',
				href: ticket_type.index(),
				icon: Tickets,
				permission: 'manage-ticket-types',
			},
			{
				title: 'Orders',
				href: order.index(),
				icon: ShoppingCart,
				permission: 'manage-orders',
			},
		],
	},
	{
		label: 'Content & Settings',
		items: [
			{
				title: 'Banners',
				href: banner.index(),
				icon: Image,
				permission: 'manage-banners',
			},
			{
				title: 'Platform Fee',
				href: platform_fee.index(),
				icon: Settings,
				// Assuming Super Admin only for platform fee if no specific permission?
				// Or maybe 'manage-platform-fee' if it existed.
				// For now, let's assume it requires 'manage-users' (admin) or just show to all with sidebar access?
				// Let's use a computed permission for now or leave empty if public to dashboard users.
				// Ideally, explicit permission. Let's assume 'manage-platform-fee' doesn't exist yet, so check role?
			},
			{
				title: 'Activity Logs',
				href: '/activity-logs', // Route::get('activity-logs', ...)->name('activity-logs.index')
				icon: Activity,
				// Only Super Admin should see this usually
			},
		],
	},
];

const filteredNavGroups = computed(() => {
	return rawNavGroups.map(group => {
		const filteredItems = group.items.filter(item => {
			// Special case for Platform Fee & Activity Logs if permissions missing
			if (item.title === 'Platform Fee') {
				return hasRole('super_admin');
			}
			if (item.title === 'Activity Logs') {
				return hasRole('super_admin');
			}

			return hasPermission(item.permission);
		});

		return {
			...group,
			items: filteredItems
		};
	}).filter(group => group.items.length > 0);
});

const footerNavItems: NavItem[] = [
	// {
	// 	title: 'Github Repo',
	// 	href: 'https://github.com/laravel/vue-starter-kit',
	// 	icon: Folder,
	// },
	// {
	// 	title: 'Documentation',
	// 	href: 'https://laravel.com/docs/starter-kits#vue',
	// 	icon: BookOpen,
	// },
];
</script>

<template>
	<Sidebar collapsible="icon" variant="inset">
		<SidebarHeader>
			<SidebarMenu>
				<SidebarMenuItem>
					<SidebarMenuButton size="lg" as-child>
						<Link :href="dashboard()">
							<AppLogo />
						</Link>
					</SidebarMenuButton>
				</SidebarMenuItem>
			</SidebarMenu>
		</SidebarHeader>

		<SidebarContent>
			<NavMain :groups="filteredNavGroups" />
		</SidebarContent>

		<SidebarFooter>
			<NavFooter :items="footerNavItems" />
			<NavUser />
		</SidebarFooter>
	</Sidebar>
	<slot />
</template>
