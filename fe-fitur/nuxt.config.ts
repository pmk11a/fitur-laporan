export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

  components: {
    dirs: ['components']
  },

  css: ['~/assets/css/main.css'],

  app: {
    baseURL: '/Dapen/Fe-Dapen/', // Wajib diakhiri dengan garis miring
    head: {
      title: 'Fluffy Bee - Dynamic Report Engine',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Dynamic Form/Report Engine for ERP' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
      ]
    }
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8080/api'
    }
  },

  nitro: {
    experimental: {
      asyncContext: true
    }
  },
  devServer: {
    host: '0.0.0.0',
    port: 3000
  }
})