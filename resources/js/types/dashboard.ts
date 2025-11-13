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
    witel_name: string | null;
    am_name?: string;
    proporsi?: number;
    pembagian?: string;
    is_primary: boolean;
    // Legacy fields
    witel_code?: string | null;
}

export interface CompanyData {
    nip_nas: string; // Primary Key - changed from id
    nama_perusahaan: string;
    subsegment: string;
    revenue: number; // Changed from total_revenue to match API response
    formatted_revenue: string; // Changed from formatted_total_revenue
    source_data?: string;
    regions?: CompanyRegion[];
    payment_count?: number;
    avg_revenue?: number;
    formatted_avg_revenue?: string;
    // Legacy fields for backward compatibility
    id?: number | string;
    total_revenue?: number;
    formatted_total_revenue?: string;
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