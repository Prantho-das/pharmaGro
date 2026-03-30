# 🏥 OmniPOS: Implementation Plan

## 📋 Current Status
- **Framework:** Laravel 13.2.0 ✅
- **Admin Panel:** Filament v5.4.3 ✅
- **Frontend:** Livewire v4.2.2 + Tailwind v4.2.2 ✅
- **Testing:** Pest v4.4.3 ✅
- **Database:** SQLite (Ready to migrate to PostgreSQL/MySQL) ✅
- **Project State:** Fresh installation, ready for development

---

## 🗓️ Implementation Timeline

### Week 1: Core Foundation (Mar 31 - Apr 6)

#### Day 1-2: Database Schema & Models
- [ ] Create migrations based on `plan-sql.md`
  - Categories table (with type enum: pharma, grocery, cosmetics, general)
  - Brands table
  - Units table
  - Products table
  - Product variants table
  - Inventory batches table (FEFO logic ready)
  - Customers table
  - Sales table
  - Sale items table
  - Settings table
- [ ] Add database indexes for performance:
  - `sku` on product_variants
  - `invoice_no` on sales
  - `phone` on customers
  - `expiry_date` on inventory_batches
  - Foreign keys automatically indexed
- [ ] Create Eloquent Models with relationships:
  - `Category` (hasMany Products)
  - `Brand` (hasMany Products)
  - `Unit`
  - `Product` (belongsTo Category/Brand, hasMany Variants)
  - `ProductVariant` (belongsTo Product/Unit, hasMany Batches)
  - `InventoryBatch` (belongsTo Variant)
  - `Customer`
  - `Sale` (belongsTo Customer, hasMany SaleItems, soldBy Staff)
  - `SaleItem` (belongsTo Sale, Variant, Batch)
  - `Setting`

#### Day 3: Authentication & RBAC
- [ ] Implement Spatie Laravel Permission or custom roles:
  - Roles: `admin`, `manager`, `staff`
  - Permissions: `manage_products`, `manage_inventory`, `process_sales`, `view_reports`, `manage_settings`
- [ ] Configure Filament users with role-based access
- [ ] Create login/logout with proper guards
- [ ] Add staff management resource (name, phone, role, status)

#### Day 4-5: Dynamic Settings & Pharmacy Mode
- [ ] Create Setting model with cache driver (Redis-ready)
- [ ] Build Settings service class:
  ```php
  class SettingsService {
      public static function get(string $key, $default = null)
      public static function set(string $key, $value)
      public static function pharmacyModeEnabled(): bool
  }
  ```
- [ ] Create Filament Resource for settings management
- [ ] Add `is_pharmacy_active` toggle
- [ ] Implement config cache fallback

#### Day 6-7: Master Catalog
- [ ] Create Filament Resources:
  - Categories (with type selector)
  - Brands
  - Units
- [ ] Add bulk operations (import/export CSV)
- [ ] Implement soft deletes for all catalog items
- [ ] Add search and filtering to all resources
- [ ] Create default seeders for common units (pc, strip, box, kg, g, ml, ltr)

---

### Week 2: Inventory & Batch Logic (Apr 7 - Apr 13)

#### Day 1-2: Product & Variant Engine
- [ ] Create Product form in Filament with variant matrix:
  - Tabbed interface: "Basic Info" + "Variants"
  - Dynamic variant creation (size, strength, packaging)
  - Automatic SKU generation: `{brand-code}-{product-code}-{variant-code}`
  - Bulk variant import from CSV
- [ ] Validate unique SKU constraints
- [ ] Add image upload per variant (optional)
- [ ] Implement `min_stock_alert` per variant

#### Day 3-5: Batch Management & FEFO
- [ ] Create InventoryBatch management:
  - Add batches via "Receive Stock" form
  -Fields: batch_no, expiry_date, purchase_price, selling_price, quantity
  - Auto-increment batch number: `B-{year}-{month}-{sequence}`
