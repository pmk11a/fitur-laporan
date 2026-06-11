
import type { DefineComponent, SlotsType } from 'vue'
type IslandComponent<T> = DefineComponent<{}, {refresh: () => Promise<void>}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, SlotsType<{ fallback: { error: unknown } }>> & T

type HydrationStrategies = {
  hydrateOnVisible?: IntersectionObserverInit | true
  hydrateOnIdle?: number | true
  hydrateOnInteraction?: keyof HTMLElementEventMap | Array<keyof HTMLElementEventMap> | true
  hydrateOnMediaQuery?: string
  hydrateAfter?: number
  hydrateWhen?: boolean
  hydrateNever?: true
}
type LazyComponent<T> = DefineComponent<HydrationStrategies, {}, {}, {}, {}, {}, {}, { hydrated: () => void }> & T

interface _GlobalComponents {
  BrowseAutocomplete: typeof import("../../components/BrowseAutocomplete.vue")['default']
  GroupedTable: typeof import("../../components/GroupedTable.vue")['default']
  MenuItem: typeof import("../../components/MenuItem.vue")['default']
  PerkiraanAutocomplete: typeof import("../../components/PerkiraanAutocomplete.vue")['default']
  ReportTable: typeof import("../../components/ReportTable.vue")['default']
  Sidebar: typeof import("../../components/Sidebar.vue")['default']
  TopBar: typeof import("../../components/TopBar.vue")['default']
  AdminTabsColumnsTab: typeof import("../../components/admin/tabs/ColumnsTab.vue")['default']
  AdminTabsDatasetsTab: typeof import("../../components/admin/tabs/DatasetsTab.vue")['default']
  AdminTabsFiltersTab: typeof import("../../components/admin/tabs/FiltersTab.vue")['default']
  AdminTabsGeneralTab: typeof import("../../components/admin/tabs/GeneralTab.vue")['default']
  AdminTabsGroupingTab: typeof import("../../components/admin/tabs/GroupingTab.vue")['default']
  AdminTabsUserAccessTab: typeof import("../../components/admin/tabs/UserAccessTab.vue")['default']
  PreferencesNumberFormatForm: typeof import("../../components/preferences/NumberFormatForm.vue")['default']
  PreferencesPanel: typeof import("../../components/preferences/PreferencesPanel.vue")['default']
  PreferencesReportsPreferencesTab: typeof import("../../components/preferences/ReportsPreferencesTab.vue")['default']
  NuxtWelcome: typeof import("../../node_modules/nuxt/dist/app/components/welcome.vue")['default']
  NuxtLayout: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-layout")['default']
  NuxtErrorBoundary: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue")['default']
  ClientOnly: typeof import("../../node_modules/nuxt/dist/app/components/client-only")['default']
  DevOnly: typeof import("../../node_modules/nuxt/dist/app/components/dev-only")['default']
  ServerPlaceholder: typeof import("../../node_modules/nuxt/dist/app/components/server-placeholder")['default']
  NuxtLink: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-link")['default']
  NuxtLoadingIndicator: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-loading-indicator")['default']
  NuxtTime: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-time.vue")['default']
  NuxtRouteAnnouncer: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-route-announcer")['default']
  NuxtImg: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtImg']
  NuxtPicture: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtPicture']
  NuxtPage: typeof import("../../node_modules/nuxt/dist/pages/runtime/page")['default']
  NoScript: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['NoScript']
  Link: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Link']
  Base: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Base']
  Title: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Title']
  Meta: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Meta']
  Style: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Style']
  Head: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Head']
  Html: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Html']
  Body: typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Body']
  NuxtIsland: typeof import("../../node_modules/nuxt/dist/app/components/nuxt-island")['default']
  LazyBrowseAutocomplete: LazyComponent<typeof import("../../components/BrowseAutocomplete.vue")['default']>
  LazyGroupedTable: LazyComponent<typeof import("../../components/GroupedTable.vue")['default']>
  LazyMenuItem: LazyComponent<typeof import("../../components/MenuItem.vue")['default']>
  LazyPerkiraanAutocomplete: LazyComponent<typeof import("../../components/PerkiraanAutocomplete.vue")['default']>
  LazyReportTable: LazyComponent<typeof import("../../components/ReportTable.vue")['default']>
  LazySidebar: LazyComponent<typeof import("../../components/Sidebar.vue")['default']>
  LazyTopBar: LazyComponent<typeof import("../../components/TopBar.vue")['default']>
  LazyAdminTabsColumnsTab: LazyComponent<typeof import("../../components/admin/tabs/ColumnsTab.vue")['default']>
  LazyAdminTabsDatasetsTab: LazyComponent<typeof import("../../components/admin/tabs/DatasetsTab.vue")['default']>
  LazyAdminTabsFiltersTab: LazyComponent<typeof import("../../components/admin/tabs/FiltersTab.vue")['default']>
  LazyAdminTabsGeneralTab: LazyComponent<typeof import("../../components/admin/tabs/GeneralTab.vue")['default']>
  LazyAdminTabsGroupingTab: LazyComponent<typeof import("../../components/admin/tabs/GroupingTab.vue")['default']>
  LazyAdminTabsUserAccessTab: LazyComponent<typeof import("../../components/admin/tabs/UserAccessTab.vue")['default']>
  LazyPreferencesNumberFormatForm: LazyComponent<typeof import("../../components/preferences/NumberFormatForm.vue")['default']>
  LazyPreferencesPanel: LazyComponent<typeof import("../../components/preferences/PreferencesPanel.vue")['default']>
  LazyPreferencesReportsPreferencesTab: LazyComponent<typeof import("../../components/preferences/ReportsPreferencesTab.vue")['default']>
  LazyNuxtWelcome: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/welcome.vue")['default']>
  LazyNuxtLayout: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-layout")['default']>
  LazyNuxtErrorBoundary: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue")['default']>
  LazyClientOnly: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/client-only")['default']>
  LazyDevOnly: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/dev-only")['default']>
  LazyServerPlaceholder: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/server-placeholder")['default']>
  LazyNuxtLink: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-link")['default']>
  LazyNuxtLoadingIndicator: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-loading-indicator")['default']>
  LazyNuxtTime: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-time.vue")['default']>
  LazyNuxtRouteAnnouncer: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-route-announcer")['default']>
  LazyNuxtImg: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtImg']>
  LazyNuxtPicture: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtPicture']>
  LazyNuxtPage: LazyComponent<typeof import("../../node_modules/nuxt/dist/pages/runtime/page")['default']>
  LazyNoScript: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['NoScript']>
  LazyLink: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Link']>
  LazyBase: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Base']>
  LazyTitle: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Title']>
  LazyMeta: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Meta']>
  LazyStyle: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Style']>
  LazyHead: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Head']>
  LazyHtml: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Html']>
  LazyBody: LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components")['Body']>
  LazyNuxtIsland: LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-island")['default']>
}

declare module 'vue' {
  export interface GlobalComponents extends _GlobalComponents { }
}

export {}
