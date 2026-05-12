/**
 * API Configuration
 * Can be switched between Keu-app (port 8000) and be-fitur (port 8080)
 */

export interface ApiConfig {
  baseUrl: string
  mode: 'keu' | 'fitur'
}

export const API_CONFIGS: Record<string, ApiConfig> = {
  keu: {
    baseUrl: 'http://localhost:8000/api',
    mode: 'keu'
  },
  fitur: {
    baseUrl: 'http://localhost:8080/api',
    mode: 'fitur'
  }
}

// Current active API
export const useActiveApi = () => {
  const runtimeConfig = useRuntimeConfig()

  // Try runtime config first, fallback to 'keu'
  const apiBase = runtimeConfig.public.apiBase as string || API_CONFIGS.keu.baseUrl

  const isKeu = apiBase.includes('8000')
  const isFitur = apiBase.includes('8080')

  return {
    baseUrl: apiBase,
    mode: isFitur ? 'fitur' : 'keu' as 'keu' | 'fitur',
    isKeu,
    isFitur
  }
}

// Keu-app API response types
export interface KeuLoginResponse {
  success: boolean
  message: string
  data: {
    user: {
      USERID: string
      UID: string
      FullName: string
      TINGKAT: number
      STATUS: number
      kodeBag: string
      KodeJab: string
      KodeKasir: string
      Kodegdg: string
    }
    access_token: string
    permissions?: string[]
  }
}

// be-fitur API response types
export interface FiturLoginResponse {
  user: {
    id: string
    username: string
    name: string
    TINGKAT: number
    STATUS: number
    access: number
    level: number
    role: string
  }
  token: string
}

// Unified user type
export interface UnifiedUser {
  id: string
  userId: string
  username: string
  name: string
  tingkat: number
  status: number
  kodeBag: string | null
  KodeKasir: string | null
  Kodegdg: string | null
  isAdmin: boolean
}

// Map Keu user to unified format
export function mapKeuUser(data: KeuLoginResponse['data']): UnifiedUser {
  return {
    id: data.user.USERID,
    userId: data.user.USERID,
    username: data.user.UID || data.user.USERID,
    name: data.user.FullName,
    tingkat: data.user.TINGKAT,
    status: data.user.STATUS,
    kodeBag: data.user.kodeBag || null,
    KodeKasir: data.user.KodeKasir || null,
    Kodegdg: data.user.Kodegdg || null,
    isAdmin: data.user.TINGKAT >= 99 || data.user.STATUS >= 255
  }
}