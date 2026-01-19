<script setup lang="ts">
import ContentLayout from '@/layouts/ContentLayout.vue';
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { GripVertical, ArrowDownUp } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import BaseDialog from '@/components/common/BaseDialog.vue';
import { Spinner } from '@/components/ui/spinner';
import DataTable from '@/components/common/DataTable.vue';
import draggable from 'vuedraggable';
import { toast } from 'vue-sonner';
import bannerRoute from '@/routes/banner';
import { useColumns } from './columns';
import { useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import type { Banner } from '@/types/dashboard/banner';
import { defineAsyncComponent } from 'vue';

const BannerForm = defineAsyncComponent({
	loader: () => import('./BannerForm.vue'),
	loadingComponent: Spinner,
});

const props = defineProps<{
	events: any[];
}>();

const queryClient = useQueryClient();
const isDialogOpen = ref(false);
const isReorderOpen = ref(false);
const selectedItem = ref<Banner | null>(null);
const localBanners = ref<Banner[]>([]);
const isLoadingReorder = ref(false);

const isLoadingEdit = ref(false);

const openEdit = async (item: Banner) => {
	try {
        isLoadingEdit.value = true;
        const response = await axios.get(bannerRoute.show(item.id).url);
        selectedItem.value = response.data;
        isDialogOpen.value = true;
    } catch (error) {
        toast.error('Failed to load banner data');
        console.error(error);
    } finally {
        isLoadingEdit.value = false;
    }
};

const onActionSuccess = () => {
	queryClient.invalidateQueries({ queryKey: ['banners'] });
	isDialogOpen.value = false;
};

const columns = useColumns(openEdit, onActionSuccess);

// Fetch all banners for reorder dialog
const openReorder = async () => {
	isLoadingReorder.value = true;
	try {
		const res = await axios.get(bannerRoute.reorderList().url);
		localBanners.value = res.data;
		isReorderOpen.value = true;
		// eslint-disable-next-line @typescript-eslint/no-unused-vars
	} catch (error) {
		toast.error('Failed to load banners for reorder');
	} finally {
		isLoadingReorder.value = false;
	}
};

const openCreate = () => {
	selectedItem.value = null;
	isDialogOpen.value = true;
};

const saveOrder = () => {
	const ids = localBanners.value.map((b: any) => b.id);
	router.patch(bannerRoute.reorder().url, { ids }, {
		preserveScroll: true,
		onSuccess: () => {
			toast.success('Banners reordered successfully');
			queryClient.invalidateQueries({ queryKey: ['banners'] });
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
						<Button variant="outline" @click="openReorder" :disabled="isLoadingReorder">
								<ArrowDownUp class="mr-2 h-4 w-4" /> Reorder
						</Button>
				</div>
		</div>

		<DataTable
			:columns="columns"
			:on-create="openCreate"
			create-label="Add Banner"
			:api-url="bannerRoute.data().url"
			:query-key="['banners']"
		/>

		<!-- Create/Edit Dialog -->
		<BaseDialog v-model:open="isDialogOpen" :title="selectedItem ? 'Edit Banner' : 'Create Banner'">
			<BannerForm
				:initial-data="selectedItem"
				:events="props.events"
				@success="onActionSuccess"
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
