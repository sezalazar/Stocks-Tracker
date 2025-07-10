<script setup lang="ts">
import { ref, h } from 'vue'
import { useVueTable, getCoreRowModel, getSortedRowModel, getFilteredRowModel, FlexRender } from '@tanstack/vue-table'
import type { ColumnFiltersState, SortingState, VisibilityState } from '@tanstack/vue-table'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { ChevronDownIcon } from '@heroicons/vue/24/solid'
import { Search } from 'lucide-vue-next'

import type { StockOrCryptoItem } from '@/types/stock'
import { getColumns } from '@/composables/useStockTable'
import { rowLinkStyle } from '@/utils/styleHelpers'

defineEmits(['view-item'])

const props = defineProps<{
    data: StockOrCryptoItem[]
    noResultsMessage?: string
}>()

const columns = getColumns()

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const table = useVueTable({
    data: props.data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: (updaterOrValue) =>
        (sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue),
    onColumnFiltersChange: (updaterOrValue) =>
        (columnFilters.value = typeof updaterOrValue === 'function' ? updaterOrValue(columnFilters.value) : updaterOrValue),
    onColumnVisibilityChange: (updaterOrValue) =>
        (columnVisibility.value = typeof updaterOrValue === 'function' ? updaterOrValue(columnVisibility.value) : updaterOrValue),
    state: {
        get sorting() {
            return sorting.value
        },
        get columnFilters() {
            return columnFilters.value
        },
        get columnVisibility() {
            return columnVisibility.value
        },
    },
})
</script>

<template>
    <div>
        <div class="mt-4 flex items-center gap-2">
            <Search class="text-gray-400 w-5 h-5" />
            <Input
                class="max-w-sm focus:ring-2 focus:ring-[hsl(var(--ring))] transition"
                placeholder="Search symbol..."
                :model-value="table.getColumn('symbol')?.getFilterValue() as string ?? ''"
                @update:model-value="(val) => table.getColumn('symbol')?.setFilterValue(val)"
            />
        </div>

        <div class="py-4 flex items-center">
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="ml-auto flex items-center">
                        Columns
                        <ChevronDownIcon class="ml-2 h-4 w-4 inline-block" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuCheckboxItem
                        v-for="column in table.getAllColumns().filter((c) => c.getCanHide())"
                        :key="column.id"
                        class="capitalize"
                        :checked="column.getIsVisible()"
                        @update:checked="(value) => column.toggleVisibility(!!value)"
                    >
                        {{ column.id }}
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <div class="rounded-lg border bg-[hsl(var(--card))]">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                        class="border-b border-[hsl(var(--border))] hover:bg-transparent"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="text-right h-12 px-4 text-[hsl(var(--primary))] font-semibold uppercase tracking-wider"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <template v-for="row in table.getRowModel().rows" :key="row.id">
                            <TableRow :class="rowLinkStyle()" @click="$emit('view-item', row.original.symbol)" class="border-0">
                                <TableCell
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    class="p-4 border-b border-[hsl(var(--border))]"
                                >
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </TableCell>
                            </TableRow>
                        </template>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center text-[hsl(var(--muted-foreground))]">
                            {{ props.noResultsMessage || 'No results.' }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
