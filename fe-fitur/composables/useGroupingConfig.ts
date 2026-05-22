/**
 * Grouping Configuration Composable
 * Provides config-based grouping helpers from database via backend
 */

export function useGroupingConfig() {
  // Default: no running balance calculation, use subgroups, show group headers
  // These can be extended to read from grouping config if needed

  function shouldCalculateRunningBalance(): boolean {
    return false
  }

  function getBalanceColumn(): string | null {
    return null
  }

  function getStartRowMarker(): string | null {
    return null
  }

  function getMarkerColumn(): string | null {
    return null
  }

  function useSubgroups(): boolean {
    return true
  }

  function showGroupHeaders(): boolean {
    return true
  }

  function calculateRunningBalance(
    allItems: any[],
    itemIndex: number,
    balanceCol: string,
    startMarker: string,
    markerCol: string
  ): any {
    let balance = 0
    for (let i = 0; i <= itemIndex; i++) {
      const val = parseFloat(allItems[i][balanceCol]) || 0
      balance += val
    }
    return balance
  }

  return {
    shouldCalculateRunningBalance,
    getBalanceColumn,
    getStartRowMarker,
    getMarkerColumn,
    useSubgroups,
    showGroupHeaders,
    calculateRunningBalance,
  }
}