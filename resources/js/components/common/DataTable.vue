<script setup lang="ts" generic="TData, TValue">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue
} from '@/components/ui/select'
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow
} from '@/components/ui/table'
import { router } from '@inertiajs/vue3'
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table'
import { debounce } from 'lodash'
import { ref, watch, computed } from 'vue';
import { route } from 'ziggy-js'
import { ChevronRight, ChevronLeft, Loader2, AlertCircle } from 'lucide-vue-next';
import { useQuery, keepPreviousData } from '@tanstack/vue-query';
import axios from 'axios';

import {
	Pagination,
	PaginationContent,
	PaginationNext,
	PaginationPrevious,
} from '@/components/ui/pagination';

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/
const props = defineProps<{
	columns: any[] | null
	// Data props (biasanya dari Inertia)
	data?: TData[] | null
	loading?: boolean
	pagination?: {
		current_page: number
		per_page: number
		total: number
		last_page: number
		from: number
		to: number
	} | null

	// Async Data Support
	apiUrl?: string
	queryKey?: any[]

	// Common
	onCreate?: () => void
	createLabel?: string
	filters?: {
		search?: string
		limit?: number
	}
}>()

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/
const search = ref(props.filters?.search ?? '')
const limit = ref(String(props.filters?.limit ?? '10'))

// Local pagination state for Async mode
const pageIndex = ref(0) // 0-indexed for TanStack Table
const pageSize = ref(Number(limit.value))

// Sorting state for Async mode
const sortColumn = ref<string | null>(null)
const sortOrder = ref<'asc' | 'desc'>('desc')

/*
|--------------------------------------------------------------------------
| ASYNC DATA FETCHING (FIXED)
|--------------------------------------------------------------------------
*/
const isAsync = computed(() => !!props.apiUrl);

// 1. FIX KEY: Gunakan props.queryKey sebagai base agar invalidation dari parent jalan
const computedQueryKey = computed(() => {
	const baseKey = props.queryKey ?? ['datatable', props.apiUrl];
	// Kita masukkan params sebagai object di elemen terakhir array key
	return [
		...baseKey,
		{
			page: pageIndex.value,
			limit: pageSize.value,
			search: search.value,
			sort: sortColumn.value,
			order: sortOrder.value,
		}
	];
});

const {
	data: queryData,
	isLoading: isQueryLoading,
	isFetching,
	isError,
	error,
	refetch
} = useQuery({
	queryKey: computedQueryKey,

	queryFn: async ({ signal }) => {
		if (!props.apiUrl) throw new Error("API URL is missing");

		const res = await axios.get(props.apiUrl, {
			params: {
				page: pageIndex.value + 1, // API is 1-indexed
				limit: pageSize.value,
				search: search.value,
				sort: sortColumn.value,
				order: sortOrder.value,
			},
			signal // 2. FIX: Auto cancel request jika user mengetik cepat
		});
		return res.data;
	},

	enabled: isAsync,
	placeholderData: keepPreviousData,

	// 3. FIX: Gunakan data Inertia sebagai cache awal (Initial Data)
	// Ini mencegah loading spinner muncul saat halaman baru dibuka
	initialData: () => {
		// Syarat: Halaman 1, tidak ada search, dan props data tersedia
		if (props.data && props.pagination && pageIndex.value === 0 && !search.value) {
			return {
				data: props.data,
				...props.pagination // Gabungkan pagination info agar strukturnya sama dengan response API
			} as any;
		}
		return undefined;
	},

	// 4. FIX: Hapus 'staleTime' hardcoded disini.
	// Biarkan dia mengikuti setting global 'vue-query.ts' (5 menit)
	retry: 1,
});

/*
|--------------------------------------------------------------------------
| DATA COMPUTED
|--------------------------------------------------------------------------
*/
const tableData = computed(() => {
	if (isAsync.value) {
		return queryData.value?.data ?? [];
	}
	return props.data ?? [];
});

const tablePagination = computed(() => {
	if (isAsync.value) {
		// Fallback jika queryData belum ready tapi kita punya initialProps (kasus edge case)
		if (!queryData.value) {
			return props.pagination ?? {
				current_page: 1,
				per_page: pageSize.value,
				total: 0,
				last_page: 1,
				from: 0,
				to: 0
			};
		}

		return {
			current_page: queryData.value.current_page,
			per_page: queryData.value.per_page,
			total: queryData.value.total,
			last_page: queryData.value.last_page,
			from: queryData.value.from,
			to: queryData.value.to,
		};
	}
	return props.pagination;
});

const isLoading = computed(() => {
	// Hanya loading jika async aktif, sedang loading awal, DAN tidak ada data di cache
	if (isAsync.value) {
		return isQueryLoading.value && !queryData.value;
	}
	return props.loading;
});

/*
|--------------------------------------------------------------------------
| TABLE INIT
|--------------------------------------------------------------------------
*/
const paginationState = computed(() => ({
	pageIndex: isAsync.value ? pageIndex.value : (props.pagination?.current_page ?? 1) - 1,
	pageSize: isAsync.value ? pageSize.value : (props.pagination?.per_page ?? 10),
}))

const getCurrentUrl = () => {
	try {
		if (route().current()) {
			return route(route().current() as string);
		}
		return window.location.pathname;
	} catch {
		return window.location.pathname;
	}
};

