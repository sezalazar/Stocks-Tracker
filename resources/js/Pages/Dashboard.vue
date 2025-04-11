<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DashboardTabs from '@/Components/DashboardTabs.vue'

const props = defineProps<{
  cryptoList: any[],
  stocksList: any[]
}>()
</script>

<template>
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
        <DashboardTabs
          :crypto-list="cryptoList"
          :stocks-list="stocksList"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
