# Technology Stack

This document details the core languages, runtimes, frameworks, dependencies, and configuration settings used throughout the KHI Hakizen application.

## Core Runtime & Frameworks

- **PHP**: `^8.2` (Running on PHP `8.5.6` in runtime)
- **Laravel Framework**: `^12.0` (Upgraded from Laravel 10 structure, maintaining traditional directory structure)
- **Laravel Folio**: `^1.1` (Page-based routing, resolving dynamically to the active theme's pages directory)
- **Laravel Livewire**: `^3.0` (Server-driven reactive components)
- **Livewire Volt**: `^1.1` (Single-file Livewire components mounted directly from theme pages)
- **Filament PHP**: `^3.2` (Administration panel, form-builder, table-builder, infolists, and widgets)
- **Wave SaaS Engine**: `0.11.0` (DevDojo SaaS framework providing subscriptions, custom themes, roles, billing, and auth hooks)

## Database & Persistence

- **Database Engine**: Relational database (typically MySQL/PostgreSQL in Laravel setup)
- **Eloquent ORM**: Primary data mapping framework
- **Laravel Migrations**: Tracks database schemas iteratively

## Frontend Architecture

- **CSS & Styling**: Tailwind CSS `^3.4.3` with `@tailwindcss/forms` and `@tailwindcss/typography`
- **Reactivity & Client Logic**: Alpine.js `^3.4.2`
- **Asset Bundling**: Vite `^5.4` with `laravel-vite-plugin`
- **HTTP Client**: Axios `^1.7.4`

## Core Packages & External Integrations

| Package / Integration | Version | Description |
|-----------------------|---------|-------------|
| `devdojo/app` | `0.11.0` | SaaS starter kit engine |
| `devdojo/auth` | `^1.0` | Authentication UI and helpers |
| `devdojo/themes` | `0.0.11` | Theme management logic |
| `laravolt/indonesia` | `^0.36.0` | Comprehensive Indonesian administrative regions database |
| `spatie/laravel-permission`| `^6.4` | Role and Permission authorization system |
| `stripe/stripe-php` | `^15.3` | International card payments and subscriptions |
| `xendit/xendit-php` | `^6.3` | Indonesian payment gateway (QRIS, E-Wallet, VA, Bank Transfer) |
| `ralphjsmit/livewire-urls` | `^1.4` | Helper for tracking Livewire page history and URL states |
| `bezhansalleh/filament-google-analytics` | `^2.0` | Google Analytics dashboard integration inside Filament |
| `alperenersoy/filament-export` | `^3.0` | Excel/PDF table exports inside Filament panels |
| `barryvdh/laravel-dompdf` | `^3.1` | PDF generation utility (invoices, receipts) |
| `intervention/image` | `^2.7` | Image upload manipulation and resizing |

## Development & Testing Stack

- **Testing Framework**: Pest PHP `^3.4`
- **Browser Testing**: Laravel Dusk `^8.0` (with `alebatistella/duskapiconf`)
- **PHPUnit**: `^11.0`
- **Laravel Boost**: `^2.1` (MCP server integration and developer utility toolkit)
- **FakerPHP**: `^1.9.1`

---
*Last updated: 2026-05-26 after initialization*