- [ ] Implement FEFO query scope:
  ```php
  InventoryBatch::forVariant($variantId)
      ->active()
      ->orderByExpiry() // ASC
      ->available()
  ```
- [ ] Stock deduction logic: always deduct from earliest expiry batch first
- [ ] Add batch search by expiry range
- [ ] Create "Expiry Alert" Filament page (next 60-90 days)
- [ ] Implement batch transfer between variants (if needed)

#### Day 6-7: Stock Tracking
- [ ] Add `current_stock` aggregation on ProductVariant:
  - Use Eloquent accessor: `getStockAttribute()` that sums batches
  - Cache stock values for 5 minutes (Redis)
- [ ] Create "Stock Adjustment" journal entry:
  - Reason: damage, loss, correction, return
  - Audit trail with staff ID
- [ ] Add "Low Stock" alerts on Filament dashboard
  - Configurable threshold per variant
  - Widget showing products below min_stock_alert
- [ ] Implement stock count/discrepancy reconciliation

---

### Week 3: High-Speed POS Interface (Apr 14 - Apr 20)

#### Day 1-3: POS Layout & Keyboard Navigation
- [ ] Create dedicated POS route: `/pos` (Livewire component)
- [ ] Build full-screen POS interface:
  - Left: Product grid/variant search (search by name, SKU, barcode)
  - Right: Cart/Hold/Checkout
  - Top: Quick actions (Hold, Clear, Pay)
- [ ] Implement keyboard shortcuts:
  - `F2` = Focus search
  - `Enter` = Add to cart
  - `F4` = Hold
  - `F5` = Recall hold
  - `F6` = Checkout
  - `F7` = Discount
  - `Escape` = Cancel/Back
- [ ] Barcode scanner support (USB HID keyboard emulation):
  - Auto-submit on Enter in search field
  - Parse SKU from barcode
  - Fallback to EAN-13 lookup
- [ ] Add recent products quick-add buttons

#### Day 4-5: Conditional UI (Pharmacy Mode)
- [ ] Create POS component with pharmacy toggle:
  ```php
  if (Settings::pharmacyModeEnabled()) {
      // Show generic_name prominently
      // Show expiry date on cart items
      // Require batch selection for medicines
  }
  ```
- [ ] In pharmacy mode:
  - Show both brand name and generic name
  - Display expiry date prominently on each cart line
  - Warn if batch expires in < 6 months (yellow) / < 3 months (red)
  - Filter medicine category only
- [ ] In general retail mode:
  - Hide generic_name
  - Hide expiry info
  - Show simple product grid

#### Day 6-7: Transaction Logic
- [ ] Cart management:
  - Add/remove/update quantities
  - Price override (manager权限 required)
  - Per-item discount
  - Global discount (percentage/fixed)
- [ ] Hold transactions:
  - Save cart to session/database
  - Recall with receipt preview
  - Auto-clear after 24h (cron)
- [ ] Payment integration:
  - Cash (manual input)
  - bKash/Nagad (mobile number + PIN confirmation screen)
  - Card (manual auth code)
  - Split payment (multiple methods)
- [ ] Customer linking:
  - Search/create walking customer
  - Points accumulation (configurable rate)
  - Due tracking for regular customers
- [ ] Complete sale flow:
  - Validate stock availability
  - Deduct stock via FEFO
  - Create sale + sale_items
  - Generate invoice_no: `INV-{YYYY}-{MM}-{DD}-{sequence}`
  - Print thermal receipt (80mm format)
  - Option: save as hold instead of complete

---

### Week 4: Reports & API Ready (Apr 21 - Apr 27)

#### Day 1-3: Financial Insights
- [ ] Create Sales Dashboard (Filament):
  - Today's sales, cash, card, mobile
  - Top-selling products (by quantity & revenue)
  - Profit margin analysis (batch-based)
- [ ] Profit calculation logic:
  ```php
  // For each sale item, get purchase price from batch
  $purchasePrice = $batch->purchase_price;
  $profit = ($item->unit_price - $purchasePrice) * $item->quantity;
  ```