const table = useVueTable({
	get data() {
		return tableData.value
	},
	get columns() {
		return props.columns ?? []
	},
	getCoreRowModel: getCoreRowModel(),
	manualPagination: true,
	manualFiltering: true,
	get rowCount() {
		return tablePagination.value?.total ?? 0
	},
	state: {
		get pagination() {
			return paginationState.value
		},
	},
	onPaginationChange: (updaterOrValue) => {
		const newPagination = typeof updaterOrValue === 'function'
			? updaterOrValue(paginationState.value)
			: updaterOrValue

		if (isAsync.value) {
			pageIndex.value = newPagination.pageIndex;
			pageSize.value = newPagination.pageSize;
			limit.value = String(newPagination.pageSize);
		} else {
			router.get(
				getCurrentUrl(),
				{
					search: search.value,
					limit: newPagination.pageSize,
					page: newPagination.pageIndex + 1,
				},
				{
					preserveState: true,
					replace: true,
					preserveScroll: true,
				}
			)
		}
	},
})

/*
|--------------------------------------------------------------------------
| WATCHERS
|--------------------------------------------------------------------------
*/
watch(limit, (newLimit) => {
	const newSize = Number(newLimit);
	if (isAsync.value) {
		pageSize.value = newSize;
		pageIndex.value = 0;
	} else {
		table.setPageSize(newSize)
	}
})

watch(search, debounce(() => {
	if (isAsync.value) {
		pageIndex.value = 0;
		// Query auto-refetches via computed key
	} else {
		router.get(
			getCurrentUrl(),
			{
				search: search.value,
				limit: Number(limit.value),
				page: 1,
			},
			{ preserveState: true, replace: true, preserveScroll: true }
		)
	}
}, 300))
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <Input
          placeholder="Search..."
          v-model="search"
          class="h-8 w-[150px] lg:w-[250px]"
        />
        <Loader2 v-if="isAsync && isFetching && !isLoading" class="h-4 w-4 animate-spin text-muted-foreground" />
      </div>
      <div class="flex items-center space-x-2">
        <Button v-if="onCreate" @click="onCreate" class="h-8">
            {{ createLabel ?? 'Add' }}
        </Button>
      </div>
    </div>

    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
          >
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <TableRow v-if="isLoading">
            <TableCell :colspan="columns?.length ?? 1" class="h-24 text-center">
              <div class="flex items-center justify-center gap-2 text-muted-foreground">
                  <Loader2 class="h-4 w-4 animate-spin" />
                  <span>Loading data...</span>
              </div>
            </TableCell>
          </TableRow>

          <TableRow v-else-if="isAsync && isError">
            <TableCell :colspan="columns?.length ?? 1" class="h-24 text-center">
              <div class="flex flex-col items-center justify-center gap-2 text-destructive">
                  <div class="flex items-center gap-2">
                    <AlertCircle class="h-4 w-4" />
                    <span class="font-medium">Failed to load data</span>
                  </div>
                  <p class="text-xs text-muted-foreground">{{ error?.message }}</p>
                  <Button variant="outline" size="sm" @click="() => refetch()" class="mt-2 h-7">
                    Try Again
                  </Button>
              </div>
            </TableCell>
          </TableRow>

          <TableRow
            v-else-if="table.getRowModel().rows.length"
            v-for="row in table.getRowModel().rows"
            :key="row.id"
          >
            <TableCell
              v-for="cell in row.getVisibleCells()"
              :key="cell.id"
              :style="{ width: `${cell.column.getSize()}px` }"
            >
              <FlexRender
                :render="cell.column.columnDef.cell"
                :props="cell.getContext()"
              />
            </TableCell>
          </TableRow>

          <TableRow v-else>
            <TableCell :colspan="columns?.length ?? 1" class="h-24 text-center">
              No results found.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div
      v-if="tablePagination && tablePagination.total > 0"
      class="flex items-center justify-between px-2"
    >
      <div class="flex items-center space-x-2">
        <p class="text-sm inline-block text-muted-foreground">Rows per page</p>
        <Select v-model="limit">
          <SelectTrigger class="h-8 w-[70px]">
            <SelectValue :placeholder="limit" />
          </SelectTrigger>
          <SelectContent side="top">
            <SelectItem v-for="size in ['10', '25', '50', '100']" :key="size" :value="size">
              {{ size }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="relative">
        <Pagination
          :total="tablePagination.total"
          :sibling-count="1"
          show-edges
          :default-page="tablePagination.current_page"
          :items-per-page="tablePagination.per_page"
        >
          <PaginationContent>
            <div class="mr-4 hidden sm:block">
              <p class="text-sm text-muted-foreground">
                Page {{ tablePagination.current_page }} of {{ tablePagination.last_page }}
              </p>
            </div>
            <PaginationPrevious
              @click="table.previousPage()"
              :disabled="!table.getCanPreviousPage()"
              class="cursor-pointer"
            >
              <ChevronLeft class="h-4 w-4" />
            </PaginationPrevious>
            <PaginationNext
              @click="table.nextPage()"
              :disabled="!table.getCanNextPage()"
              class="cursor-pointer"
            >
              <ChevronRight class="h-4 w-4" />
            </PaginationNext>
          </PaginationContent>
        </Pagination>
      </div>
    </div>
  </div>
</template>
