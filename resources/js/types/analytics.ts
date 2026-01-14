export interface AnalyticsOverview {
  total_sold: number;
  total_revenue: number;
  total_platform_fee: number;
}

export interface TicketSalesRank {
  ticket_name: string;
  total_sold: number;
  total_revenue: number;
}

export interface DaySalesData {
  date: string;
  total_sold: number;
  total_revenue: number;
}

export interface AnalyticsData {
  overview: AnalyticsOverview;
  ranking: TicketSalesRank[];
  chart: DaySalesData[];
  last_updated: string;
}

export interface EventOption {
  id: string;
  name: string;
}