- [ ] Batch-level profit reports:
  - Which batches sold better
  - Expiry impact on margins
- [ ] Date-range filtering (today, week, month, custom)
- [ ] Export to CSV/PDF

#### Day 4: Expiry Forecaster
- [ ] Expiry report:
  - All batches expiring next 30/60/90/180 days
  - Quantity + current selling price
  - Potential loss calculation
  - Group by product
- [ ] Email alert to managers (daily cron)
- [ ] Dashboard widget: "Expiring Soon" (top 10)

#### Day 5-7: API & Mobile Preparation
- [ ] Install Laravel Sanctum
- [ ] Configure API routes in `routes/api.php`:
  - `POST /api/auth/login` (staff)
  - `POST /api/auth/logout`
  - `GET /api/products/search?q={term}` (SKU/name lookup)
  - `GET /api/variant/{id}/stock` (real-time stock)
  - `GET /api/batches/{variantId}` (FEFO sorted)
  - `POST /api/sales` (create sale with token)
  - `GET /api/sales/{id}` (receipt)
- [ ] Create API Resources for JSON serialization
- [ ] Implement rate limiting (60/min for authenticated, 10/min for guests)
- [ ] Add API documentation (OpenAPI/Swagger)
- [ ] Test with Postman/Insomnia

---

## 🎯 Additional Features (Post-Week 4)

### Mobile App Support
- [ ] Camera barcode scanning (React Native/Flutter)
- [ ] Offline-first sync (queue sales when offline)
- [ ] Owner dashboard (sales KPIs, top products)

### Advanced Features
- [ ] Purchase management module (suppliers, purchase orders, GRN)
- [ ] Returns & refunds (credit notes)
- [ ] Loyalty program (points, tiers, SMS notifications)
- [ ] Accounting integration (journal entries, chart of accounts)
- [ ] Multi-store support (if needed)

---

## 📂 File Structure

```
app/
├── Domain/
│   ├── Catalogs/
│   │   ├── Models/
│   │   │   ├── Category.php
│   │   │   ├── Brand.php
│   │   │   └── Unit.php
│   │   └── Resources/
│   │       └── filament/
│   ├── Products/
│   │   ├── Models/
│   │   │   ├── Product.php
│   │   │   ├── ProductVariant.php
│   │   │   └── InventoryBatch.php
│   │   ├── Services/
│   │   │   ├── BatchService.php (FEFO logic)
│   │   │   └── StockService.php
│   │   └── Resources/
│   │       └── filament/
│   ├── Sales/
│   │   ├── Models/
│   │   │   ├── Sale.php
│   │   │   ├── SaleItem.php
│   │   │   └── Customer.php
│   │   ├── Services/
│   │   │   ├── SaleService.php
│   │   │   └── PaymentService.php
│   │   └── Resources/
│   │       └── filament/
│   └── Settings/
│       ├── Models/
│       │   └── Setting.php
│       ├── Services/
│       │   └── SettingsService.php
│       └── Resources/
│           └── filament/
├── Http/
│   ├── Controllers/
│   │   ├── PosController.php
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   └── SaleController.php
│   └── Livewire/
│       ├── Pos/
│       │   ├── PosScreen.php
│       │   ├── Cart.php
│       │   ├── PaymentModal.php
│       │   └── HoldRecall.php
│       └── Pos/
│           └── PharmacyModeToggle.php
├── Policies/
├── Requests/
└── Database/
    ├── Migrations/ (auto-generated)
    ├── Seeders/
    │   ├── DatabaseSeeder.php
    │   ├── CategorySeeder.php
    │   ├── UnitSeeder.php
    │   └── SettingsSeeder.php
    └──Factories/ (for Pest testing)
```

---

## 🔧 Technical Decisions

