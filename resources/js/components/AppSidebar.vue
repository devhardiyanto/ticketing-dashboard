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
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
	BookOpen,
	Building2,
	Image,
	LayoutGrid,
	Settings,
	ShoppingCart,
	Tickets,
	Users,
	Shield,
	Activity,
	ChartArea,
	Calendar,
	FileText,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();

// Map string icon names from backend to Vue Components
const iconMap: Record<string, any> = {
	'PieChart': LayoutGrid,
	'Calendar': Calendar,
	'Ticket': Tickets,
	'ShoppingBag': ShoppingCart,
	'Users': Users,
	'Shield': Shield,
	'Settings': Settings,
	'FileText': FileText,
	'BookOpen': BookOpen,
	'Building2': Building2,
	'Image': Image,
	'Activity': Activity,
	'ChartArea': ChartArea
};

// Transform backend menu structure
// Note: Backend now provides 'href' resolved URL.
const menuGroups = computed(() => {
	const rawMenu = page.props.sidebar_menu as Array<{ group: string, items: Array<any> }> || [];

	return rawMenu.map(group => ({
		label: group.group,
		items: group.items.map(item => ({
			title: item.title,
			href: item.href,
			icon: iconMap[item.icon] || BookOpen,
			isActive: page.url === item.href || (item.href !== '/dashboard' && page.url.startsWith(item.href))
		}))
	}));
});

const footerNavItems: NavItem[] = [];
</script>

<template>
	<Sidebar collapsible="icon" variant="inset">
		<SidebarHeader>
			<SidebarMenu>
				<SidebarMenuItem>
					<SidebarMenuButton size="lg" as-child>
						<Link href="/dashboard">
							<AppLogo />
						</Link>
					</SidebarMenuButton>
				</SidebarMenuItem>
			</SidebarMenu>
		</SidebarHeader>

		<SidebarContent>
			<NavMain :groups="menuGroups" />
		</SidebarContent>

		<SidebarFooter>
			<NavFooter :items="footerNavItems" />
			<NavUser />
		</SidebarFooter>
	</Sidebar>
	<slot />
</template>
