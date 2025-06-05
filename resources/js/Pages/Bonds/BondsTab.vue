<script setup>
import { usePage } from '@inertiajs/vue3'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

const page = usePage()
const bonds = page.props.bondsList || []
</script>

<template>
  <Table>
    <TableCaption>Bonds.</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead class="w-[100px]">Bond</TableHead>
        <TableHead>TIR</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="bond in bonds" :key="bond.symbol">
        <TableCell class="font-medium">{{ bond.symbol }}</TableCell>
        <TableCell>{{ bond.tir }}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>