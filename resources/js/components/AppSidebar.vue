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
import { Link } from '@inertiajs/vue3';
import {
	BookOpen,
	Building2,
	Folder,
	Image,
	LayoutGrid,
	Settings,
	ShoppingCart,
	Tickets,
	Users,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

/* Routes */
import { dashboard } from '@/routes';
import banner from '@/routes/banner';
import event from '@/routes/event';
import order from '@/routes/order';
import organization from '@/routes/organization';
import platform_fee from '@/routes/platform_fee';
import ticket_type from '@/routes/ticket_type';
import user from '@/routes/user';

const navGroups: NavGroup[] = [
	{
		label: 'Overview',
		items: [
			{
				title: 'Dashboard',
				href: dashboard(),
				icon: LayoutGrid,
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
			},
			{
				title: 'Organization',
				href: organization.user.index(),
				icon: Building2,
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
			},
			{
				title: 'Ticket Types',
				href: ticket_type.index(),
				icon: Tickets,
			},
			{
				title: 'Orders',
				href: order.index(),
				icon: ShoppingCart,
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
			},
			{
				title: 'Platform Fee',
				href: platform_fee.index(),
				icon: Settings,
			},
		],
	},
];

const footerNavItems: NavItem[] = [
	{
		title: 'Github Repo',
		href: 'https://github.com/laravel/vue-starter-kit',
		icon: Folder,
	},
	{
		title: 'Documentation',
		href: 'https://laravel.com/docs/starter-kits#vue',
		icon: BookOpen,
	},
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
			<NavMain :groups="navGroups" />
		</SidebarContent>

		<SidebarFooter>
			<NavFooter :items="footerNavItems" />
			<NavUser />
		</SidebarFooter>
	</Sidebar>
	<slot />
</template>
