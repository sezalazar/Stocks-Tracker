import { defineStore } from 'pinia'
  import { ref, computed, type Ref } from 'vue'
  import type { StockOrCryptoItem } from '@/types/stock'

  export interface MarketData {
    fearAndGreed: unknown | null
  }

  export interface DashboardLists {
    cryptoList: StockOrCryptoItem[]
    stocksList: StockOrCryptoItem[]
  }

  /* ───── Store ───── */
  export const useDashboardStore = defineStore('dashboard', () => {
    const cryptoList: Ref<StockOrCryptoItem[]> = ref([])
    const stocksList: Ref<StockOrCryptoItem[]> = ref([])
    const marketData = ref<MarketData | null>(null)

    const isHydrated     = computed(() => cryptoList.value.length > 0 || stocksList.value.length > 0)
    const getCryptoList  = computed(() => cryptoList.value)
    const getStocksList  = computed(() => stocksList.value)
    const getMarketData  = computed(() => marketData.value)

    function setData (data: Partial<DashboardLists & { marketData: MarketData | null }>) {
      if (data.cryptoList)  cryptoList.value = data.cryptoList
      if (data.stocksList)  stocksList.value = data.stocksList
      if (Object.prototype.hasOwnProperty.call(data, 'marketData')) {
        marketData.value = data.marketData ?? null
      }
    }

    return {
      // state
      cryptoList,
      stocksList,
      marketData,

      // actions
      setData,

      // getters
      isHydrated,
      getCryptoList,
      getStocksList,
      getMarketData,
    }
  })