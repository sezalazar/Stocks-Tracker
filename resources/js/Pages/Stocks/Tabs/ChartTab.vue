<script setup lang="ts">
import { defineProps } from 'vue'
import { LineChart } from '@/Components/ui/chart-line'

const props = defineProps<{
  chartData: Array<{ date: string; close: number }>
}>()

const yFormatter = (tick: unknown) => {
  return typeof tick === 'number' ? `$${new Intl.NumberFormat('us').format(tick)}` : ''
}
</script>

<template>
  <div class="p-6">
    <h3 class="text-xl font-semibold mb-4">Stock Price Chart</h3>
    <LineChart
      :data="chartData"
      index="date"
      :categories="['close']"
      :y-formatter="yFormatter"
      :y-scale="1.3"
      class="h-64"
    />
  </div>
</template>
