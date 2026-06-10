import { computed } from 'vue'
import { useUserPreferencesStore } from '~/stores/userPreferences'

/**
 * Number formatter with hierarchical config resolution.
 *
 * Resolution order (first match wins):
 *   1. format.col.{kode}.{column}  - column-specific in this report
 *   2. format.col._default.{col}   - default for this column name (all reports)
 *   3. format.col._type.{type}      - default for column type (currency, percent, etc.)
 *   4. format.rep.{kode}            - default for this report
 *   5. format.rep._default          - user global default
 *   6. system hardcoded             - last-resort fallback
 *
 * Usage:
 *   const { format, formatColumn, source } = useNumberFormatter('020101')
 *   formatColumn('penerimaan', 1234567.89, 'currency') -> "Rp 1.234.567,89"
 */

const SYSTEM_DEFAULT = {
  decimal: 2,
  locale: 'id-ID',
  prefix: '',
  suffix: '',
  compact: false,
  showZero: true,
  style: 'normal',
}

const TYPE_DEFAULTS: Record<string, any> = {
  currency: { decimal: 2, locale: 'id-ID', style: 'currency', prefix: 'Rp ' },
  money_idr: { decimal: 0, locale: 'id-ID', style: 'currency', prefix: 'Rp ' },
  money: { decimal: 2, locale: 'id-ID', style: 'currency' },
  percent: { decimal: 1, locale: 'id-ID', suffix: '%', style: 'percent' },
  qty: { decimal: 0, locale: 'id-ID' },
  integer: { decimal: 0, locale: 'id-ID' },
  number: { decimal: 2, locale: 'id-ID' },
}

export function useNumberFormatter(kode?: string) {
  const store = useUserPreferencesStore()

  // Eagerly load format namespace
  if (import.meta.client) {
    store.loadNamespace('format')
  }

  /**
   * Resolve effective config for a column (with all fallback levels).
   */
  const resolveFormat = (
    column: string,
    columnType?: string
  ): { config: any, source: string } => {
    const fmt = store.data['format'] || {}

    // 1. Column-specific in this report
    const c1 = kode ? fmt[`col.${kode}.${column}`] : null
    if (c1) return { config: { ...SYSTEM_DEFAULT, ...c1 }, source: 'col_specific' }

    // 2. Default for this column name (all reports)
    const c2 = fmt[`col._default.${column}`]
    if (c2) return { config: { ...SYSTEM_DEFAULT, ...c2 }, source: 'col_default' }

    // 3. Default for column type
    if (columnType) {
      const c3 = fmt[`col._type.${columnType}`]
      if (c3) {
        return {
          config: { ...SYSTEM_DEFAULT, ...TYPE_DEFAULTS[columnType], ...c3 },
          source: 'col_type',
        }
      }
    }

    // 4. Default for this report
    const c4 = kode ? fmt[`rep.${kode}`] : null
    if (c4) return { config: { ...SYSTEM_DEFAULT, ...c4 }, source: 'rep_default' }

    // 5. User global default
    const c5 = fmt[`rep._default`]
    if (c5) return { config: { ...SYSTEM_DEFAULT, ...c5 }, source: 'global' }

    // 6. System default (smart by type)
    return {
      config: { ...SYSTEM_DEFAULT, ...(TYPE_DEFAULTS[columnType || ''] || {}) },
      source: 'system',
    }
  }

  /**
   * Format a value using the resolved config.
   */
  const formatWithConfig = (value: any, cfg: any): string => {
    if (value === null || value === undefined || value === '') {
      if (cfg.showZero === false) return '-'
      return formatRaw(0, cfg)
    }

    const num = Number(value)
    if (isNaN(num)) return String(value)

    return formatRaw(num, cfg)
  }

  const formatRaw = (num: number, cfg: any): string => {
    // Percent: treat input as ratio (0.125 -> 12.5%)
    if (cfg.style === 'percent') {
      return (num * 100).toFixed(cfg.decimal).replace('.', ',') + (cfg.suffix || '%')
    }

    // Compact mode
    if (cfg.compact) return formatCompact(num, cfg.locale)

    // Standard
    const formatted = new Intl.NumberFormat(cfg.locale, {
      minimumFractionDigits: cfg.decimal,
      maximumFractionDigits: cfg.decimal,
    }).format(num)

    return (cfg.prefix || '') + formatted + (cfg.suffix || '')
  }

  const formatCompact = (num: number, locale: string): string => {
    const abs = Math.abs(num)
    const sign = num < 0 ? '-' : ''
    const isId = !locale || locale.startsWith('id')

    if (abs >= 1_000_000_000) {
      return sign + (abs / 1_000_000_000).toFixed(1).replace('.', ',') + (isId ? ' M' : 'B')
    }
    if (abs >= 1_000_000) {
      return sign + (abs / 1_000_000).toFixed(1).replace('.', ',') + (isId ? ' jt' : 'M')
    }
    if (abs >= 1_000) {
      return sign + (abs / 1_000).toFixed(1).replace('.', ',') + (isId ? ' rb' : 'K')
    }
    return String(num)
  }

  /**
   * Format a value with the resolved config for a specific column.
   */
  const formatColumn = (column: string, value: any, columnType?: string): string => {
    const { config } = resolveFormat(column, columnType)
    return formatWithConfig(value, config)
  }

  /**
   * Format using just the report default (no column-specific lookup).
   */
  const format = (value: any): string => {
    const { config } = resolveFormat('__default')
    return formatWithConfig(value, config)
  }

  /**
   * Get the source of the current format (for UI badges).
   */
  const source = (column: string, columnType?: string): string => {
    return resolveFormat(column, columnType).source
  }

  /**
   * Get the resolved config for a column (useful for editing).
   */
  const getConfig = (column: string, columnType?: string): any => {
    return resolveFormat(column, columnType).config
  }

  return { format, formatColumn, source, getConfig, resolveFormat }
}
