<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/Components/ui/tabs'
import CryptoTable from './CryptoTable.vue'
import StocksTable from './StocksTable.vue'
import FgiCard from '@/Components/Fear&Greed.vue'
import BondsTab from '../Pages/Bonds/BondsTab.vue'
import OptionsTab from '../Pages/Options/OptionsTab.vue'
import WebhookTab from '../Pages/Options/WebhookTab.vue'

import {
    LineChartIcon,
    FlagIcon,
    BanknoteIcon,
    PuzzleIcon,
    BitcoinIcon,
    WebhookIcon
} from 'lucide-vue-next'

const props = defineProps<{
    cryptoList: any[]
    stocksList: any[]
    marketData: { fearAndGreed: any | null }
}>()

const page = usePage()
const tabFromQuery = computed(() => {
    const url = page.url
    const queryString = url.split('?')[1] || ''
    const params = new URLSearchParams(queryString)
    return params.get('tab') || 'crypto'
})
</script>

<template>
    <Tabs :default-value="tabFromQuery" class="w-full">
        <TabsList class="mb-4 flex h-auto justify-start overflow-x-auto rounded-t-lg bg-[hsl(var(--secondary))] p-0">
            <TabsTrigger
                value="crypto"
                class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4"
            >
                <BitcoinIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />
                Cryptos
            </TabsTrigger>
            <TabsTrigger
                value="stocks"
                class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4"
            >
                <LineChartIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />
                Stocks
            </TabsTrigger>
            <TabsTrigger
                value="merval"
                class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4"
            >
                <FlagIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />
                Merval
            </TabsTrigger>
            <TabsTrigger
                value="bonds"
                class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4"
            >
                <BanknoteIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />
                Bonds
            </TabsTrigger>
            <TabsTrigger
                value="options"
                class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4"
            >
                <PuzzleIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />
                Options
            </TabsTrigger>
            <TabsTrigger
                value="webhook"
                class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4"
            >
                <WebhookIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />
                Webhook
            </TabsTrigger>
        </TabsList>

        <TabsContent value="crypto"><CryptoTable :data="props.cryptoList" /></TabsContent>
        <TabsContent value="stocks">
          <div class="max-w-4xl mx-auto">
            <FgiCard :data="props.marketData.fearAndGreed" />
          </div>
          <StocksTable :data="props.stocksList" />
        </TabsContent>
        <TabsContent value="bonds"><BondsTab /></TabsContent>
        <TabsContent value="options"><OptionsTab /></TabsContent>
        <TabsContent value="webhook"><WebhookTab /></TabsContent>
    </Tabs>
</template>