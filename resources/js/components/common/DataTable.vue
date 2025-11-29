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
import { ChevronRight, ChevronLeft } from 'lucide-vue-next';

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
  data: TData[] | null
  loading?: boolean
  onCreate?: () => void
  filters?: {
    search?: string
    limit?: number
  }
  pagination?: {
    current_page: number
    per_page: number
    total: number
    last_page: number
    from: number
    to: number
  } | null
}>()

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/
const search = ref(props.filters?.search ?? '')
const limit = ref(String(props.filters?.limit ?? '10'))

// FIX: route().url() → tidak ada
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

/*
|--------------------------------------------------------------------------
| TABLE INIT
|--------------------------------------------------------------------------
*/
const paginationState = computed(() => ({
  pageIndex: (props.pagination?.current_page ?? 1) - 1,
  pageSize: props.pagination?.per_page ?? 10,
}))

const table = useVueTable({
  get data() {
    return props.data ?? []
  },
  get columns() {
    return props.columns ?? []
  },
  getCoreRowModel: getCoreRowModel(),
  manualPagination: true,
  manualFiltering: true,
  get rowCount() {
    return props.pagination?.total ?? 0
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
  },
})

/*
|--------------------------------------------------------------------------
| WATCHERS
|--------------------------------------------------------------------------
*/
// Sync limit changes to table state (which triggers onPaginationChange)
watch(limit, (newLimit) => {
  table.setPageSize(Number(newLimit))
})

// Search still triggers direct URL update for now, as it's external to table state
watch(search, debounce(() => {
  router.get(
    getCurrentUrl(),
    {
      search: search.value,
      limit: Number(limit.value),
      page: 1,
    },
    {
      preserveState: true,
      replace: true,
      preserveScroll: true,
    }
  )
}, 300))
</script>


<template>
  <div class="space-y-4">
    <!-- TOP CONTROLS -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <Input
          placeholder="Search..."
          v-model="search"
          class="h-8 w-[150px] lg:w-[250px]"
        />
      </div>
      <div class="flex items-center space-x-2">
        <Button v-if="onCreate" @click="onCreate" class="h-8">
            Add Event
        </Button>
      </div>
    </div>

    <!-- TABLE -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
          >
            <TableHead
              v-for="header in headerGroup.headers"
              :key="header.id"

            >
              <FlexRender
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <!-- LOADING -->
          <TableRow v-if="loading">
            <TableCell
              :colspan="table.getAllLeafColumns().length"
              class="h-24 text-center"
            >
              Loading...
            </TableCell>
          </TableRow>

          <!-- DATA -->
          <TableRow
            v-else-if="table.getRowModel().rows.length"
            v-for="row in table.getRowModel().rows"
            :key="row.id"
          >
            <TableCell
              v-for="cell in row.getVisibleCells()
              "
              :key="cell.id"
              :style="{ width: `${cell.column.getSize()}px` }"
            >
              <FlexRender
                :render="cell.column.columnDef.cell"
                :props="cell.getContext()"
              />
            </TableCell>
          </TableRow>

          <!-- EMPTY -->
          <TableRow v-else>
            <TableCell
              :colspan="table.getAllLeafColumns().length"
              class="h-24 text-center"
            >
              No results.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- PAGINATION & LIMIT -->
    <div
      v-if="pagination && pagination.total"
      class="flex items-center justify-between px-2"
    >
      <!-- ROWS PER PAGE (LEFT) -->
      <div class="flex items-center space-x-2">
        <p class="text-sm inline-block text-muted-foreground">Rows per page</p>
        <Select v-model="limit">
          <SelectTrigger class="h-8 w-[70px]">
            <SelectValue :placeholder="limit" />
          </SelectTrigger>
          <SelectContent side="top">
            <SelectItem
              v-for="size in ['10', '25', '50', '100']"
              :key="size"
              :value="size"
            >
              {{ size }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- PAGINATION (RIGHT) -->
      <div class="relative">
        <Pagination
          :total="pagination.total"
          :sibling-count="1"
          show-edges
          :default-page="pagination.current_page"
          :items-per-page="pagination.per_page"
        >
          <PaginationContent>
            <div>
              <p class="text-sm inline-block text-muted-foreground">Page {{ pagination.current_page }} of {{ pagination.last_page }}</p>
            </div>

            <PaginationPrevious @click="table.previousPage()">
              <ChevronLeft />
            </PaginationPrevious>

            <PaginationNext @click="table.nextPage()">
              <ChevronRight />
            </PaginationNext>

          </PaginationContent>
        </Pagination>
      </div>
    </div>
  </div>
</template>
