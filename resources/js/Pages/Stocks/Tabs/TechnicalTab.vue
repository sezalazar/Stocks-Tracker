<script setup lang="ts">
import { defineProps, onMounted } from 'vue'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/Components/ui/tabs'
import { LineChart } from '@/Components/ui/chart-line'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

const props = defineProps<{
    chartData: Array<{ date: string; close: number }>
    rsiChartData: Array<{ date: string; rsi: number }>
    macdChartData: Array<{ date: string; macd: number; signal: number; histogram: number }>
    rsiData: Array<{ data_timestamp: string; value: number }>
    macdData: Array<{ data_timestamp: string; value: number; signal: number; histogram: number }>
}>()

const yFormatterPrice = (tick: unknown) => {
    return typeof tick === 'number' ? `$${new Intl.NumberFormat('us').format(tick)}` : ''
}
</script>

<template>
    <div class="p-6">
        <h3 class="text-xl font-semibold mb-4">Technical Indicators</h3>
        <Tabs default-value="rsi" class="w-full space-y-4">
            <TabsList class="flex border-b">
                <TabsTrigger value="rsi" class="py-2 px-3 text-sm font-medium hover:text-blue-600">RSI</TabsTrigger>
                <TabsTrigger value="macd" class="py-2 px-3 text-sm font-medium hover:text-blue-600">MACD</TabsTrigger>
            </TabsList>

            <TabsContent value="rsi">
                <h4 class="text-lg font-semibold">RSI Chart</h4>
                <div class="flex flex-col space-y-4 mt-2">
                    <div class="bg-white rounded shadow-sm p-4">
                        <LineChart
                            :data="chartData"
                            index="date"
                            :categories="['close']"
                            :y-scale="1.3"
                            :y-formatter="yFormatterPrice"
                            class="h-48"
                        />
                    </div>
                    <div class="bg-white rounded shadow-sm p-4">
                        <LineChart
                            :data="rsiChartData"
                            index="date"
                            :categories="['rsi']"
                            :horizontal-lines="[30, 70]"
                            :y-formatter="(tick) => (typeof tick === 'number' ? tick.toFixed(2) : '')"
                            class="h-48"
                        />
                    </div>
                </div>
                <Table class="mt-6">
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-left">Date</TableHead>
                            <TableHead class="text-left">RSI Value</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <tr v-for="(item, idx) in rsiData" :key="idx" class="hover:bg-gray-50">
                            <TableCell>{{ new Date(item.data_timestamp).toLocaleDateString() }}</TableCell>
                            <TableCell :class="item.value > 70 ? 'text-red-500' : item.value < 30 ? 'text-green-500' : ''">
                                {{ typeof item.value === 'number' ? item.value.toFixed(2) : '-' }}
                            </TableCell>
                        </tr>
                    </TableBody>
                </Table>
            </TabsContent>

            <TabsContent value="macd">
                <h4 class="text-lg font-semibold">MACD</h4>
                <div class="bg-white rounded shadow-sm p-4 mt-2">
                    <LineChart
                        :data="macdChartData"
                        index="date"
                        :categories="['macd', 'signal', 'histogram']"
                        :y-formatter="(tick) => (typeof tick === 'number' ? tick.toFixed(2) : '')"
                        class="h-64"
                    />
                </div>
                <Table class="mt-6">
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-left">Date</TableHead>
                            <TableHead class="text-left">MACD</TableHead>
                            <TableHead class="text-left">Signal</TableHead>
                            <TableHead class="text-left">Histogram</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <tr v-for="(item, idx) in macdData" :key="idx" class="hover:bg-gray-50">
                            <TableCell>{{ new Date(item.data_timestamp).toLocaleDateString() }}</TableCell>
                            <TableCell>{{ typeof item.value === 'number' ? item.value.toFixed(2) : '-' }}</TableCell>
                            <TableCell>{{ typeof item.signal === 'number' ? item.signal.toFixed(2) : '-' }}</TableCell>
                            <TableCell :class="item.histogram > 0 ? 'text-green-500' : item.histogram < 0 ? 'text-red-500' : ''">
                                {{ typeof item.histogram === 'number' ? item.histogram.toFixed(2) : '-' }}
                            </TableCell>
                        </tr>
                    </TableBody>
                </Table>
            </TabsContent>
        </Tabs>
    </div>
</template>
