<script setup lang="ts">
import { ref, computed, defineProps } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ChartTab from './Tabs/ChartTab.vue'
import PricesTab from './Tabs/PricesTab.vue'
import TechnicalTab from './Tabs/TechnicalTab.vue'
import FinancialTab from './Tabs/FinancialTab.vue'
import { Button } from '@/Components/ui/button'
import { Card } from '@/Components/ui/card'

// Importar tipos
import type {
  StockData,
  RsiItem,
  MacdItem,
  CompanyData,
  FinancialStatementItem,
  FinancialRecord
} from '@/types/stock'

const props = defineProps<{
  symbol: string;
  stockData: StockData;
  companyData: CompanyData;
  rsiData: RsiItem[];
  macdData: MacdItem[];
  financialData: FinancialStatementItem[];
  lastTwoCloses: { lastClose?: number | null; prevClose?: number | null };
}>()

const chartData = computed(() => {
  if (!props.stockData?.data) return []
  const sorted = [...props.stockData.data].sort(
    (a, b) => new Date(a.date).getTime() - new Date(b.date).getTime()
  )
  return sorted.map(item => ({ date: item.date, close: item.close }))
})
const prices = computed(() => props.stockData?.data || [])

const rsiChartData = computed(() => {
  const sorted = [...props.rsiData].sort(
    (a, b) => new Date(a.data_timestamp).getTime() - new Date(b.data_timestamp).getTime()
  )
  return sorted.map(item => ({
    date: new Date(item.data_timestamp).toISOString().split('T')[0],
    rsi: item.value
  }))
})
const macdChartData = computed(() => {
  const sorted = [...props.macdData].sort(
    (a, b) => new Date(a.data_timestamp).getTime() - new Date(b.data_timestamp).getTime()
  )
  return sorted.map(item => ({
    date: new Date(item.data_timestamp).toISOString().split('T')[0],
    macd: item.value,
    signal: item.signal,
    histogram: item.histogram
  }))
})

const formattedMarketCap = computed(() => {
  const cap = props.companyData?.results?.market_cap ?? 0
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    notation: 'compact'
  }).format(cap)
})

const priceChangeData = computed(() => {
  const { lastClose, prevClose } = props.lastTwoCloses
  if (lastClose == null || prevClose == null) return null
  const changePercent = ((lastClose - prevClose) / prevClose) * 100
  return { lastClose, changePercent }
})
const priceChangeClass = computed(() => {
  if (!priceChangeData.value) return ''
  return priceChangeData.value.changePercent > 0
    ? 'text-green-600'
    : priceChangeData.value.changePercent < 0
      ? 'text-red-600'
      : ''
})
const priceChangeSymbol = computed(() => {
  if (!priceChangeData.value) return ''
  return priceChangeData.value.changePercent > 0 ? '+' : ''
})
const formattedPriceChange = computed(() => {
  if (!priceChangeData.value) return ''
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(priceChangeData.value.lastClose)
})

const financials = computed<FinancialRecord[]>(() => {
  return props.financialData.map(item => ({
    date: item.date,
    revenue: item.revenue ?? 0,
    netIncome: item.net_income ?? 0,
    eps: item.eps ?? 0,
    grossProfit: item.gross_profit ?? 0,
    grossProfitRatio: item.gross_profit_ratio ?? 0,
    costOfRevenue: item.cost_of_revenue ?? 0,
    researchAndDevelopmentExpenses: item.research_and_development_expenses ?? 0,
    generalAndAdministrativeExpenses: item.general_and_administrative_expenses ?? 0,
    
  }))
})

const distinctDates = computed(() => {
  const dates = financials.value.map(item => item.date)
  return Array.from(new Set(dates)).sort((a, b) => new Date(a).getTime() - new Date(b).getTime())
})

const uniqueYears = computed(() => {
  const years = props.financialData.map(item => new Date(item.date).getFullYear())
  return Array.from(new Set(years)).sort()
})

const epsChartData = computed(() => {
  const map: Record<number, number> = {}
  props.financialData.forEach(item => {
    const year = new Date(item.date).getFullYear()
    if (item.eps !== null) {
      // Convertir a número
      map[year] = Number(item.eps)
    }
  })
  return uniqueYears.value.map(year => ({ year, EPS: map[year] ?? 0 }))
})

const incomeChartData = computed(() => {
  const map: Record<number, { revenue: number, netIncome: number }> = {}
  props.financialData.forEach(item => {
    const year = new Date(item.date).getFullYear()
    if (item.revenue !== null || item.net_income !== null) {
      map[year] = { revenue: item.revenue ?? 0, netIncome: item.net_income ?? 0 }
    }
  })
  return uniqueYears.value.map(year => ({
    year,
    Ingresos: map[year]?.revenue ?? 0,
    Utilidad: map[year]?.netIncome ?? 0
  }))
})

