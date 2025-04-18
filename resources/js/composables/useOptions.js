import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useOptions() {
  const page = usePage();

  const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    if (isNaN(date)) return dateStr;
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const yyyy = date.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
  };

  const groupedOptionsByExpiration = computed(() => {
    const groups = {};
    const options = page.props.options || [];
    options.forEach(option => {
      const expFormatted = formatDate(option.expiration);
      if (!groups[expFormatted]) {
        groups[expFormatted] = {
          expiration: option.expiration,
          expirationFormatted: expFormatted,
          rows: {},
        };
      }
      const strike = option.strike;
      if (!groups[expFormatted].rows[strike]) {
        groups[expFormatted].rows[strike] = { strike, call: null, put: null };
      }
      if (option.type === 'call') {
        groups[expFormatted].rows[strike].call = {
          buy_volume: option.buy_volume,
          buy_price: option.buy_price,
          sell_price: option.sell_price,
          sell_volume: option.sell_volume,
          last_price: option.last_price,
          variation_percent: option.variation_percent,
          nominal_volume: option.nominal_volume,
          previous_close: option.previous_close,
          delta: option.implied_volatility_delta,
        };
      }
      if (option.type === 'put') {
        groups[expFormatted].rows[strike].put = {
          buy_volume: option.buy_volume,
          buy_price: option.buy_price,
          sell_price: option.sell_price,
          sell_volume: option.sell_volume,
          last_price: option.last_price,
          variation_percent: option.variation_percent,
          nominal_volume: option.nominal_volume,
          previous_close: option.previous_close,
          delta: option.implied_volatility_delta,
        };
      }
    });

    return Object.values(groups).map(group => ({
      ...group,
      rows: Object.values(group.rows),
    }));
  });

  return { groupedOptionsByExpiration, formatDate };
}
