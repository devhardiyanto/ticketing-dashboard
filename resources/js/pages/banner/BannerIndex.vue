<script setup lang="ts">
import ContentLayout from '@/layouts/ContentLayout.vue';
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { GripVertical, ArrowDownUp } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import BaseDialog from '@/components/common/BaseDialog.vue';
import BannerForm from './BannerForm.vue';
import DataTable from '@/components/common/DataTable.vue';
import draggable from 'vuedraggable';
import { toast } from 'vue-sonner';
import bannerRoute from '@/routes/banner';
import { useColumns } from './columns';
import type { Banner } from '@/types/dashboard/banner';

const props = defineProps<{
	banners: {
		data: any[];
		current_page: number;
		per_page: number;
		total: number;
		last_page: number;
		from: number;
		to: number;
	};
	events: any[];
	filters?: {
		search?: string;
		limit?: number;
	};
}>();

const columns = useColumns();
const isDialogOpen = ref(false);
const isReorderOpen = ref(false);
const selectedItem = ref<any>(null);
const localBanners = ref<Banner[]>([]);

// Sync localBanners when reorder opens
const openReorder = () => {
	// We ideally should fetch *all* banners for reordering if pagination is active,
	// but for now we work with current page or assume low count.
	// If explicit reordering is needed across pages, backend should support fetching all for list.
	// Assuming banners are few (e.g. < 50), passing them all or handle reorder on current page.
	localBanners.value = [...props.banners.data];
	isReorderOpen.value = true;
};

const openCreate = () => {
	selectedItem.value = null;
	isDialogOpen.value = true;
};

const openEdit = (item: any) => {
	selectedItem.value = item;
	isDialogOpen.value = true;
};

// Prepare data for DataTable with callbacks attached
const tableData = computed(() =>
	props.banners.data.map(banner => ({
		...banner,
		onEdit: openEdit,
		onSuccess: () => {
			// Optional: Force reload if needed, usually Inertia handles it
		}
	}))
);

const saveOrder = () => {
	const ids = localBanners.value.map((b: any) => b.id);
	router.patch(bannerRoute.reorder().url, { ids }, {
		preserveScroll: true,
		onSuccess: () => {
			toast.success('Banners reordered successfully');
			isReorderOpen.value = false;
		},
		onError: () => {
			toast.error('Failed to reorder banners');
		}
	});
};

const breadcrumbs = computed(() => [
	{ title: 'Banners', href: bannerRoute.index().url }
]);
</script>

<template>
	<Head title="Banners" />
	<ContentLayout title="Banners" :breadcrumbs="breadcrumbs">
		<div class="flex justify-between items-center mb-6">
				<h2 class="text-2xl font-bold tracking-tight">Banner Management</h2>
				<div class="flex space-x-2">
						<Button variant="outline" @click="openReorder">
									<ArrowDownUp class="mr-2 h-4 w-4" /> Reorder
						</Button>
				</div>
		</div>

		<DataTable
			:columns="columns"
			:data="tableData"
			:filters="filters"
			:pagination="banners"
			:on-create="openCreate"
			create-label="Add Banner"
		/>

		<!-- Create/Edit Dialog -->
		<BaseDialog v-model:open="isDialogOpen" :title="selectedItem ? 'Edit Banner' : 'Create Banner'">
			<BannerForm
				:initial-data="selectedItem"
				:events="events"
				@success="isDialogOpen = false"
			/>
		</BaseDialog>

		<!-- Reorder Dialog -->
		<BaseDialog v-model:open="isReorderOpen" title="Reorder Banners" class="sm:max-w-lg">
			<div class="p-4 space-y-4">
				<p class="text-sm text-gray-500">Drag and drop items to reorder.</p>
				<div class="max-h-[60vh] overflow-y-auto border rounded-md p-2">
					<draggable
						v-model="localBanners"
						item-key="id"
						handle=".handle"
						:animation="200"
						ghost-class="opacity-50"
						class="space-y-2">
							<template #item="{ element }">
								<div class="flex items-center gap-3 border p-2 rounded shadow-sm">
									<Button variant="ghost" size="icon" class="handle cursor-move h-8 w-8 text-gray-400">
										<GripVertical class="h-5 w-5" />
									</Button>
									<span class="font-medium text-sm">{{ element.title }}</span>
								</div>
							</template>
					</draggable>
				</div>
				<div class="flex justify-end space-x-2">
					<Button variant="outline" @click="isReorderOpen = false">Cancel</Button>
					<Button @click="saveOrder">Save Order</Button>
				</div>
			</div>
		</BaseDialog>

	</ContentLayout>
</template>
