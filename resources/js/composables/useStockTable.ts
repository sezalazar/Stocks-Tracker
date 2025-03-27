import { h } from 'vue'
import { Button } from '@/Components/ui/button'
import { ArrowsUpDownIcon } from '@heroicons/vue/24/solid'
import type { ColumnDef } from '@tanstack/vue-table'
import type { StockOrCryptoItem } from '@/types/stock'

export function rsiClass(rsi: number | null): string {
  if (rsi === null) return 'text-muted-foreground'
  if (rsi > 70) return 'text-red-600 font-bold'
  if (rsi < 30) return 'text-green-600 font-bold'
  return 'text-yellow-600 font-medium'
}

export function macdClass(macd: number | null): string {
  if (macd === null) return 'text-muted-foreground'
  if (macd > 0) return 'text-green-600 font-bold'
  return 'text-red-600 font-bold'
}

export function changeClass(changePercent: number | null): string {
  if (changePercent === null) return 'text-muted-foreground'
  return changePercent >= 0 ? 'text-green-600 font-bold' : 'text-red-600 font-bold'
}

export function formatCurrency(value: number | null): string {
  if (value === null) return '-'
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)
}

export function formatChangePercent(changePercent: number | null): string {
  if (changePercent == null) return '-'
  return `${changePercent > 0 ? '+' : ''}${changePercent.toFixed(2)}%`
}

export function getColumns(): ColumnDef<StockOrCryptoItem>[] {
  return [
    {
      accessorKey: 'symbol',
      header: ({ column }) =>
        h(
          Button,
          {
            variant: 'ghost',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: 'text-left'
          },
          () => [
            'Ticker',
            h(ArrowsUpDownIcon, { class: 'ml-2 h-4 w-4 inline-block' })
          ]
        ),
      enableSorting: true,
      enableColumnFilter: true,
      cell: ({ row }) => row.original.symbol,
    },
    {
      accessorKey: 'price',
      header: () => h('div', { class: 'text-right' }, 'Price'),
      cell: ({ row }) => {
        const price = row.original.price
        return h('div', { class: 'text-right' }, formatCurrency(price))
      },
      enableSorting: false,
    },
    {
      accessorKey: 'changePercent',
      header: ({ column }) =>
        h(
          Button,
          {
            variant: 'ghost',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: 'text-right'
          },
          () => [
            '% Change',
            h(ArrowsUpDownIcon, { class: 'ml-2 h-4 w-4 inline-block' })
          ]
        ),
      cell: ({ row }) => {
        const cp = row.original.changePercent
        return h('div', { class: `text-right ${changeClass(cp)}` }, formatChangePercent(cp))
      },
      enableSorting: true,
    },
    {
      accessorKey: 'rsi',
      header: ({ column }) =>
        h(
          Button,
          {
            variant: 'ghost',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: 'text-right'
          },
          () => [
            'RSI',
            h(ArrowsUpDownIcon, { class: 'ml-2 h-4 w-4 inline-block' })
          ]
        ),
      cell: ({ row }) => {
        const rsi = row.original.rsi
        return h('div', { class: `text-right ${rsiClass(rsi)}` }, rsi === null ? '-' : rsi)
      },
      enableSorting: true,
    },
    {
      accessorKey: 'macd',
      header: ({ column }) =>
        h(
          Button,
          {
            variant: 'ghost',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: 'text-right'
          },
          () => [
            'MACD',
            h(ArrowsUpDownIcon, { class: 'ml-2 h-4 w-4 inline-block' })
          ]
        ),
      cell: ({ row }) => {
        const macd = row.original.macd
        const numericMacd = Number(macd)
        const formattedMacd = !isNaN(numericMacd) ? numericMacd.toFixed(2) : '-'
        return h('div', { class: `text-right ${macdClass(macd)}` }, formattedMacd)
      },
      enableSorting: true,
    },
  ]
}
