<script setup lang="ts">
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/Components/ui/tabs'
import CryptoTable from './CryptoTable.vue'
import StocksTable from './StocksTable.vue'
import OptionsTab from './OptionsTab.vue'

const props = defineProps<{
  cryptoList: any[],
  stocksList: any[],
}>()

const page = usePage()
const tabFromQuery = computed(() => {
  const url = page.url
  const queryString = url.split('?')[1] || ''
  const params = new URLSearchParams(queryString)
  return params.get('tab') || 'crypto'
})

function gotoStocks() {
  router.visit('/dashboard?tab=stocks')
}
function gotoCrypto() {
  router.visit('/dashboard?tab=crypto')
}
function gotoOptions() {
  router.visit(route('dashboard.options'))
}
</script>

<template>
  <Tabs :default-value="tabFromQuery" class="w-full">
    <TabsList>
      <TabsTrigger value="crypto">Cryptos</TabsTrigger>
      <TabsTrigger value="stocks">Stocks</TabsTrigger>
      <TabsTrigger value="options">Options</TabsTrigger>
    </TabsList>

    <TabsContent value="crypto"><CryptoTable :data="props.cryptoList" /></TabsContent>
    <TabsContent value="stocks"><StocksTable :data="props.stocksList" /></TabsContent>
    <TabsContent value="options"><OptionsTab /></TabsContent>
  </Tabs>
</template>
