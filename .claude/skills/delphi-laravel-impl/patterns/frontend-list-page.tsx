import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { use{Module}s, useDelete{Module}, useRefresh{Module}s } from '@/hooks/use{Module}';
import { PageHeader } from '@/components/Layout/Layout';
import { DataGrid } from '@/components/DataGrid/DataGrid';
import type { Column } from '@/components/DataGrid/DataGrid';
import type { {Module} } from '@/services/{module}Service';

/**
 * {Module} List Page
 *
 * Generated from: Delphi FrmMain{Xxx}
 * Main grid with master-detail expandable rows
 *
 * Replaces Delphi features:
 * - GetData → Load data with filters
 * - ToolButton1Click → Add button
 * - ToolButton2Click → Edit button (double-click)
 * - ToolButton3Click → Delete button
 * - ToolButton9Click → Refresh button
 * - cxGrid master-detail → expandable rows
 */
export function {Module}Page() {
  const navigate = useNavigate();

  // Tab state (if applicable)
  const [activeTab, setActiveTab] = useState<'tab1' | 'tab2'>('tab1');

  // Filters state
  const [filters, setFilters] = useState({
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
    status: 'all' as 'all' | 'active' | 'inactive',
  });

  // Expanded rows for master-detail
  const [expandedRows, setExpandedRows] = useState<Set<string>>(new Set());

  // Data fetching
  const { data, isLoading, error, refetch } = use{Module}s({
    ...filters,
    tab_value: activeTab === 'tab1' ? 0 : 1,
  });

  const deleteMutation = useDelete{Module}();
  const refreshMutation = useRefresh{Module}s();

  // Define columns based on Delphi cxGrid
  const columns: Column<{Module}>[] = [
    {
      key: 'id',
      header: 'ID',
      width: '80px',
      sortable: true,
    },
    {
      key: 'field1',
      header: 'Column 1',
      width: '150px',
      sortable: true,
    },
    {
      key: 'field2',
      header: 'Column 2',
      width: '120px',
      sortable: true,
      render: (_, row) => row.field2 || '-',
    },
    {
      key: 'related_field',
      header: 'Related Data',
      width: '180px',
      sortable: false,
      render: (_, row) => row.related?.name || '-',
    },
    {
      key: 'amount',
      header: 'Amount',
      width: '120px',
      sortable: true,
      render: (_, row) => {
        const amount = row.amount || 0;
        return new Intl.NumberFormat('id-ID').format(amount);
      },
    },
  ];

  // Toggle row expansion for detail view
  const toggleExpand = (id: string) => {
    setExpandedRows(prev => {
      const newSet = new Set(prev);
      if (newSet.has(id)) {
        newSet.delete(id);
      } else {
        newSet.add(id);
      }
      return newSet;
    });
  };

  // Handle add button
  const handleAdd = () => {
    navigate('/{module-path}/create');
  };

  // Handle refresh
  const handleRefresh = async () => {
    await refreshMutation.mutateAsync();
  };

  // Handle row double click (edit)
  const handleRowDoubleClick = (row: {Module}) => {
    navigate(`/{module-path}/edit/${row.id}`);
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="{Module} Title"
        subtitle="Description of this page"
        breadcrumbs={[
          { label: 'Category', path: '/category' },
          { label: '{Module}' },
        ]}
      />

      {/* Tabs - Optional */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        <div className="flex border-b border-gray-200">
          <button
            onClick={() => setActiveTab('tab1')}
            className={`px-6 py-3 font-medium text-sm transition-colors ${
              activeTab === 'tab1'
                ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
            }`}
          >
            Tab 1 Label
          </button>
          <button
            onClick={() => setActiveTab('tab2')}
            className={`px-6 py-3 font-medium text-sm transition-colors ${
              activeTab === 'tab2'
                ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
            }`}
          >
            Tab 2 Label
          </button>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div className="flex flex-wrap items-center gap-4">
          <div className="flex items-center gap-2">
            <label className="text-sm font-medium text-gray-700">Bulan:</label>
            <select
              value={filters.bulan}
              onChange={(e) => setFilters({ ...filters, bulan: parseInt(e.target.value) })}
              className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value={1}>Januari</option>
              <option value={2}>Februari</option>
              {/* ... */}
            </select>
          </div>

          <div className="flex items-center gap-2">
            <label className="text-sm font-medium text-gray-700">Tahun:</label>
            <input
              type="number"
              value={filters.tahun}
              onChange={(e) => setFilters({ ...filters, tahun: parseInt(e.target.value) || new Date().getFullYear() })}
              className="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div className="flex-1"></div>

          <div className="text-sm text-gray-500">
            Total: {data?.data?.length || 0} data
          </div>
        </div>
      </div>

      {/* Data Grid with Master-Detail */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        <DataGrid
          data={data?.data || []}
          columns={columns}
          loading={isLoading}
          error={error as Error | null}
          rowKey="id"
          onRowDoubleClick={handleRowDoubleClick}
          expandedRowIds={Array.from(expandedRows)}
          onToggleExpand={toggleExpand}
          renderDetailRow={(row: {Module}) => (
            <div>
              <div className="text-sm font-semibold text-blue-600 mb-3 pb-2 border-b border-blue-200">
                Detail {row.field1}
              </div>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2 text-sm">
                <div>
                  <span className="text-gray-500">Field A:</span>
                  <span className="ml-2 font-medium text-gray-900">{row.detail_a || '-'}</span>
                </div>
                <div>
                  <span className="text-gray-500">Field B:</span>
                  <span className="ml-2 font-medium text-gray-900">{row.detail_b || '-'}</span>
                </div>
                {/* ... more detail fields */}
              </div>
            </div>
          )}
          emptyMessage="Tidak ada data"
          compact
          striped
        />
      </div>

      {/* Toolbar */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div className="flex items-center gap-2">
          <button
            onClick={handleAdd}
            className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Tambah
          </button>

          <div className="w-px h-6 bg-gray-300"></div>

          <button
            onClick={handleRefresh}
            disabled={refreshMutation.isPending}
            className="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            <svg className={`w-4 h-4 ${refreshMutation.isPending ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
        </div>
      </div>
    </div>
  );
}
