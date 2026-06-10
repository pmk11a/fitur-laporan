import { computed, ref, watch } from 'vue'
import { useUserPreferencesStore } from '~/stores/userPreferences'

/**
 * usePreference — generic, type-safe preference accessor.
 *
 * Usage:
 *   const { value, update, reset, loading } = usePreference<MyType>(
 *     'format',
 *     'rep._default',
 *     { decimal: 2, locale: 'id-ID' }
 *   )
 *
 *   // value is a ref<MyType>
 *   // update(newValue) persists to server
 *   // reset() deletes from server (falls back to defaultValue)
 */
export function usePreference<T = any>(
  namespace: string,
  key: string,
  defaultValue: T
) {
  const store = useUserPreferencesStore()

  // Eagerly load the namespace (idempotent, safe in setup)
  if (import.meta.client) {
    store.loadNamespace(namespace)
  }

  const value = computed<T>({
    get: () => {
      // Read from store; fall back to defaultValue
      const v = store.data[namespace]?.[key]
      return v === undefined ? defaultValue : (v as T)
    },
    set: (v) => {
      store.set(namespace, key, v)
    },
  })

  const update = (newValue: T) => store.set(namespace, key, newValue)
  const reset = () => store.remove(namespace, key)
  const loading = computed(() => !store.loaded[namespace])

  return { value, update, reset, loading }
}

/**
 * usePreferenceNamespace — access all keys in a namespace.
 *
 * Usage:
 *   const { values, set, remove } = usePreferenceNamespace('format')
 *   set('rep._default', { decimal: 2, ... })
 *   values.value // { 'rep._default': {...}, 'col.020101.penerimaan': {...} }
 */
export function usePreferenceNamespace(namespace: string) {
  const store = useUserPreferencesStore()

  if (import.meta.client) {
    store.loadNamespace(namespace)
  }

  const values = computed<Record<string, any>>(() => store.data[namespace] || {})

  const set = (key: string, value: any) => store.set(namespace, key, value)
  const remove = (key: string) => store.remove(namespace, key)
  const get = (key: string, defaultValue: any = null) =>
    values.value[key] ?? defaultValue

  return { values, set, remove, get }
}
