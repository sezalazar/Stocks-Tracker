<template>
  <div class="w-full mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Load Options Data</h1>
    <form @submit.prevent="submitForm" class="mb-8">
      <div class="mb-4">
        <label for="market_data" class="block text-sm font-medium text-gray-700">
          Paste Options Data Block here
        </label>
        <textarea
          id="market_data"
          v-model="form.market_data"
          rows="10"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Paste Options Data Block…"
        ></textarea>
      </div>
      <button
        type="submit"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
        :disabled="form.processing"
      >
        {{ form.processing ? 'Processing...' : 'Send' }}
      </button>
    </form>

    <div v-if="page.props.underlying" class="mt-8">
      <h2 class="text-xl font-bold mb-4">Results</h2>
      
      <div class="mb-6 p-4 border rounded">
        <h3 class="font-bold text-lg mb-2">Underlying</h3>
        <p><strong>Ticker:</strong> {{ page.props.underlying.symbol }}</p>
        <p><strong>Compra:</strong> {{ page.props.underlying.buy_price }}</p>
        <p><strong>Venta:</strong> {{ page.props.underlying.sell_price }}</p>
        <p><strong>Último Precio:</strong> {{ page.props.underlying.last_price }}</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-xs">
          <thead>
            <tr>
              <th rowspan="2" class="border p-1 text-center">Vencimiento</th>
              <th colspan="9" class="border p-1 text-center">Call</th>
              <th rowspan="2" class="border p-1 text-center font-bold bg-gray-200">Strike</th>
              <th colspan="9" class="border p-1 text-center">Put</th>
            </tr>
            <tr>
              <th class="border p-1 text-center">Cant C.</th>
              <th class="border p-1 text-center">$ Compra</th>
              <th class="border p-1 text-center">$ Venta</th>
              <th class="border p-1 text-center">Cant V.</th>
              <th class="border p-1 text-center">Ult</th>
              <th class="border p-1 text-center">Var.%</th>
              <th class="border p-1 text-center">Vol</th>
              <th class="border p-1 text-center">Cierre</th>
              <th class="border p-1 text-center">Delta</th>
              <th class="border p-1 text-center">Cant C.</th>
              <th class="border p-1 text-center">$ Compra</th>
              <th class="border p-1 text-center">$ Venta</th>
              <th class="border p-1 text-center">Cant V.</th>
              <th class="border p-1 text-center">Ult</th>
              <th class="border p-1 text-center">Var.%</th>
              <th class="border p-1 text-center">Vol</th>
              <th class="border p-1 text-center">Cierre</th>
              <th class="border p-1 text-center">Delta</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="group in groupedOptionsByExpiration" :key="group.expirationFormatted">
              <template v-for="(row, rowIndex) in group.rows" :key="row.strike">
                <tr>
                  <td class="border p-1 text-center" v-if="rowIndex === 0" :rowspan="group.rows.length">
                    {{ group.expirationFormatted }}
                  </td>
                  <td class="border p-1 text-center">{{ row.call?.buy_volume ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.call?.buy_price ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.call?.sell_price ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.call?.sell_volume ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.call?.last_price ?? '-' }}</td>
                  <td class="border p-1 text-center"
                      :class="{'text-green-600': row.call?.variation_percent > 0, 'text-red-600': row.call?.variation_percent < 0}">
                    {{ row.call?.variation_percent ?? '-' }}
                  </td>
                  <td class="border p-1 text-center">{{ row.call?.nominal_volume ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.call?.previous_close ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.call?.delta ?? '-' }}</td>
                  <td class="border p-1 text-center font-bold bg-gray-200">{{ row.strike }}</td>
                  <td class="border p-1 text-center">{{ row.put?.buy_volume ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.put?.buy_price ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.put?.sell_price ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.put?.sell_volume ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.put?.last_price ?? '-' }}</td>
                  <td class="border p-1 text-center"
                      :class="{'text-green-600': row.put?.variation_percent > 0, 'text-red-600': row.put?.variation_percent < 0}">
                    {{ row.put?.variation_percent ?? '-' }}
                  </td>
                  <td class="border p-1 text-center">{{ row.put?.nominal_volume ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.put?.previous_close ?? '-' }}</td>
                  <td class="border p-1 text-center">{{ row.put?.delta ?? '-' }}</td>
                </tr>
              </template>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Strategies -->
      <div class="mt-8 overflow-x-auto">
        <h3 class="text-lg font-bold mb-2">Strategies</h3>
        <table class="w-full border-collapse text-xs">
          <thead>
            <tr>
              <th class="border p-1 text-center">Strategy</th>
              <th class="border p-1 text-center">Strike</th>
              <th class="border p-1 text-center">$ Costo</th>
              <th class="border p-1 text-center">Breakeven Up</th>
              <th class="border p-1 text-center">Breakeven Down</th>
              <th class="border p-1 text-center">Dist. Up (%)</th>
              <th class="border p-1 text-center">Dist. Down (%)</th>
              <th class="border p-1 text-center">Allocation ($)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(strategy, index) in page.props.strategies" :key="index">
              <td class="border p-1 text-center">{{ strategy.strategy }}</td>
              <td class="border p-1 text-center">{{ strategy.strike }}</td>
              <td class="border p-1 text-center">{{ formatCurrency(strategy.cost) }}</td>
              <td class="border p-1 text-center">{{ formatCurrency(strategy.breakeven_up) }}</td>
              <td class="border p-1 text-center">{{ formatCurrency(strategy.breakeven_down) }}</td>
              <td class="border p-1 text-center">{{ strategy.distance_up_pct.toFixed(2) }}%</td>
              <td class="border p-1 text-center">{{ strategy.distance_down_pct.toFixed(2) }}%</td>
              <td class="border p-1 text-center">{{ formatCurrency(strategy.allocation) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Import Schadcn-Vue components
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

// Import composable
import { useOptions } from '@/composables/useOptions';

const page = usePage();

const form = useForm({
  market_data: '',
});

const { groupedOptionsByExpiration } = useOptions();

const formatCurrency = (value) => {
  if (value == null) return '-';
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const submitForm = () => {
  form.post(route('options.process'), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {},
    onError: (errors) => {
      console.error("Errores:", errors);
    },
  });
};
</script>
