# External Integrations & APIs

This document outlines the external systems, payment gateways, databases, and APIs integrated into the KHI Hakizen application.

## 1. Payment Gateways

### Xendit (Indonesian Payment Gateway)
- **Purpose**: Serves as the primary payment processor for domestic payments in Indonesia. Handles QRIS, E-Wallet payments, Virtual Accounts (VA), and direct Bank Transfers.
- **Endpoints**:
  - Webhook URL callback: `/api/xendit/callback`
  - Invoice Creation (Checkout): `/api/checkout/create-invoice` (protected by `auth` and `throttle:checkout` middlewares)
  - Invoice Creation (Event booking): `/api/events/checkout/create-invoice` (protected by `auth` and `throttle:checkout` middlewares)
- **Classes**:
  - `App\Http\Controllers\XenditController` (Controller interface)
  - `App\Services\XenditInvoiceGateway` (Gateway client)
  - `App\Actions\CreateCheckoutInvoiceAction` (Workflow action to compile orders and create checkout invoices)
  - `App\Actions\HandleXenditWebhookAction` (Webhook handler callback verification and payment state transitions)
- **Configuration variables**: `XENDIT_SECRET_KEY`

### Stripe (International Billing)
- **Purpose**: Handles international subscriptions and standard card payments (via the DevDojo Wave system).
- **Configuration variables**: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`

## 2. Shipping & Logistics

### RajaOngkir (Komerce Logistics API)
- **Purpose**: Dynamic lookup of administrative regions (Provinces, Cities, Districts, Sub-districts) and postal codes in Indonesia, as well as calculating real-time courier shipping costs.
- **Base Endpoint**: `https://rajaongkir.komerce.id/api/v1` (Komerce logistics endpoint wrapper)
- **Sub-endpoints**:
  - `destination/province`
  - `destination/city/{provinceCode}`
  - `destination/district/{cityCode}`
  - `destination/sub-district/{districtCode}`
  - `destination/domestic-destination` (For dynamic destination searching)
- **Classes**:
  - `App\Http\Controllers\RajaOngkirLocationLookup` (Provides caching and search capability for locations)
- **Configuration variables**: `services.rajaongkir.api_key`, `services.rajaongkir.base_url`, `services.rajaongkir.cache_ttl`

## 3. Analytics & Logging

### Google Analytics
- **Purpose**: Tracks visitor behavior and captures user conversion metrics.
- **Integration**: `bezhansalleh/filament-google-analytics` renders real-time tracking graphs within Filament.
- **Configuration variables**: Google Analytics tracking ID and key credentials.

## 4. Administrative Data

### Laravolt Indonesia
- **Purpose**: Local database region mapping populated via seeds. Stores full lists of Indonesian Provinces, Cities, Districts, and Villages to ensure users can input valid physical delivery addresses without external API overhead for base region lists.
- **Tables**: `provinces`, `cities`, `districts`, `villages`

---
*Last updated: 2026-05-26 after initialization*
