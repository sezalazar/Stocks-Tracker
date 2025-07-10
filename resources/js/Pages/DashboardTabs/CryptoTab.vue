<script setup>
import { computed } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { rowLinkStyle } from '@/utils/styleHelpers';
import ColorizedChange from '@/Components/shared/ColorizedChange.vue';
import AssetSparkline from '@/Components/shared/AssetSparkline.vue';
import { formatNumber, formatPrice } from '@/utils/formatters';

const props = defineProps({
    data: Array,
});

const sortedData = computed(() => {
    if (!props.data) return [];
    return [...props.data].sort((a, b) => {
        const rankA = a.daily?.marketCapRank ?? Infinity;
        const rankB = b.daily?.marketCapRank ?? Infinity;
        return rankA - rankB;
    });
});
</script>

<template>
    <div class="rounded-lg border bg-card">
        <Table>
            <TableHeader>
                <TableRow class="border-border hover:bg-transparent">
                    <TableHead class="w-[50px] text-center text-muted-foreground uppercase tracking-wider text-xs">Rank</TableHead>
                    <TableHead class="text-left text-muted-foreground uppercase tracking-wider text-xs">Name</TableHead>
                    <TableHead class="text-right text-muted-foreground uppercase tracking-wider text-xs">Price</TableHead>
                    <TableHead class="text-right text-muted-foreground uppercase tracking-wider text-xs">24h %</TableHead>
                    <TableHead class="text-right text-muted-foreground uppercase tracking-wider text-xs">Market Cap</TableHead>
                    <TableHead class="text-center text-muted-foreground uppercase tracking-wider text-xs">Last 7 Days</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-if="!sortedData || sortedData.length === 0" class="border-border">
                    <TableCell colspan="6" class="text-center text-muted-foreground py-10">No crypto data available.</TableCell>
                </TableRow>
                <TableRow 
                    v-else
                    v-for="crypto in sortedData" 
                    :key="crypto.symbol" 
                    :class="['border-border', rowLinkStyle()]"
                >
                    <TableCell class="text-center font-medium text-muted-foreground">{{ crypto.daily.marketCapRank }}</TableCell>
                    
                    <TableCell>
                        <div class="flex items-center gap-3">
                            <img v-if="crypto.daily.image" :src="`/storage/${crypto.daily.image}`" :alt="`${crypto.symbol} logo`" class="h-6 w-6 rounded-full bg-secondary" />
                            <div>
                                <div class="font-semibold">{{ crypto.daily.name || crypto.symbol }}</div>
                                <div class="text-xs text-muted-foreground">{{ crypto.symbol }}</div>
                            </div>
                        </div>
                    </TableCell>
                    
                    <TableCell class="text-right font-semibold">${{ formatPrice(crypto.daily.price) }}</TableCell>
                    
                    <TableCell class="text-right font-medium">
                        <ColorizedChange :value="crypto.daily.priceChangePercentage24h" is-percentage />
                    </TableCell>
                    
                    <TableCell class="text-right">{{ formatNumber(crypto.daily.marketCap) }}</TableCell>
                    
                    <TableCell class="flex justify-center items-center py-1">
                        <AssetSparkline :chart-data="crypto.daily.chartData" :change-value="crypto.daily.priceChangePercentage24h" />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
