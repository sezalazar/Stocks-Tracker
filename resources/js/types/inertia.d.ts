import type { StockOrCryptoItem } from '@/types/stock';

export interface SharedProps {
  auth: any;
  dashboardLists: {
    cryptoList: StockOrCryptoItem[];
    stocksList: StockOrCryptoItem[];
  };
  marketData: {
    fearAndGreed: any | null;
  };
}

declare module '@inertiajs/core' {
  interface PageProps extends SharedProps {}
}