interface Metric {
  key: keyof FinancialRecord;
  label: string;
  formatter: (value: number) => string;
  color?: (value: number) => string;
}
const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}
const formatDecimal = (value: number): string => {
  return value.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })
}
const getEpsColor = (eps: number): string => {
  if (eps < 0) return 'text-red-500'
  if (eps < 1) return 'text-yellow-500'
  if (eps < 5) return 'text-green-500'
  return 'text-emerald-500'
}
const metrics = computed<Metric[]>(() => [
  { key: 'revenue', label: 'Revenue', formatter: formatNumber },
  { key: 'netIncome', label: 'Net Income', formatter: formatNumber },
  { key: 'eps', label: 'EPS', formatter: formatDecimal, color: getEpsColor },
  { key: 'grossProfit', label: 'Gross Profit', formatter: formatNumber },
  { key: 'grossProfitRatio', label: 'Gross Profit Ratio', formatter: formatDecimal },
  { key: 'costOfRevenue', label: 'Cost of Revenue', formatter: formatNumber },
  { key: 'researchAndDevelopmentExpenses', label: 'R&D Expenses', formatter: formatNumber },
  { key: 'generalAndAdministrativeExpenses', label: 'G&A Expenses', formatter: formatNumber },
])


const tabs = [
  { key: 'chart', label: 'Chart' },
  { key: 'prices', label: 'Prices' },
  { key: 'technical', label: 'Technical Indicators' },
  { key: 'financial', label: 'Financial Statements' }
] as const
type TabKey = (typeof tabs)[number]['key']
const selectedTab = ref<TabKey>('chart')
</script>

<template>
  <Head title="Stocks" />
  <AuthenticatedLayout>
    <div class="p-6 space-y-8 bg-gray-100 min-h-screen">
      <!-- Header: Company Information -->
      <div class="p-6 space-y-8 bg-gradient-to-r from-blue-50 via-white to-gray-100 rounded-xl shadow-lg">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <div class="flex items-center gap-6">
            <div v-if="companyData.results?.logo_url" class="w-20 h-20 flex-shrink-0 bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
              <img :src="companyData.results.logo_url" alt="Logo" class="object-contain w-full h-full" />
            </div>
            <div>
              <h1 class="text-5xl font-extrabold text-gray-900">
                {{ companyData.results?.name ?? symbol }}
              </h1>
              <p class="text-lg text-gray-500 font-medium">
                {{ companyData.results?.ticker }}
              </p>
              <div v-if="priceChangeData" class="flex items-center space-x-2 mt-3">
                <span class="text-2xl font-bold text-gray-900">
                  {{ formattedPriceChange }}
                </span>
                <span :class="['text-lg font-semibold', priceChangeClass]">
                  {{ priceChangeSymbol }}{{ priceChangeData.changePercent.toFixed(2) }}%
                </span>
              </div>
              <div v-else class="text-sm text-gray-500 mt-2">
                No data available for price change.
              </div>
            </div>
          </div>
          <div class="flex flex-col items-end space-y-2">
            <span class="text-lg text-gray-600 font-medium">
              Market Cap:
              <span class="text-gray-900 font-bold">
                {{ formattedMarketCap }}
              </span>
            </span>
            <Button
              variant="outline"
              as="a"
              :href="companyData.results?.homepage_url"
              target="_blank"
              v-if="companyData.results?.homepage_url"
              class="text-base font-semibold px-6 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition duration-300"
            >
              Visit Official Site
            </Button>
          </div>
        </div>
      </div>

      <!-- About the Company -->
      <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
        <h2 class="text-2xl font-semibold text-gray-900">About the Company</h2>
        <p class="mt-2 text-gray-600 leading-relaxed">
          {{ companyData.results?.description ?? 'No description available.' }}
        </p>
      </div>

      <!-- Tabs Navigation -->
      <div>
        <div class="flex space-x-4 border-b">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="selectedTab = tab.key"
            :class="selectedTab === tab.key ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-3 py-2 border-b-2 focus:outline-none"
          >
            {{ tab.label }}
          </button>
        </div>
        <div class="pt-4 space-y-8">
          <!-- Chart Tab -->
          <ChartTab v-if="selectedTab === 'chart'" :chartData="chartData" />
          <!-- Prices Tab -->
          <PricesTab v-if="selectedTab === 'prices'" :prices="prices" />
          <!-- Technical Indicators Tab -->
          <TechnicalTab
            v-if="selectedTab === 'technical'"
            :chartData="chartData"
            :rsiChartData="rsiChartData"
            :macdChartData="macdChartData"
            :rsiData="props.rsiData"
            :macdData="props.macdData"
          />
          <!-- Financial Tab -->
          <FinancialTab 
            v-if="selectedTab === 'financial'"
            :financials="financials" 
            :distinctDates="distinctDates" 
            :metrics="metrics" 
            :financialDataByDate="financials.reduce((acc, curr) => ({ ...acc, [curr.date]: curr }), {})"
            :epsChartData="epsChartData"
            :incomeChartData="incomeChartData"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
