<script setup lang="ts">
import BaseDashboardTable from '@/Components/BaseDashboardTable.vue'
import type { StockOrCryptoItem } from '@/types/stock'

const props = defineProps<{ data: StockOrCryptoItem[] }>()

defineEmits(['view-stock'])
</script>

<template>
  <BaseDashboardTable 
    :data="data" 
    noResultsMessage="No stocks results."
    @view-item="$emit('view-stock', $event)" 
  />
</template>