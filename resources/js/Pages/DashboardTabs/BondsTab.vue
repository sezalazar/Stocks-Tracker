<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { rowLinkStyle } from '@/utils/styleHelpers'
import ColorizedChange from '@/Components/shared/ColorizedChange.vue';
import AssetSparkline from '@/Components/shared/AssetSparkline.vue';
import { formatPrice } from '@/utils/formatters';

const page = usePage()
const bonds = computed(() => page.props.bondsList || [])
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <Table>
      <TableCaption class="text-muted-foreground mt-4">Bonds list - Close prices</TableCaption>
      
      <TableHeader>
        <TableRow class="border-border hover:bg-transparent">
          <TableHead class="w-[100px] text-left text-muted-foreground uppercase tracking-wider text-xs">Symbol</TableHead>
          <TableHead class="text-right text-muted-foreground uppercase tracking-wider text-xs">Price</TableHead>
          <TableHead class="text-right text-muted-foreground uppercase tracking-wider text-xs">Change</TableHead>
          <TableHead class="text-right text-muted-foreground uppercase tracking-wider text-xs">Change %</TableHead>
          <TableHead class="text-center text-muted-foreground uppercase tracking-wider text-xs">Last 7 days</TableHead>
        </TableRow>
      </TableHeader>
      
      <TableBody>
        <TableRow v-if="bonds.length === 0" class="border-border">
            <TableCell colspan="5" class="text-center text-muted-foreground py-10">No hay datos de bonos para mostrar.</TableCell>
        </TableRow>
        
        <TableRow 
          v-for="bond in bonds" 
          :key="bond.symbol"
          :class="['border-border', rowLinkStyle()]"
        >
          <TableCell class="font-medium">{{ bond.symbol }}</TableCell>
          <TableCell class="text-right font-semibold">${{ formatPrice(bond.price) }}</TableCell>
          <TableCell class="text-right font-medium">
            <ColorizedChange :value="bond.change" />
          </TableCell>
          <TableCell class="text-right font-medium">
            <ColorizedChange :value="bond.changePercent" is-percentage />
          </TableCell>
          <TableCell class="flex justify-center items-center py-1">
            <AssetSparkline :chart-data="bond.chartData" :change-value="bond.change" />
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
