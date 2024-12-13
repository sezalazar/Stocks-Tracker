<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { LineChart } from '@/components/ui/chart-line'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Button } from '@/components/ui/button'

interface StockItem {
  date: string
  open: number
  high: number
  low: number
  close: number
  volume: number
}

interface StockData {
  meta?: {
    date_from?: string
    date_to?: string
  }
  data?: StockItem[]
}

interface RsiItem {
  symbol: string
  timespan: string
  data_timestamp: string
  value: number
}

interface CompanyAddress {
  address1: string
  city: string
  state: string
  postal_code: string
}

interface CompanyBranding {
  logo_url?: string
  icon_url?: string
}

interface CompanyResults {
  ticker: string
  name: string
  description: string
  market_cap: number
  homepage_url?: string
  branding?: CompanyBranding
  address?: CompanyAddress
}

interface CompanyData {
  request_id?: string
  status?: string
  results?: CompanyResults
}

interface FinancialStatementItem {
  date: string
  revenue: number | null
  gross_profit: number | null
  gross_profit_ratio: number | null
  net_income: number | null
  eps: number | null
}

// Definimos un tipo para las claves métricas
type MetricKey = 'revenue' | 'gross_profit' | 'gross_profit_ratio' | 'net_income' | 'eps';

interface Metric {
  key: MetricKey
  label: string
  formatter: (v: number|null) => string
  color?: (eps: number|null) => string
}

const props = defineProps<{
  symbol: string
  stockData: StockData
  companyData: CompanyData
  rsiData: RsiItem[]
  financialData: FinancialStatementItem[]
}>()

const chartData = computed(() => {
  if (!props.stockData?.data) return []
  
  const sortedData = [...props.stockData.data].sort((a, b) => {
    return new Date(a.date).getTime() - new Date(b.date).getTime()
  })
  
  return sortedData.map((item: StockItem) => {
    const dateObj = new Date(item.date)
    const year = dateObj.getFullYear()
    const month = String(dateObj.getMonth() + 1).padStart(2, '0')
    const day = String(dateObj.getDate()).padStart(2, '0')
    return {
      date: `${year}-${month}-${day}`,
      close: item.close,
    }
  })
})

const formattedMarketCap = computed(() => {
  const cap = props.companyData?.results?.market_cap ?? 0
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', notation: 'compact' }).format(cap)
})

const formatNumber = (value: number | null): string => {
  if (value === null) return '-'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
  }).format(value)
}

const formatRatio = (value: number | null): string => {
  if (value === null) return '-'
  return `${(value * 100).toFixed(2)}%`
}

const getEpsColor = (eps: number | null): string => {
  if (eps === null) return 'text-gray-500'
  if (eps < 0) return 'text-red-500'
  if (eps < 1) return 'text-yellow-500'
  if (eps < 5) return 'text-green-500'
  return 'text-emerald-500'
}

const financialDates = computed(() => props.financialData.map(item => item.date))

const metrics = computed<Metric[]>(() => {
  return [
    { key: 'revenue', label: 'Revenue', formatter: formatNumber },
    { key: 'gross_profit', label: 'Gross Profit', formatter: formatNumber },
    { key: 'gross_profit_ratio', label: 'Gross Profit Ratio', formatter: formatRatio },
    { key: 'net_income', label: 'Net Income', formatter: formatNumber },
    { key: 'eps', label: 'EPS', formatter: (v: number|null) => v !== null ? v.toFixed(2) : '-', color: getEpsColor },
  ]
})

const financialDataByDate = computed(() => {
  const dataMap: Record<string, FinancialStatementItem> = {}
  for (const item of props.financialData) {
    dataMap[item.date] = item
  }
  return dataMap
})

// Definimos un tipo para lastTwo
interface LastTwoData {
  lastClose: number
  changePercent: number
}

const lastTwo = computed<LastTwoData|null>(() => {
  if (!props.stockData?.data || props.stockData.data.length < 2) return null
  const sorted = [...props.stockData.data].sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
  const last = sorted[sorted.length - 1]
  const prev = sorted[sorted.length - 2]
  const changePercent = ((last.close - prev.close) / prev.close) * 100
  return {
    lastClose: last.close,
    changePercent
  }
})

const priceChangeClass = computed(() => {
  if (!lastTwo.value) return ''
  const cp = lastTwo.value.changePercent
  if (cp > 0) return 'text-green-500'
  if (cp < 0) return 'text-red-500'
  return ''
})
</script>

