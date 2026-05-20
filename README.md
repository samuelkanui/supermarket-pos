# 🚀 Multi-Tenant SaaS Supermarket POS System (Production Ready)

Welcome to the **SaaS Supermarket POS System**, a high-performance, multi-tenant Point of Sale (POS) solution built specifically for scaling supermarkets, hypermarkets, and retail outlets. This system provides a unified cloud-native architecture that supports multiple independent tenants via custom subdomains, multi-branch operations per tenant, offline-resilient sales checkouts, real-time inventory synchronization, and M-Pesa automated billing.

---

## 🧠 System Architecture

The application is structured into layered architectural roles, isolating global administration, business ownership, branch management, and cashier checkouts.

```
                  ┌──────────────────────────────┐
                  │      SUPER ADMIN LAYER       │
                  │   (Plan Management, SaaS)    │
                  └──────────────┬───────────────┘
                                 │
                  ┌──────────────▼───────────────┐
                  │      TENANT SAAS LAYER       │
                  │   (Shop Owners - Subdomain)  │
                  └──────────────┬───────────────┘
                                 │
                  ┌──────────────▼───────────────┐
                  │         BRANCH LAYER         │
                  │   (Teams - Outlets/Stores)   │
                  └──────────────┬───────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         │                       │                       │
┌────────▼────────┐     ┌────────▼────────┐     ┌────────▼────────┐
│  ROLE: MANAGER  │     │  ROLE: CASHIER  │     │ ROLE: WAREHOUSE │
│ (Reports/Stock) │     │  (POS Checkout) │     │ (Receive/Transf)│
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

1.  **Super Admin Layer**: Manages pricing packages, monitors global system health, views revenue analytics, and controls system-wide configurations.
2.  **SaaS Tenant Layer**: Shop owners register their business and automatically receive a dedicated subdomain (e.g., `naivas.yourpos.com`). The business owner uses the unified **`Dashboard.vue`** platform to invite team members and delegate operational controls.
3.  **Branch Layer (Teams)**: Each physical branch operates as an independent operational unit (Team), keeping its own stocks and records while sharing products from the central tenant inventory database.
4.  **Role Layer (Staff Permissions)**: Staff members are added to branches with explicit roles:
    *   **Branch Manager**: Oversees sales, approves adjustments, and manages branch stocks.
    *   **Cashier**: Accesses the high-speed POS interface to execute sales.
    *   **Warehouse Staff**: Manages inventory reception and executes stock transfers between branches.
5.  **POS Checkout & Offline Layer**: Front-end checkout console that remains functional during internet outages using browser IndexedDB and background queues.

---

## 🧭 Complete System Workflow

```
[ SUPER ADMIN ] ──► Configures SaaS Plans, Subdomain DNS & global limits
       │
[ TENANT ADMIN ] ─► Registers business ──► Receives Subdomain (e.g., naivas.yourpos.com)
       │
       ├─────────► Enters Dashboard ──► Adds Branches (via Teams layout)
       │
       ├─────────► Invites Staff ──► Assigns roles: Cashier, Manager, Warehouse
       │
       └─────────► Manages Catalog ──► Uploads Products, Categories & Barcodes
       │
[ CASHIER / STAFF ] ──► Logs in on subdomain ──► Selects Active Branch
       │
       ├─────────► Opens POS checkout screen
       │
       ├─────────► Scans Barcode / Searches Product
       │
       ├─────────► Selects Payment: Cash / M-Pesa STK Push / Card
       │
       └─────────► Auto-updates Branch Inventory & prints receipt