### Database
- **Migration Strategy:** From SQLite to PostgreSQL (better for concurrent POS operations)
- **Indexing:** Add composite indexes on:
  - `inventory_batches(variant_id, expiry_date, current_stock)`
  - `sales(created_at, payment_method)`
  - `sale_items(sale_id, variant_id)`
- **Caching:** Redis for:
  - Settings (5 min)
  - Stock counts (1 min)
  - Product lookups (10 min)

### Performance Optimizations
- Laravel Octane ready (keep routes pure, avoid session state in API)
- Database query optimization:
  - Eager load relationships in POS
  - Use `chunk()` for reports
  - Database-level FEFO ordering (index on expiry_date)
- Frontend:
  - Livewire wire:loading indicators
  - Debounce search input (300ms)
  - Virtual scrolling for product grid (if >500 items)

### Security
- Sanctum tokens with 24h expiration for mobile
- Rate limiting per IP and per token
- All price modifications require manager role
- Batch purchase_price hidden from staff (only visible to admin/manager)
- Audit log for all stock adjustments and transactions

### Testing Strategy (Pest)
```
tests/
├── Unit/
│   ├── BatchServiceTest.php (FEFO logic)
│   ├── SettingsServiceTest.php
│   └── SaleServiceTest.php (profit calculation)
├── Feature/
│   ├── Pos/
│   │   ├── PosProcessingTest.php
│   │   ├── PharmacyModeTest.php
│   │   └── KeyboardShortcutsTest.php
│   ├── Api/
│   │   ├── ApiAuthTest.php
│   │   └── ApiProductSearchTest.php
│   └── Reports/
│       ├── ProfitReportTest.php
│       └── ExpiryAlertTest.php
└── Browser/
    └── Pos/
        └── FullSaleFlowTest.php (visits /pos, simulates sale)
```

---

## 🚀 Quick Start Commands

```bash
# 1. Install Filament (already done)
php artisan filament:install --panels

# 2. Create migrations from plan-sql.md
# (to be executed sequentially)

# 3. Run migrations
php artisan migrate

# 4. Seed default data
php artisan db:seed --class=UnitSeeder
php artisan db:seed --class=SettingsSeeder

# 5. Create admin user
php artisan make:filament-user

# 6. Install Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

# 7. Generate models with factory & seeder
php artisan make:model Category -mfsr
php artisan make:model Brand -mfsr
# ... repeat for all models

# 8. Build assets
npm run build

# 9. Run tests
php artisan test --compact

# 10. Start server
php artisan serve
```

---

## ⚠️ Known Constraints & Mitigations

| Constraint | Mitigation |
|------------|------------|
| SQLite doesn't support ENUM | Use string column with validation in migration: `$table->string('type')->default('pharma')` |
| Concurrent stock updates | Use database transactions + row locking (`SELECT ... FOR UPDATE`) |
| FEFO performance on large batches | Index on `(variant_id, expiry_date, current_stock)`, cache stock |
| Thermal printing variances | Use ESC/POS commands with `windows-1252` charset, test on actual printer |
| bKash/Nagad no official API | Manual entry + verification screen (future: SDK integration) |

---

## 📊 Success Metrics

- **POS Transaction Time:** < 30 seconds from scan to receipt
- **Batch FEFO Accuracy:** 100% (always oldest first)
- **API Response Time:** < 200ms for product search
- **Test Coverage:** > 80% (unit + feature)
- **Zero stock mismatch** after 1000 test transactions

---

## 🔄 Next Steps

1. ✅ Review this plan with stakeholders
2. ✅ Confirm database choice (PostgreSQL recommended)
3. ✅ Approve tech stack (already locked in)
4. ⏭️ **Start implementing Week 1** (Database Migrations → Models → Seeds)
5. ⏭️ Set up Git repository (if not already)
6. ⏭️ Configure CI/CD for tests

---

**Ready to start building?** The architecture is solid, the schema is normalized for performance, and the Phased approach allows incremental delivery. Let's begin with **Day 1: Database Migrations**.
