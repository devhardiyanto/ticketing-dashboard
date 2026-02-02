<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { useAuthCache } from '@/composables/useAuthCache';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

interface Props {
	breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
	breadcrumbs: () => [],
});

const page = usePage();
const { ensureCache } = useAuthCache();

// Initialize auth cache on layout mount
onMounted(async () => {
	const user = page.props.auth?.user as any;
	if (user) {
		await ensureCache({
			permissions: user.permissions,
			roles: user.roles,
			sidebar_menu: page.props.sidebar_menu as any,
		});
	}
});
</script>

<template>
	<AppShell variant="sidebar">
		<AppSidebar />
		<AppContent variant="sidebar" class="overflow-x-hidden">
			<AppSidebarHeader :breadcrumbs="breadcrumbs" />
			<slot />
		</AppContent>
	</AppShell>
</template>
