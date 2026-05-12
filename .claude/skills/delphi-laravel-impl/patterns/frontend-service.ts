import api from '@/utils/api';

/**
 * Data interface for {Module}
 */
export interface {Module}Data {
  id?: string | number;
  field1: string;
  field2: number;
  field3?: string;
  field4?: number;
}

/**
 * API Response interface
 */
export interface {Module}Response {
  data: {Module}Data | null;
  message?: string;
}

/**
 * List Response interface
 */
export interface {Module}ListResponse {
  data: {Module}Data[];
  total?: number;
}

/**
 * {Module} Service
 *
 * Handle API calls for {module} module.
 * Migrated from: Delphi Frm{Xxx}
 */
class {Module}Service {
  /**
   * Get {module} data for authenticated user
   */
  async getData(): Promise<{Module}Response> {
    const response = await api.get('/api/v1/{module}');
    return response.data;
  }

  /**
   * Get list of {module} data
   */
  async getList(params?: {
    page?: number;
    search?: string;
  }): Promise<{Module}ListResponse> {
    const response = await api.get('/api/v1/{module}', { params });
    return response.data;
  }

  /**
   * Get {module} data by ID
   */
  async getById(id: string | number): Promise<{Module}Response> {
    const response = await api.get(`/api/v1/{module}/${id}`);
    return response.data;
  }

  /**
   * Save {module} data (create or update)
   */
  async save(data: {Module}Data): Promise<{Module}Response> {
    const response = await api.post('/api/v1/{module}', data);
    return response.data;
  }

  /**
   * Update {module} data
   */
  async update(id: string | number, data: Partial<{Module}Data>): Promise<{Module}Response> {
    const response = await api.put(`/api/v1/{module}/${id}`, data);
    return response.data;
  }

  /**
   * Delete {module} data
   */
  async delete(id: string | number): Promise<{Response}> {
    const response = await api.delete(`/api/v1/{module}/${id}`);
    return response.data;
  }

  /**
   * Check if data exists
   */
  async check(): Promise<{ exists: boolean }> {
    const response = await api.post('/api/v1/{module}/check');
    return response.data;
  }
}

export const {module}Service = new {Module}Service();
