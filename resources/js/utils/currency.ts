/**
 * Format currency value with appropriate suffix
 * - M for Miliar (< 1000 Miliar)
 * - T for Triliun (>= 1000 Miliar)
 */
export function formatCurrency(value: number, decimals: number = 1): string {
    if (value >= 1000000000000) {
        // Triliun (>= 1000 Miliar)
        return `Rp ${(value / 1000000000000).toFixed(decimals)}T`;
    } else {
        // Miliar
        return `Rp ${(value / 1000000000).toFixed(decimals)}M`;
    }
}

/**
 * Format currency for chart axis (shorter format)
 */
export function formatCurrencyShort(value: number): string {
    if (value >= 1000000000000) {
        return `${(value / 1000000000000).toFixed(1)}T`;
    } else {
        return `${(value / 1000000000).toFixed(1)}M`;
    }
}

/**
 * Format currency with full detail (no abbreviation)
 * Example: Rp 1,234,567,890,123
 */
export function formatCurrencyFull(value: number): string {
    return `Rp ${value.toLocaleString('id-ID')}`;
}
