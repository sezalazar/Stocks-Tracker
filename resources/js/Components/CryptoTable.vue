<script setup lang="ts">
import BaseDashboardTable from '@/Components/BaseDashboardTable.vue'
import type { StockOrCryptoItem } from '@/types/stock'

const props = defineProps<{ data: StockOrCryptoItem[] }>()
</script>

<template>
  <BaseDashboardTable :data="data" noResultsMessage="No crypto results." />
</template>
