import { useState, useEffect } from 'react';
import { use{Module}, useSave{Module} } from '@/hooks/use{Module}';
import { useAuthStore } from '@/stores/authStore';
import { PageHeader } from '@/components/Layout/Layout';

/**
 * Form data interface
 */
interface {Module}FormData {
  field1: string;
  field2: number;
  field3?: string;
}

/**
 * {Module} Page
 *
 * Display and manage {module} data.
 * Migrated from: Delphi Frm{Xxx}
 */
export function {Module}Page() {
  const { data: currentData, isLoading } = use{Module}();
  const saveMutation = useSave{Module}();
  const { user } = useAuthStore();

  const [formData, setFormData] = useState<{Module}FormData>({
    field1: '',
    field2: 0,
    field3: '',
  });

  const [errors, setErrors] = useState<Partial<Record<keyof {Module}FormData, string>>>({});
  const [isSaved, setIsSaved] = useState(false);

  // Load current data when available (replaces Delphi FormShow)
  useEffect(() => {
    if (currentData?.data) {
      setFormData({
        field1: currentData.data.field1 || '',
        field2: currentData.data.field2 || 0,
        field3: currentData.data.field3 || '',
      });
      setIsSaved(true);
    }
  }, [currentData]);

  // Validate form (replaces Delphi validation logic)
  const validateForm = (): boolean => {
    const newErrors: Partial<Record<keyof {Module}FormData, string>> = {};

    if (!formData.field1 || formData.field1.trim() === '') {
      newErrors.field1 = 'Field 1 wajib diisi';
    }

    if (formData.field2 < 1 || formData.field2 > 9999) {
      newErrors.field2 = 'Field 2 harus antara 1-9999';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // Handle form submit (replaces Delphi button click)
  const handleSubmit = async (e?: React.FormEvent) => {
    e?.preventDefault();

    if (!validateForm()) {
      return;
    }

    try {
      await saveMutation.mutateAsync(formData);
      setIsSaved(true);
    } catch (error) {
      console.error('Failed to save data:', error);
    }
  };

  // Handle Enter key (replaces Delphi FormKeyDown)
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleSubmit();
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-gray-500">Memuat data...</div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="{Module} Title"
        subtitle="Description of this page"
        breadcrumbs={[
          { label: 'Category' },
          { label: '{Module}' },
        ]}
      />

      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        {/* Current Data Display */}
        <div className="px-6 py-4 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-sm font-medium text-gray-500">Current Data</h3>
              <p className="text-2xl font-bold text-gray-900 mt-1">
                {currentData?.data ? 'Data loaded' : 'No data'}
              </p>
            </div>
            <div className="text-right">
              <p className="text-sm text-gray-500">User</p>
              <p className="font-medium text-gray-900">{user?.display_name || user?.name}</p>
            </div>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} onKeyDown={handleKeyDown} className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Field 1 */}
            <div>
              <label htmlFor="field1" className="block text-sm font-medium text-gray-700 mb-2">
                Field 1
              </label>
              <input
                type="text"
                id="field1"
                value={formData.field1}
                onChange={(e) => {
                  setFormData({ ...formData, field1: e.target.value });
                  setErrors({ ...errors, field1: undefined });
                  setIsSaved(false);
                }}
                className={`
                  w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2
                  ${errors.field1
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
                    : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                  }
                `}
              />
              {errors.field1 && (
                <p className="mt-1 text-sm text-red-600">{errors.field1}</p>
              )}
            </div>

            {/* Field 2 */}
            <div>
              <label htmlFor="field2" className="block text-sm font-medium text-gray-700 mb-2">
                Field 2
              </label>
              <input
                type="number"
                id="field2"
                value={formData.field2}
                onChange={(e) => {
                  const value = parseInt(e.target.value) || 0;
                  setFormData({ ...formData, field2: value });
                  setErrors({ ...errors, field2: undefined });
                  setIsSaved(false);
                }}
                min={1}
                max={9999}
                className={`
                  w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2
                  ${errors.field2
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
                    : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                  }
                `}
              />
              {errors.field2 && (
                <p className="mt-1 text-sm text-red-600">{errors.field2}</p>
              )}
            </div>
          </div>

          {/* Actions */}
          <div className="mt-6 flex items-center justify-end gap-3">
            <button
              type="submit"
              disabled={saveMutation.isPending}
              className={`
                px-4 py-2 rounded-lg font-medium text-white transition-colors
                ${saveMutation.isPending
                  ? 'bg-gray-400 cursor-not-allowed'
                  : 'bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500'
                }
              `}
            >
              {saveMutation.isPending ? 'Menyimpan...' : 'Simpan'}
            </button>

            {isSaved && !saveMutation.isPending && (
              <span className="text-green-600 text-sm flex items-center gap-1">
                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fillRule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clipRule="evenodd"
                  />
                </svg>
                Data berhasil disimpan
              </span>
            )}
          </div>
        </form>
      </div>
    </div>
  );
}
