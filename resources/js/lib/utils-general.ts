/**
 * Format number as Indonesian Rupiah currency
 * @param value - The numeric value to format
 * @returns Formatted currency string (e.g., "Rp 14.600.000")
 */
export function formatCurrency(value: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value);
}

/**
 * Format number with Indonesian locale
 * @param value - The numeric value to format
 * @returns Formatted number string (e.g., "1.234.567")
 */
export function formatNumber(value: number): string {
  return new Intl.NumberFormat('id-ID').format(value);
}
