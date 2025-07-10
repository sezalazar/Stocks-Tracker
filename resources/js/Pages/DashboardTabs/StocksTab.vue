<script setup>
import { ref, defineAsyncComponent } from 'vue'
import axios from 'axios'
import StocksTable from '@/Components/StocksTable.vue'
import FgiCard from '@/Components/Fear&Greed.vue'

const StockDetail = defineAsyncComponent(() => import('@/Components/StockDetail.vue'))

const props = defineProps({
    stocksList: Array,
    marketData: Object,
})

const selectedStock = ref(null)
const isLoadingStock = ref(false)

async function viewStockDetail(symbol) {
    isLoadingStock.value = true
    try {
        const response = await axios.get(`/stock-data/${symbol}`)
        selectedStock.value = response.data
    } catch (error) {
        console.error('Error fetching stock detail:', error)
    } finally {
        isLoadingStock.value = false
    }
}
</script>

<template>
    <div>
        <div v-if="isLoadingStock" class="text-center p-10">Loading data...</div>

        <div v-else-if="!selectedStock">
            <div class="max-w-4xl mx-auto">
                <FgiCard :data="marketData.fearAndGreed" />
            </div>
            <StocksTable :data="stocksList" @view-stock="viewStockDetail" />
        </div>

        <div v-else>
            <StockDetail v-bind="selectedStock" />
        </div>
    </div>
</template>
