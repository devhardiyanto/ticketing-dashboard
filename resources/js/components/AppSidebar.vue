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
import { useAuthCache } from '@/composables/useAuthCache';
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
	ScanQrCode,
	History,
	UsersRound,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';
import SimpleScrollArea from '@/components/ui/simple-scroll-area/SimpleScrollArea.vue';

const page = usePage();
const { getSidebarMenu, hasCache, isExpired } = useAuthCache();

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
	'ChartArea': ChartArea,
	'ScanQrCode': ScanQrCode,
	'History': History,
	'UsersRound': UsersRound,
};

// Transform backend menu structure
// Uses cache if valid, fallback to Inertia props
const menuGroups = computed(() => {
	let rawMenu: Array<{ group: string, items: Array<any> }> = [];

	// Try cache first
	if (hasCache() && !isExpired()) {
		const cachedMenu = getSidebarMenu();
		if (cachedMenu.length > 0) {
			rawMenu = cachedMenu;
		}
	}

	// Fallback to Inertia props
	if (rawMenu.length === 0) {
		rawMenu = page.props.sidebar_menu as Array<{ group: string, items: Array<any> }> || [];
	}

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

		<SidebarContent class="overflow-hidden">
			<SimpleScrollArea>
				<NavMain :groups="menuGroups" />
			</SimpleScrollArea>
		</SidebarContent>

		<SidebarFooter>
			<NavFooter :items="footerNavItems" />
			<NavUser />
		</SidebarFooter>
	</Sidebar>
	<slot />
</template>
