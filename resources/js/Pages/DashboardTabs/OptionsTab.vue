<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { useOptions } from '@/composables/useOptions'

const page = usePage()

const form = useForm({
    market_data: '',
})

const examples = ref([
    { name: 'ALUA', path: '/options_examples/alua.txt' },
    { name: 'BBAR', path: '/options_examples/bbar.txt' },
    { name: 'BYMA', path: '/options_examples/byma.txt' },
    { name: 'COME', path: '/options_examples/come.txt' },
    { name: 'GGAL', path: '/options_examples/ggal.txt' },
    { name: 'PAMP', path: '/options_examples/pamp.txt' },
    { name: 'TGSU2', path: '/options_examples/tgsu2.txt' },
    { name: 'YPFD', path: '/options_examples/ypfd.txt' },
])

const isLoadingExample = ref(false)

async function loadExample(path) {
    if (!path) return
    isLoadingExample.value = true
    form.market_data = 'Loading example...'

    try {
        const response = await fetch(path)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }
        const textContent = await response.text()
        form.market_data = textContent
    } catch (error) {
        console.error('Could not fetch the example file:', error)
        form.market_data = `Error: Could not load example from ${path}. Check console.`
    } finally {
        isLoadingExample.value = false
    }
}

const { groupedOptionsByExpiration } = useOptions()

const underlying = computed(() => page.props.underlying)
const strategies = computed(() => page.props.strategies || [])

const getChangeColor = (value) => {
    if (value > 0) return 'text-green-600'
    if (value < 0) return 'text-red-600'
    return 'text-foreground'
}

const formatCurrency = (value) => {
    if (value == null) return '-'
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)
}

const submitForm = () => {
    form.post(route('options.process'), {
        preserveState: true,
        preserveScroll: true,
        only: ['underlying', 'options', 'strategies'],
        onSuccess: () => {},
        onError: (errors) => {
            console.error('Errores:', errors)
        },
    })
}
</script>