<template>
  <Head title="Stocks" />
  <div class="p-6 space-y-8">
    <div class="p-6 space-y-8 bg-gray-50 rounded-lg shadow-md">
      <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <!-- Logo and Stock Info -->
        <div class="flex items-center gap-6">
          <div v-if="props.companyData.results?.branding?.logo_url" class="w-16 h-16 flex-shrink-0 bg-white rounded-full shadow-md overflow-hidden">
            <img :src="props.companyData.results.branding.logo_url" alt="Logo" class="object-cover w-full h-full" />
          </div>
          <div>
            <h1 class="text-4xl font-extrabold text-gray-900">{{ props.companyData.results?.name ?? props.symbol }}</h1>
            <p class="text-sm text-gray-500">{{ props.companyData.results?.ticker }}</p>
            <div class="flex items-center gap-2 mt-2">
              <span v-if="lastTwo.value" class="text-2xl font-semibold text-gray-800">
                {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(lastTwo.value.lastClose) }}
              </span>
              <span :class="['text-sm font-semibold', priceChangeClass]">
                <template v-if="lastTwo.value">
                  ({{ lastTwo.value.changePercent.toFixed(2) }}%)
                </template>
                <template v-else>-</template>
              </span>
            </div>
          </div>
        </div>
        
        <!-- Market Cap and Website -->
        <div class="flex flex-col items-end space-y-2">
          <span class="text-sm text-gray-500">Market Cap: {{ formattedMarketCap }}</span>
          <Button
            variant="outline"
            as="a"
            :href="props.companyData.results?.homepage_url"
            target="_blank"
            v-if="props.companyData.results?.homepage_url"
            class="text-sm font-medium hover:bg-blue-50 hover:text-blue-600"
          >
            Visit Official Site
          </Button>
        </div>
      </div>

      <!-- Description Section -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold text-gray-900">About the Company</h2>
        <p class="mt-2 text-sm text-gray-600">
          {{ props.companyData.results?.description }}
        </p>
      </div>
    </div>

    <Separator class="my-6" />

    <Tabs default-value="chart" class="w-full">
      <TabsList class="mb-4">
        <TabsTrigger value="chart">Chart</TabsTrigger>
        <TabsTrigger value="prices">Prices</TabsTrigger>
        <TabsTrigger value="rsi">RSI</TabsTrigger>
        <TabsTrigger value="financial">Financial Statements</TabsTrigger>
      </TabsList>
      <TabsContent value="chart">
        <div class="bg-white shadow rounded-lg p-4">
          <LineChart
            :data="chartData"
            index="date"
            :categories="['close']"
            :y-formatter="(tick) => typeof tick === 'number' ? `$ ${new Intl.NumberFormat('us').format(tick)}` : ''"
          />
        </div>
      </TabsContent>
      <TabsContent value="prices">
        <div class="bg-white shadow rounded-lg overflow-x-auto p-4">
          <Table>
            <TableCaption>Precios Históricos</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[120px]">Fecha</TableHead>
                <TableHead>Open</TableHead>
                <TableHead>High</TableHead>
                <TableHead>Low</TableHead>
                <TableHead>Close</TableHead>
                <TableHead>Volume</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="(item, index) in props.stockData.data || []"
                :key="index"
                class="hover:bg-gray-50"
              >
                <TableCell class="font-medium">{{ new Date(item.date).toLocaleDateString() }}</TableCell>
                <TableCell>{{ item.open }}</TableCell>
                <TableCell>{{ item.high }}</TableCell>
                <TableCell>{{ item.low }}</TableCell>
                <TableCell>{{ item.close }}</TableCell>
                <TableCell>{{ item.volume }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </TabsContent>
      <TabsContent value="rsi">
        <div class="bg-white shadow rounded-lg overflow-x-auto p-4">
          <Table>
            <TableCaption>RSI (día)</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[120px]">Fecha</TableHead>
                <TableHead>RSI Value</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="(item, index) in props.rsiData || []"
                :key="index"
                class="hover:bg-gray-50"
              >
                <TableCell class="font-medium">
                  {{ new Date(item.data_timestamp).toLocaleDateString() }}
                </TableCell>
                <TableCell>{{ item.value.toFixed(2) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </TabsContent>
      <TabsContent value="financial">
        <div class="bg-white shadow rounded-lg overflow-x-auto p-4">
          <Table>
            <TableCaption>Últimos Estados Financieros</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[120px]">Métrica</TableHead>
                <TableHead
                  v-for="(d, i) in financialDates"
                  :key="i"
                >
                  {{ new Date(d).toLocaleDateString() }}
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="(metric, mIndex) in metrics"
                :key="mIndex"
                class="hover:bg-gray-50"
              >
                <TableCell class="font-medium">{{ metric.label }}</TableCell>
                <TableCell
                  v-for="(d, di) in financialDates"
                  :key="di"
                  :class="metric.key === 'eps' && metric.color ? metric.color(financialDataByDate[d]?.[metric.key] ?? null) : ''"
                >
                  {{ metric.formatter(financialDataByDate[d]?.[metric.key] ?? null) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </TabsContent>
    </Tabs>
  </div>
</template>
