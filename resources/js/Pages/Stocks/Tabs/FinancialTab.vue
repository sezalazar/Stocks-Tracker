<script setup lang="ts">
import { defineProps, computed } from 'vue'
import type { FinancialRecord } from '@/types/stock'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card'
import { LineChart } from '@/Components/ui/chart-line'
import { BarChart } from '@/Components/ui/chart-bar'
import { DonutChart } from '@/Components/ui/chart-donut'
import { Tooltip, TooltipProvider, TooltipTrigger, TooltipContent } from '@/Components/ui/tooltip'

const props = defineProps<{
    financials: FinancialRecord[]
    distinctDates: string[]
    metrics: {
        key: keyof FinancialRecord
        label: string
        formatter: (value: number) => string
        color?: (value: number) => string
        description?: string
    }[]
    financialDataByDate: Record<string, FinancialRecord>
    epsChartData: { year: number; EPS: number }[]
    incomeChartData: { year: number; Ingresos: number; Utilidad: number }[]
}>()

const lastDate = computed(() => props.distinctDates[props.distinctDates.length - 1])
const lastFinancial = computed(() => props.financialDataByDate[lastDate.value])

const costCompositionData = computed(() => {
    if (!lastFinancial.value) return []
    return [
        { name: 'Cost of Revenue', value: lastFinancial.value.costOfRevenue },
        { name: 'R&D Expenses', value: lastFinancial.value.researchAndDevelopmentExpenses },
        { name: 'G&A Expenses', value: lastFinancial.value.generalAndAdministrativeExpenses },
    ]
})

const formatDate = (dateStr: string): string => {
    const date = new Date(dateStr)
    return isNaN(date.getTime()) ? dateStr : date.toLocaleDateString()
}
const formatNumber = (value: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value)
}
const getColorClass = (value: number): string => {
    return value > 0 ? 'text-green-600' : value < 0 ? 'text-red-600' : 'text-gray-600'
}
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
                <CardHeader>
                    <CardTitle>Revenue (Last Period)</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ formatNumber(lastFinancial?.revenue ?? 0) }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>Net Income (Last Period)</CardTitle>
                </CardHeader>
                <CardContent>
                    <p :class="['text-2xl font-bold', getColorClass(Number(lastFinancial?.netIncome ?? 0))]">
                        {{ formatNumber(Number(lastFinancial?.netIncome ?? 0)) }}
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>EPS (Last Period)</CardTitle>
                </CardHeader>
                <CardContent>
                    <p :class="['text-2xl font-bold', getColorClass(Number(lastFinancial?.eps ?? 0))]">
                        {{ Number(lastFinancial?.eps ?? 0).toFixed(2) }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Card>
                <CardHeader>
                    <CardTitle>Anual EPS</CardTitle>
                    <CardDescription>Earnings Per Share (last 4 years)</CardDescription>
                </CardHeader>
                <CardContent>
                    <LineChart
                        :data="epsChartData"
                        index="year"
                        :categories="['EPS']"
                        :colors="['blue']"
                        :showLegend="false"
                        :showTooltip="true"
                        :x-formatter="(tick: number | Date) => {
              if (typeof tick === 'number') {
                return props.epsChartData[Math.round(tick)]?.year.toString() || 'N/A'
              }
              return tick.toLocaleDateString()
            }"
                        :y-formatter="(tick: number | Date) => typeof tick === 'number' ? tick.toFixed(2) : '-'"
                        class="h-64"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Financial Comparison</CardTitle>
                    <CardDescription>Revenue vs Annual Net Income</CardDescription>
                </CardHeader>
                <CardContent>
                    <BarChart
                        :data="incomeChartData"
                        index="year"
                        :categories="['Ingresos', 'Utilidad']"
                        :colors="['blue', 'teal']"
                        type="grouped"
                        :showLegend="true"
                        :showTooltip="true"
                        :y-formatter="(tick: number | Date) => typeof tick === 'number' ? `$${tick}M` : '-'"
                        class="h-64"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Cost Composition (Last period)</CardTitle>
                    <CardDescription>Operating Cost Distribution</CardDescription>
                </CardHeader>
                <CardContent>
                    <DonutChart
                        :data="costCompositionData"
                        index="name"
                        category="value"
                        :colors="['#FF6384', '#36A2EB', '#FFCE56']"
                        :showLegend="true"
                        :showTooltip="true"
                        class="h-64"
                    />
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Financial Details</CardTitle>
                <CardDescription>Key Metrics History</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 font-medium">
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger>Metric</TooltipTrigger>
                                            <TooltipContent>
                                                <p>The company’s key financial metrics.</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </th>
                                <th v-for="(date, index) in distinctDates" :key="index" class="px-4 py-3 font-medium whitespace-nowrap">
                                    {{ formatDate(date) }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(metric, idx) in metrics" :key="idx" class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger>{{ metric.label }}</TooltipTrigger>
                                            <TooltipContent>
                                                <p>{{ metric.description || 'Sin descripción.' }}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </td>
                                <td
                                    v-for="(date, index) in distinctDates"
                                    :key="index"
                                    class="px-4 py-3 font-medium whitespace-nowrap"
                                    :class="metric.color ? metric.color(Number(financialDataByDate[date]?.[metric.key] ?? 0)) : ''"
                                >
                                    {{ metric.formatter(Number(financialDataByDate[date]?.[metric.key] ?? 0)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