```

---

## 🗄️ Database Schema Design

The system implements a robust single-database architecture using dynamic `tenant_id` scopes to ensure absolute data isolation.

### 1. Tenancy Core

#### `tenants`
Tracks independent tenant subscriptions and custom subdomains.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `name` (VARCHAR, Business/Shop Name)
*   `subdomain` (VARCHAR, Unique)
*   `domain` (VARCHAR, Nullable, Unique)
*   `plan_id` (BIGINT UNSIGNED, Foreign Key)
*   `status` (VARCHAR, Enum: `active`, `suspended`, `trial`)
*   `trial_ends_at` (TIMESTAMP, Nullable)
*   `created_at`, `updated_at`, `deleted_at`

#### `plans`
SaaS pricing plan structures and feature boundaries.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `name` (VARCHAR)
*   `price` (DECIMAL 10,2)
*   `billing_cycle` (VARCHAR, Enum: `monthly`, `yearly`)
*   `features` (JSON, e.g., max users, max branches limits)
*   `created_at`, `updated_at`

### 2. User & Branch Management

#### `users`
System accounts, linked to their parent Tenant.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `tenant_id` (BIGINT UNSIGNED, Nullable for Super Admins)
*   `name` (VARCHAR)
*   `email` (VARCHAR, unique within tenant)
*   `password` (VARCHAR)
*   `current_team_id` (BIGINT UNSIGNED, Nullable, maps to current active branch)
*   `created_at`, `updated_at`

#### `teams` (Branches)
Maps directly to retail outlets under the parent business.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `tenant_id` (BIGINT UNSIGNED, Foreign Key)
*   `name` (VARCHAR, e.g. "Nairobi West Outlet")
*   `slug` (VARCHAR, unique within tenant)
*   `is_personal` (BOOLEAN, default false)
*   `created_at`, `updated_at`, `deleted_at`

#### `team_members` (Staff Roles)
Assigns staff users to specific branches with specialized roles.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `team_id` (BIGINT UNSIGNED, Foreign Key)
*   `user_id` (BIGINT UNSIGNED, Foreign Key)
*   `role` (VARCHAR, Enum: `manager`, `cashier`, `warehouse`)
*   `created_at`, `updated_at`
*   *Unique Key*: `[team_id, user_id]`

### 3. Inventory & Catalog

#### `categories` & `suppliers`
*   `categories`: `id`, `tenant_id`, `name`, `slug`, `created_at`, `updated_at`
*   `suppliers`: `id`, `tenant_id`, `name`, `email`, `phone`, `address`, `created_at`, `updated_at`

#### `products`
Central product catalogs shared across all branches of the tenant.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `tenant_id` (BIGINT UNSIGNED, Foreign Key)
*   `category_id` (BIGINT UNSIGNED, Nullable, Foreign Key)
*   `name` (VARCHAR)
*   `sku` (VARCHAR, Unique within tenant)
*   `barcode` (VARCHAR, Nullable, Index for scan lookup)
*   `description` (TEXT, Nullable)
*   `cost_price` (DECIMAL 10,2)
*   `selling_price` (DECIMAL 10,2)
*   `vat_rate` (DECIMAL 5,2, Default 16.00)
*   `is_active` (BOOLEAN, Default true)
*   `created_at`, `updated_at`, `deleted_at`

#### `branch_stocks`
Monitors stock levels separately per branch outlet.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `tenant_id` (BIGINT UNSIGNED, Foreign Key)
*   `team_id` (BIGINT UNSIGNED, Foreign Key)
*   `product_id` (BIGINT UNSIGNED, Foreign Key)
*   `quantity` (DECIMAL 10,2)
*   `low_stock_threshold` (DECIMAL 10,2, Default 10.00)
*   `created_at`, `updated_at`
*   *Unique Key*: `[team_id, product_id]`

#### `stock_adjustments`
Audit log tracks adjustments, damages, or shrinkage.
*   `id`, `tenant_id`, `team_id`, `product_id`, `user_id`, `quantity`, `type` (Enum: `addition`, `reduction`, `damaged`), `reason` (TEXT), `created_at`

### 4. Sales & Receipts

#### `sales`
Individual cashier transactions.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `tenant_id` (BIGINT UNSIGNED, Foreign Key)
*   `team_id` (BIGINT UNSIGNED, Foreign Key)
*   `user_id` (BIGINT UNSIGNED, Foreign Key)
*   `customer_id` (BIGINT UNSIGNED, Nullable, Foreign Key)
*   `invoice_number` (VARCHAR, unique within tenant)
*   `subtotal`, `discount`, `tax`, `total` (DECIMALS)
*   `payment_method` (VARCHAR, Enum: `cash`, `mpesa`, `card`, `split`)
*   `payment_status` (VARCHAR, Enum: `paid`, `unpaid`, `partially_paid`, `refunded`)
*   `sync_status` (VARCHAR, Enum: `synced`, `pending`)
*   `offline_sale_id` (UUID, Nullable, prevent double sync)
*   `created_at`, `updated_at`

#### `sale_items`
Detailed checkout cart breakdown.
*   `id` (BIGINT UNSIGNED, Primary Key)
*   `sale_id` (BIGINT UNSIGNED, Foreign Key on Delete Cascade)
*   `product_id` (BIGINT UNSIGNED, Foreign Key)
*   `quantity`, `unit_price`, `tax_amount`, `discount_amount`, `total` (DECIMALS)
*   `created_at`, `updated_at`

#### `mpesa_transactions`
Automated reconciliation logs mapping Daraja STK Push responses.
*   `id`, `tenant_id`, `sale_id`, `merchant_request_id`, `checkout_request_id`, `result_code`, `result_desc`, `mpesa_receipt_number`, `amount`, `phone_number`, `status` (Enum: `pending`, `completed`, `failed`), `created_at`, `updated_at`

---

## 🧱 Development & Implementation Roadmap

The system is developed through a rigid, sequential pipeline to maintain structural stability:

1.  **Phase 1: Tenancy & Authentication Foundation**
    *   Setup `tenants` and `plans` migrations.
    *   Add `tenant_id` to `users`, `teams`, `team_members` tables.
    *   Build subdomain routing middleware (`TenantIdentify`) and Eloquent `TenantScope` traits.
    *   Update Fortify registration logic to auto-provision subdomains and default Main Branch (Team) under the new Tenant.
2.  **Phase 2: Central Catalog & Branch Inventory**
    *   Implement `categories`, `suppliers`, `products`, and `branch_stocks`.
    *   Provide view controls for managers to track and adjust stock per branch.
    *   Index barcodes in Redis for sub-millisecond cashier scans.
3.  **Phase 3: High-Performance Checkout Screen (POS)**
    *   Build split POS UI: Left side search catalog, Right side checkout cart.
    *   Add global key-listener to auto-apprehend barcode gun keyboard sequences.
4.  **Phase 4: Payment Engines (STK Push)**
    *   Integrate cash/card payment flow logs.
    *   Implement Safaricom Daraja REST client. cashiers prompt an STK push on customers' phones and check confirmation callbacks to conclude sales.
5.  **Phase 5: ESC/POS Thermal Receipt Support**
    *   Build print CSS layers.
    *   Enable silent browser fallback printing to local system receipt drivers.
6.  **Phase 6: Analytical Reporting Layouts**
    *   Generate real-time comparison tables of sales and cashier performances.
7.  **Phase 7: Resilient Offline Syncer**
    *   Connect browser IndexedDB (via Dexie.js) to cash product indices.
    *   Configure Service Worker intercepting offline checkouts and queueing them for server-sync upon recovery.
8.  **Phase 8: SaaS Subscriptions & SaaS Central Dashboard**
    *   Enforce plan restrictions (e.g. branch, product limits).
    *   Design a central system metrics command console for the Super Admin.

---

## 💻 Running & Deploying Locally

### Prerequisites
*   **Laragon** / **XAMPP** (with PHP 8.3+, MySQL, Apache)
*   **Node.js** (v20+) & **npm**
*   **Redis** (enabled in Laragon)
*   **Local DNS Setup**:
    Add the wildcard local domain wildcard mapping in Laragon `hosts` file:
    ```text
    127.0.0.1  supermarket-pos.test
    127.0.0.1  naivas.supermarket-pos.test
    127.0.0.1  quickmart.supermarket-pos.test
    ```

### Installation Steps
1.  Clone the repository and install Composer packages:
    ```bash
    composer install
    ```
2.  Install frontend dependencies:
    ```bash
    npm install
    ```
3.  Run database migrations and seed default plans:
    ```bash
    php artisan migrate
    ```
4.  Start local development servers:
    ```bash
    npm run dev
    ```
    *(Uses `concurrently` to run `artisan serve`, queue listening, and Vite compilation).*

---

## 🚀 SaaS Production Deployment
*   **Stack**: Nginx, PHP-FPM, MySQL 8+, Redis Server.
*   **Nginx Configuration**: Enable wildcard subdomains server block:
    ```nginx
    server {
        listen 80;
        server_name *.yourpos.com;
        root /var/www/supermarket-pos/public;
        ...
    }
    ```
*   **SSL**: Configure wildcard Let's Encrypt certificates (`*.yourpos.com`) using Certbot.