<template>
    <div class="w-full mx-auto p-4 sm:p-6 lg:p-8">
        <h1 class="text-2xl font-bold mb-4 text-foreground">Load Options Data</h1>
        <form @submit.prevent="submitForm" class="mb-8 p-6 bg-card border border-border rounded-lg">
            <div class="mb-4">
                <label class="block text-sm font-medium text-muted-foreground mb-2">Load an example:</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="example in examples"
                        :key="example.name"
                        type="button"
                        @click="loadExample(example.path)"
                        :disabled="isLoadingExample"
                        class="px-3 py-1 text-xs font-medium bg-secondary text-secondary-foreground rounded-full hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ example.name }}
                    </button>
                </div>
            </div>
            <div class="mb-6">
                <label for="market_data" class="block text-sm font-medium text-muted-foreground">Paste Options Data Block here</label>
                <textarea
                    id="market_data"
                    v-model="form.market_data"
                    rows="10"
                    class="mt-1 block w-full bg-input border-border rounded-md shadow-sm focus:ring-ring focus:border-primary placeholder:text-muted-foreground/50"
                    placeholder="Paste Options Data Block or click an example above…"
                ></textarea>
            </div>
            <button
                type="submit"
                class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-[hsl(var(--primary-dark))] disabled:opacity-50 transition-colors"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Processing...' : 'Process' }}
            </button>
        </form>

        <div v-if="underlying" class="mt-8 space-y-8">
            <div class="bg-card border border-border rounded-lg p-6">
                <h3 class="font-bold text-lg mb-3 text-primary">Underlying</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-muted-foreground">Ticker:</span>
                        <strong class="text-foreground">{{ underlying.symbol }}</strong>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Compra:</span>
                        <strong class="text-foreground">{{ underlying.buy_price }}</strong>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Venta:</span>
                        <strong class="text-foreground">{{ underlying.sell_price }}</strong>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Último:</span>
                        <strong class="text-foreground">{{ underlying.last_price }}</strong>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <h3 class="text-xl font-bold mb-4 text-foreground">Options Chain</h3>
                <Table class="text-xs">
                    <TableHeader>
                        <TableRow class="border-border hover:bg-transparent">
                            <TableHead rowspan="2" class="align-middle text-center p-1 text-muted-foreground uppercase tracking-wider">
                                Vencimiento
                            </TableHead>
                            <TableHead colspan="9" class="text-center text-muted-foreground uppercase tracking-wider p-2">Calls</TableHead>
                            <TableHead
                                rowspan="2"
                                class="align-middle text-center font-bold bg-muted text-primary uppercase tracking-wider p-1"
                            >
                                Strike
                            </TableHead>
                            <TableHead colspan="9" class="text-center text-muted-foreground uppercase tracking-wider p-2">Puts</TableHead>
                        </TableRow>
                        <TableRow class="border-border hover:bg-transparent text-[11px]">
                            <TableHead class="text-center p-1">Cant C.</TableHead>
                            <TableHead class="text-center p-1">$ Compra</TableHead>
                            <TableHead class="text-center p-1">$ Venta</TableHead>
                            <TableHead class="text-center p-1">Cant V.</TableHead>
                            <TableHead class="text-center p-1">Ult</TableHead>
                            <TableHead class="text-center p-1">Var.%</TableHead>
                            <TableHead class="text-center p-1">Vol</TableHead>
                            <TableHead class="text-center p-1">Cierre</TableHead>
                            <TableHead class="text-center p-1">Delta</TableHead>
                            <TableHead class="text-center p-1">Cant C.</TableHead>
                            <TableHead class="text-center p-1">$ Compra</TableHead>
                            <TableHead class="text-center p-1">$ Venta</TableHead>
                            <TableHead class="text-center p-1">Cant V.</TableHead>
                            <TableHead class="text-center p-1">Ult</TableHead>
                            <TableHead class="text-center p-1">Var.%</TableHead>
                            <TableHead class="text-center p-1">Vol</TableHead>
                            <TableHead class="text-center p-1">Cierre</TableHead>
                            <TableHead class="text-center p-1">Delta</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="group in groupedOptionsByExpiration" :key="group.expirationFormatted">
                            <TableRow
                                v-for="(row, rowIndex) in group.rows"
                                :key="row.strike"
                                class="border-border transition-colors hover:bg-muted/50"
                            >
                                <TableCell
                                    v-if="rowIndex === 0"
                                    :rowspan="group.rows.length"
                                    class="text-center align-middle font-medium text-foreground p-1"
                                >
                                    {{ group.expirationFormatted }}
                                </TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.buy_volume ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.buy_price ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.sell_price ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.sell_volume ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.last_price ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1" :class="getChangeColor(row.call?.variation_percent)">
                                    {{ row.call?.variation_percent ?? '-' }}
                                </TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.nominal_volume ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.previous_close ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.call?.delta ?? '-' }}</TableCell>
                                <TableCell class="text-center font-bold bg-muted text-primary p-1">{{ row.strike }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.buy_volume ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.buy_price ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.sell_price ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.sell_volume ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.last_price ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1" :class="getChangeColor(row.put?.variation_percent)">
                                    {{ row.put?.variation_percent ?? '-' }}
                                </TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.nominal_volume ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.previous_close ?? '-' }}</TableCell>
                                <TableCell class="text-center p-1">{{ row.put?.delta ?? '-' }}</TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <div class="overflow-x-auto">
                <h3 class="text-xl font-bold mb-4 text-foreground">Strategies</h3>
                <Table class="text-xs">
                    <TableHeader>
                        <TableRow class="border-border hover:bg-transparent">
                            <TableHead class="text-left p-2 text-muted-foreground uppercase tracking-wider">Strategy</TableHead>
                            <TableHead class="text-center p-2 text-muted-foreground uppercase tracking-wider">Strike</TableHead>
                            <TableHead class="text-center p-2 text-muted-foreground uppercase tracking-wider">$ Costo</TableHead>
                            <TableHead class="text-center p-2 text-muted-foreground uppercase tracking-wider">Breakeven Up</TableHead>
                            <TableHead class="text-center p-2 text-muted-foreground uppercase tracking-wider">Breakeven Down</TableHead>
                            <TableHead class="text-center p-2 text-muted-foreground uppercase tracking-wider">Dist. Up (%)</TableHead>
                            <TableHead class="text-center p-2 text-muted-foreground uppercase tracking-wider">Dist. Down (%)</TableHead>
                            <TableHead class="text-center p-2 text-muted-foreground uppercase tracking-wider">Allocation ($)</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="(strategy, index) in strategies"
                            :key="index"
                            class="border-border transition-colors hover:bg-muted/50"
                        >
                            <TableCell class="font-medium text-foreground p-2">{{ strategy.strategy }}</TableCell>
                            <TableCell class="text-center text-primary p-2">{{ strategy.strike }}</TableCell>
                            <TableCell class="text-center p-2">{{ formatCurrency(strategy.cost) }}</TableCell>
                            <TableCell class="text-center p-2">{{ formatCurrency(strategy.breakeven_up) }}</TableCell>
                            <TableCell class="text-center p-2">{{ formatCurrency(strategy.breakeven_down) }}</TableCell>
                            <TableCell class="text-center p-2">{{ strategy.distance_up_pct.toFixed(2) }}%</TableCell>
                            <TableCell class="text-center p-2">{{ strategy.distance_down_pct.toFixed(2) }}%</TableCell>
                            <TableCell class="text-center p-2">{{ formatCurrency(strategy.allocation) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
