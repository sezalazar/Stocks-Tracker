export interface StockItem {
  date: string;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
}

export interface StockData {
  data?: StockItem[];
}

export interface RsiItem {
  symbol: string;
  timespan: string;
  data_timestamp: string;
  value: number;
}

export interface MacdItem {
  symbol: string;
  timespan: string;
  data_timestamp: string;
  value: number;
  signal: number;
  histogram: number;
}

export interface CompanyResults {
  ticker: string;
  name: string;
  description: string;
  market_cap: number;
  homepage_url?: string;
  logo_url?: string;
  icon_url?: string;
}

export interface CompanyData {
  request_id?: string;
  status?: string;
  results?: CompanyResults;
}

export interface FinancialStatementItem {
  date: string;
  revenue: number | null;
  gross_profit: number | null;
  gross_profit_ratio: number | null;
  net_income: number | null;
  eps: number | null;
  cost_of_revenue: number;
  research_and_development_expenses: number;
  general_and_administrative_expenses: number;
}

export interface FinancialRecord {
  date: string;
  revenue: number;
  netIncome: number;
  eps: number;
  grossProfit: number;
  grossProfitRatio: number;
  costOfRevenue: number;
  researchAndDevelopmentExpenses: number;
  generalAndAdministrativeExpenses: number;
}

export interface StockOrCryptoItem {
  symbol: string;
  rsi: number | null;
  macd: number | null;
  price: number | null;
  changePercent: number | null;
}
