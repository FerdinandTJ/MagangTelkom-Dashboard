export interface SubsegmentData {
    subsegment: string;
    total_revenue: number;
    total_companies: number;
    formatted_total_revenue: string;
    avg_revenue?: number;
    formatted_avg_revenue?: string;
    [key: string]: any; 
}

export interface MonthData {
    bulan: number;
    bulan_name: string;
    total_revenue: number;
    total_companies: number;
    formatted_revenue: string;
}

export interface CompanyRegion {
    region_code: string;
    region_name: string;
    witel_code: string | null;
    witel_name: string | null;
    is_primary: boolean;
}

export interface CompanyData {
    id: number;
    nip_nas: string;
    nama_perusahaan: string;
    subsegment: string;
    total_revenue: number;
    formatted_total_revenue: string;
    status: string;
    source_data?: string;
    regions?: CompanyRegion[];
}

export interface MonthlyRevenue {
    bulan: number;
    bulan_name: string;
    tahun: number;
    revenue: number;
    formatted_revenue: string;
    period_label?: string;
}

export interface YearlyRevenue {
    tahun: number;
    total_revenue: number;
    formatted_total_revenue: string;
    months_count: number;
}