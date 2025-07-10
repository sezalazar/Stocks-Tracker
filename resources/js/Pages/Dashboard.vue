<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DashboardTabs from '@/Components/DashboardTabs.vue'
import CryptoTab from '@/Pages/DashboardTabs/CryptoTab.vue'
import BondsTab from '@/Pages/DashboardTabs/BondsTab.vue'
import MervalTab from '@/Pages/DashboardTabs/MervalTab.vue'
import OptionsTab from '@/Pages/DashboardTabs/OptionsTab.vue'
import WebhookTab from '@/Pages/DashboardTabs/WebhookTab.vue'
import StocksTab from '@/Pages/DashboardTabs/StocksTab.vue';
import { usePage, Head } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <DashboardTabs>
                    <template #crypto><CryptoTab :data="page.props.cryptoList" /></template>
                    <template #stocks><StocksTab :stocks-list="page.props.stocksList" :market-data="page.props.marketData" /></template>
                    <template #merval><MervalTab /></template>
                    <template #bonds><BondsTab /></template>
                    <template #options><OptionsTab /></template>
                    <template #webhook><WebhookTab /></template>
                </DashboardTabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
