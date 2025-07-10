<script setup>
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/Components/ui/tabs'
import { BitcoinIcon, LineChartIcon, BanknoteIcon, FlagIcon, PuzzleIcon, WebhookIcon } from 'lucide-vue-next'

const emit = defineEmits(['update:modelValue'])

function onTabChange(value) {
    emit('update:modelValue', value)
}
</script>

<template>
    <Tabs default-value="crypto" @update:model-value="onTabChange" class="w-full">
        <TabsList class="mb-4 flex h-auto justify-start overflow-x-auto rounded-t-lg bg-[hsl(var(--secondary))] p-0">
             <TabsTrigger value="crypto" class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4">
                <BitcoinIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" /> Cryptos
            </TabsTrigger>
            <TabsTrigger value="stocks" class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4">
                <LineChartIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" /> Stocks
            </TabsTrigger>
            <TabsTrigger value="bonds" class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4">
                <BanknoteIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />Bonds
            </TabsTrigger>
            <TabsTrigger value="merval" class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4">
                <FlagIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />Merval
            </TabsTrigger>
            <TabsTrigger value="options" class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4">
                <PuzzleIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />Options
            </TabsTrigger>
            <TabsTrigger value="webhook" class="asset-tab-active-indicator relative flex-1 rounded-none px-2 py-3 text-[hsl(var(--secondary-foreground))] shadow-none hover:bg-[hsl(var(--nav-border))] focus-visible:ring-0 data-[state=active]:bg-transparent data-[state=active]:text-[hsl(var(--secondary-foreground))] data-[state=active]:shadow-none md:px-4">
                <WebhookIcon class="mb-0.5 mr-1 h-4 w-4 md:mr-2" />Webhook
            </TabsTrigger>
        </TabsList>

        <TabsContent value="crypto"><slot name="crypto" /></TabsContent>
        <TabsContent value="stocks"><slot name="stocks" /></TabsContent>
        <TabsContent value="merval"><slot name="merval" /></TabsContent>
        <TabsContent value="bonds"><slot name="bonds" /></TabsContent>
        <TabsContent value="options"><slot name="options" /></TabsContent>
        <TabsContent value="webhook"><slot name="webhook" /></TabsContent>
    </Tabs>
</template>