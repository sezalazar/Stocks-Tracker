<script setup lang="ts">
import { ref, defineAsyncComponent } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DashboardTabs from '@/Components/DashboardTabs.vue'
import StocksTable from '@/Components/StocksTable.vue'
import CryptoTable from '@/Components/CryptoTable.vue'
import BondsTab from '@/Pages/Bonds/BondsTab.vue'
import OptionsTab from '@/Pages/Options/OptionsTab.vue'
import WebhookTab from '@/Pages/Options/WebhookTab.vue'
import FgiCard from '@/Components/Fear&Greed.vue'
import { usePage, Head } from '@inertiajs/vue3'

const StockDetail = defineAsyncComponent(() => import('@/Components/StockDetail.vue'))

const page = usePage()

const selectedStock = ref<any>(null) 
const isLoadingStock = ref(false)

async function viewStockDetail(symbol: string) {
    isLoadingStock.value = true
    try {
        const response = await axios.get(`/stock-data/${symbol}`);
        selectedStock.value = response.data
    } catch (error) {
        console.error("Error:", error);
    } finally {
        isLoadingStock.value = false
    }
}

function clearSelectedStock() {
    selectedStock.value = null
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <DashboardTabs @update:modelValue="clearSelectedStock">
                    
                    <template #crypto>
                        <CryptoTable :data="page.props.dashboardLists.cryptoList" />
                    </template>

                    <template #stocks>
                        <div v-if="isLoadingStock" class="text-center p-10">Loading data...</div>
                        
                        <div v-else-if="!selectedStock">
                            <div class="max-w-4xl mx-auto">
                                <FgiCard :data="page.props.marketData.fearAndGreed" />
                            </div>
                            <StocksTable 
                                :data="page.props.dashboardLists.stocksList" 
                                @view-stock="viewStockDetail"
                            />
                        </div>
                        
                        <div v-else>
                            <StockDetail v-bind="selectedStock" />
                        </div>
                    </template>

                    <template #merval><BondsTab /></template>
                    <template #bonds><BondsTab /></template>
                    <template #options><OptionsTab /></template>
                    <template #webhook><WebhookTab /></template>
                </DashboardTabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>