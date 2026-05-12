import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  {module}Service,
  type {Module},
  type Create{Module}Request,
  type Update{Module}Request,
} from '@/services/{module}Service';

// ============================================================================
// LIST HOOKS
// ============================================================================

/**
 * Get list of {module} with filters
 * From Delphi: FrmMain{Xxx}.GetData, filter clicks
 *
 * Usage:
 * const { data, isLoading, error } = use{Module}s({ bulan: 4, tahun: 2026, status: 'all' });
 */
export const use{Module}s = (params: {
  bulan?: number;
  tahun?: number;
  status?: 'all' | 'active' | 'inactive';
  tab_value?: 0 | 1;
}) => {
  return useQuery({
    queryKey: ['{module}s', params],
    queryFn: () => {module}Service.get{Module}s(params),
  });
};

/**
 * Refresh {module} data
 * From Delphi: FrmMain{Xxx}.ToolButton9Click (Refresh button)
 *
 * Usage:
 * const refreshMutation = useRefresh{Module}s();
 * await refreshMutation.mutateAsync();
 */
export const useRefresh{Module}s = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => {module}Service.refresh{Module}s(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['{module}s'] });
    },
  });
};

// ============================================================================
// SINGLE ITEM HOOKS
// ============================================================================

/**
 * Get {module} by ID
 * From Delphi: Frm{Xxx}.FormShow (load single record)
 *
 * Usage:
 * const { data, isLoading } = use{Module}(id);
 */
export const use{Module} = (id: string) => {
  return useQuery({
    queryKey: ['{module}', id],
    queryFn: () => {module}Service.get{Module}(id),
    enabled: !!id,  // Only run when id is provided
  });
};

// ============================================================================
// CRUD MUTATION HOOKS
// ============================================================================

/**
 * Create new {module}
 * From Delphi: Frm{Xxx}.BitBtn3Click (Save - new mode)
 *
 * Usage:
 * const createMutation = useCreate{Module}();
 * await createMutation.mutateAsync(data);
 */
export const useCreate{Module} = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: Create{Module}Request) =>
      {module}Service.create{Module}(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['{module}s'] });
    },
  });
};

/**
 * Update {module}
 * From Delphi: Frm{Xxx}.BitBtn3Click (Save - edit mode)
 *
 * Usage:
 * const updateMutation = useUpdate{Module}();
 * await updateMutation.mutateAsync({ id, data });
 */
export const useUpdate{Module} = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: Update{Module}Request }) =>
      {module}Service.update{Module}(id, data),
    onSuccess: (_, variables) => {
      // Invalidate both list and single item queries
      queryClient.invalidateQueries({ queryKey: ['{module}s'] });
      queryClient.invalidateQueries({ queryKey: ['{module}', variables.id] });
    },
  });
};

/**
 * Delete {module}
 * From Delphi: FrmMain{Xxx}.ToolButton3Click (Delete button)
 *
 * Usage:
 * const deleteMutation = useDelete{Module}();
 * await deleteMutation.mutateAsync(id);
 */
export const useDelete{Module} = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => {module}Service.delete{Module}(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['{module}s'] });
    },
  });
};

// ============================================================================
// SPECIAL ACTION HOOKS (Module-specific)
// ============================================================================

/**
 * Special action (e.g., realize, approve, cancel, etc.)
 * From Delphi: FrmRealisasi.BitBtn1Click
 *
 * Usage:
 * const realizeMutation = useRealize{Module}();
 * await realizeMutation.mutateAsync({ id, data: { ... } });
 */
export const useRealize{Module} = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: { field: string } }) =>
      {module}Service.realize{Module}(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['{module}s'] });
      queryClient.invalidateQueries({ queryKey: ['{module}', variables.id] });
    },
  });
};

/**
 * Cancel {module}
 *
 * Usage:
 * const cancelMutation = useCancel{Module}();
 * await cancelMutation.mutateAsync({ id, reason: '...' });
 */
export const useCancel{Module} = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, reason }: { id: string; reason?: string }) =>
      {module}Service.cancel{Module}(id, reason),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['{module}s'] });
      queryClient.invalidateQueries({ queryKey: ['{module}', variables.id] });
    },
  });
};

/**
 * Calculate/preview values
 * From Delphi: Frm{Xxx}.GetNilai, field change events
 *
 * Usage:
 * const calculateMutation = useCalculate{Module}();
 * const result = await calculateMutation.mutateAsync(data);
 */
export const useCalculate{Module} = () => {
  return useMutation({
    mutationFn: (data: { field1: number; field2: number }) =>
      {module}Service.calculate{Module}(data),
  });
};

/**
 * Export to Excel
 * From Delphi: FrmMain{Xxx}.ExportExcel1Click
 *
 * Usage:
 * const exportMutation = useExport{Module}s();
 * const blob = await exportMutation.mutateAsync({ bulan: 4, tahun: 2026 });
 */
export const useExport{Module}s = () => {
  return useMutation({
    mutationFn: (params: { bulan: number; tahun: number }) =>
      {module}Service.exportToExcel(params),
  });
};

/**
 * Get print data
 * From Delphi: FrmDialogCetak
 *
 * Usage:
 * const { data } = use{Module}Print(id);
 * // Then trigger print with data
 */
export const use{Module}Print = (id: string) => {
  return useQuery({
    queryKey: ['{module}-print', id],
    queryFn: () => {module}Service.getPrintData(id),
    enabled: false,  // Manual trigger only
  });
};
