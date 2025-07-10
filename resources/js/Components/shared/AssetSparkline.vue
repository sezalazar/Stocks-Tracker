<script setup>
import { computed } from 'vue'
import { LineChart } from '@/Components/ui/chart-line'

const props = defineProps({
    chartData: {
        type: Array,
        default: () => [],
    },
    changeValue: {
        type: Number,
        default: 0,
    },
})

const chartColor = computed(() => {
    return props.changeValue >= 0 ? 'hsl(var(--primary))' : 'hsl(var(--destructive))'
})

const formattedChartData = computed(() => {
    return props.chartData.map((value, index) => ({ day: index, price: value }))
})
</script>

<template>
    <LineChart
        v-if="chartData && chartData.length > 1"
        class="h-10 w-32"
        index="day"
        :categories="['price']"
        :data="formattedChartData"
        :show-y-axis="false"
        :show-x-axis="false"
        :show-legend="false"
        :show-grid-line="false"
        :show-tooltip="false"
        :colors="[chartColor]"
    />
</template>
