<script setup lang="ts">
import { defineProps } from 'vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

const props = defineProps<{
  prices: Array<{ date: string; open: number; high: number; low: number; close: number; volume: number }>
}>()

const formatDate = (dateStr: string): string => {
  const date = new Date(dateStr)
  return isNaN(date.getTime()) ? dateStr : date.toLocaleDateString()
}
</script>

<template>
  <div class="p-6">
    <h3 class="text-xl font-semibold mb-4">Historical Prices</h3>
    <Table class="w-full">
      <TableHeader>
        <TableRow>
          <TableHead class="text-left">Date</TableHead>
          <TableHead class="text-left">Open</TableHead>
          <TableHead class="text-left">High</TableHead>
          <TableHead class="text-left">Low</TableHead>
          <TableHead class="text-left">Close</TableHead>
          <TableHead class="text-left">Volume</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <tr v-for="(item, index) in prices" :key="index" class="hover:bg-gray-50">
          <TableCell>{{ formatDate(item.date) }}</TableCell>
          <TableCell>{{ item.open }}</TableCell>
          <TableCell>{{ item.high }}</TableCell>
          <TableCell>{{ item.low }}</TableCell>
          <TableCell>{{ item.close }}</TableCell>
          <TableCell>{{ item.volume }}</TableCell>
        </tr>
      </TableBody>
    </Table>
  </div>
</template>
