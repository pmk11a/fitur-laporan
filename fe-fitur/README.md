# Fluffy Bee - Frontend

Dynamic Report/Form Engine Frontend using Nuxt.js 3 + Tailwind CSS.

## Setup

```bash
cd fe-fitur
npm install
```

## Development

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000)

## Features

- **Login Page** - Authentication with username/password
- **Dashboard** - Main dashboard with stats and quick actions
- **Sidebar** - Dynamic menu based on user access (ACCESS bitmask)
- **Reports** - List of available reports filtered by permissions
- **Admin Panel** - Configuration for admins (access >= 255)

## Environment

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api
```

## Pages

- `/login` - Login page
- `/dashboard` - Main dashboard (protected)
- `/reports` - List of reports (protected)
- `/reports/[KODEMENU]` - Individual report (protected)

## Backend API

The frontend expects these endpoints from Laravel backend:

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login with username/password |
| POST | `/api/auth/logout` | Logout user |
| GET | `/api/auth/me` | Get current user |
| GET | `/api/menu` | Get menu items by access |

## Tech Stack

- Nuxt.js 3
- Vue 3 (Composition API)
- Tailwind CSS
- Pinia (State Management)