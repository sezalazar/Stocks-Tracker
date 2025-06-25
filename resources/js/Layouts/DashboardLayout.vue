<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DashboardTabs from '@/Components/DashboardTabs.vue'
import type { PageProps } from '@inertiajs/core';

const page = usePage<PageProps>()

</script>

<template>
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
        <DashboardTabs
          :crypto-list="page.props.dashboardLists.cryptoList"
          :stocks-list="page.props.dashboardLists.stocksList"
          :market-data="page.props.marketData"
        />
        <main>
          <slot />
        </main>
      </div>
    </div>
  </AuthenticatedLayout>
</template>