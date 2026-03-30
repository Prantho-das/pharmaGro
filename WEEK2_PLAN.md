# Week 2 Plan: Inventory Management & Filament Admin UI

## 📋 Week 2 Overview

**Goal:** Build the Filament admin panel interface for managing inventory, products, and batches with full FEFO logic integration.

**Theme:** "Staff can receive stock, manage batches, see expiry alerts, and understand inventory at a glance"

**Deliverables by End of Week 2:**
1. ✅ Complete Filament Resources for all core entities
2. ✅ Intelligent batch management with FEFO automation
3. ✅ Dashboard widgets for expiry alerts & low stock
4. ✅ Product variant creation UI (matrix style)
5. ✅ Sales history with batch-level traceability
6. ✅ All features role-aware (admin vs manager vs staff)

---

## 🎯 Week 2 Success Criteria

| Metric | Target |
|--------|--------|
| Filament Resources Created | 8 (Category, Brand, Unit, Product, ProductVariant, InventoryBatch, Customer, Sale) |
| Forms with Custom Logic | 100% (all forms use our Services) |
| Dashboard Widgets | 3 (Expiring Soon, Low Stock, Today's Sales) |
| Time to Create Product+Variant | < 5 minutes |
| Batch Receiving Workflow | Single page, < 3 clicks |
| Test Coverage (New UI) | > 70% |

---

## 📅 Daily Breakdown (Apr 7-13)

### Day 1 (Mon): Filament Setup & Basic Resources

**Morning (3h):**
- [ ] Install & configure Filament v5 properly
  - `php artisan filament:install --panels`
  - Configure admin panel (branding, navigation)
  - Set up authentication guard (web)
- [ ] Create Filament Resources for simple catalogs:
  - `php artisan make:filament-resource Category`
  - `php artisan make:filament-resource Brand`
  - `php artisan make:filament-resource Unit`
- [ ] Customize each resource:
  - Add `type` badge on Category (pharma/grocery/cosmetics)
  - Make categories filterable by type
  - Add search by name for all three

**Afternoon (3h):**
- [ ] Create Product Resource with custom form:
  - Tabbed form: Basic Info + Variants
  - Basic tab: name, generic_name, category, brand, is_medicine, has_variants, image upload
  - Variants tab: Repeater or table to create variants inline
  - Auto-generate SKU when variant added: `{brand-code}-{product-code}-{variant-index}`
  - Preview variants before saving
- [ ] Create ProductVariant Resource:
  - Show parent product (BELONGS_TO relationship)
  - Fields: product, unit, variant_name, sku, min_stock_alert
  - Read-only stock display (from Batches sum)
  - Actions: "View Batches", "Adjust Stock"
- [ ] Test: Create "Napa" product with 2 variants

**End of Day 1 Deliverable:** Admin can manage catalog (categories, brands, units, products, variants) entirely through Filament.

---

### Day 2 (Tue): Batch Management & FEFO UI

**Morning (3h):**
- [ ] Create InventoryBatch Resource:
  - **Index page:** filter by variant, batch_no, expiry range
  - Columns: batch_no, variant (product name + variant), expiry_date (colored: red if <90d, yellow <180d), purchase_price (hidden from staff), selling_price, current_stock, is_active toggle
  - **Form:** variant selector (searchable), batch_no (auto-suggest: B-{year}-{seq}), expiry_date picker, purchase_price (manager only), selling_price, current_stock, is_active
- [ ] Create "Receive Stock" custom page (NOT a resource):
  - Route: `/admin/inventory/receive`
  - Single form to receive stock:
    - Select product → variant (cascading dropdowns)
    - Batch details: batch_no, expiry_date, purchase_price, selling_price, quantity
    - On submit:
      1. Check if batch_no exists for this variant → append stock
      2. Else create new batch with `is_active=true`
      3. Use `BatchService` to create/update
      4. Invalidate stock cache
      5. Show success: "Added 100 pcs to batch B-2026-03"
  - Use Filament's `Page` class with `HasTable`? No, custom form page

**Afternoon (3h):**
- [ ] Implement "Stock Transfer" feature:
  - Move stock between batches of same variant
  - Reason: batch consolidation, damaged batch correction
  - Audit trail (who transferred what)
- [ ] Add batch-level actions:
  - Deactivate batch (moves stock to 0, keeps history)
  - Reactivate batch
  - Delete batch (only if stock=0)
- [ ] Price management rules:
  - Staff: cannot see purchase_price (only selling_price)
  - Manager: can edit both
  - Use Filament's `canView()` / `canEdit()` on fields
- [ ] Test FEFO logic in UI:
  - Create 3 batches with different expiry
  - Make a sale → verify stock deducted from earliest

**End of Day 2 Deliverable:** Complete inventory management with FEFO automation, batch receiving in <5 clicks.

---

### Day 3 (Wed): Sales History & Reports

**Morning (3h):**
- [ ] Create Sale Resource:
  - **Index:** filter by date range, invoice_no, customer, payment_method
  - Columns: invoice_no, customer_name, grand_total, payment_method, sold_by (staff), created_at
  - **Show page:** Full receipt view with:
    - Invoice header: no, date, customer, staff
    - Items table: variant name, batch_no, expiry_date (if pharma), quantity, unit_price, total
    - Summary: sub_total, discount, grand_total, payment, due
    - Action: Print receipt (PDF or thermal)
- [ ] Create SaleItem Resource (read-only):
  - View-only table showing all items in a sale
  - Link to batch details
  - Show profit calculation: (selling - purchase) × qty

**Afternoon (3h):**
- [ ] Create custom "Reports" section in Filament:
  - **Page 1: Sales Dashboard**
    - Today's total sales, cash, card, bKash
    - Top 10 products by quantity & revenue (last 7 days)
    - Profit margin by batch (which batches most profitable)
    - Chart: sales trend (last 30 days)
  - **Page 2: Expiry Forecaster**
    - Table: batches expiring next 30/60/90/180 days
    - Columns: product, variant, batch_no, expiry_date, stock, selling_price, potential_loss
    - Export to CSV
  - **Page 3: Low Stock Monitor**
    - All variants where stock ≤ min_stock_alert
    - Sort by stock ascending
    - Quick action: "Receive Stock" button linking to receive page

**End of Day 3 Deliverable:** Complete sales history, traceability, and reporting dashboard.

---

### Day 4 (Thu): Customer Management & POS Prep

**Morning (3h):**
- [ ] Create Customer Resource:
  - Fields: name, phone (unique), points
  - Index: search by name/phone
  - Show sales history summary: total purchases, total due
  - Actions: "Add Points", "Reset Points"
- [ ] Enhance Sale creation with customer linking:
  - In Sale Resource form: search customer by phone/name
  - If new customer: create inline (modal)
  - Points calculation: 1 point per 10 Taka spent (configurable later)
- [ ] Create "Hold" functionality for POS (pre-sales):
  - Save incomplete sale to database with `status = 'hold'`
  - Recall from hold to complete
  - Auto-delete holds older than 24h (cron later)

**Afternoon (3h):**
- [ ] Build **POS Practice Page** (not yet full-screen):
  - Route: `/pos` (public, auth required)
  - Simple Livewire component:
    - Left: search box (SKU/name)
    - Right: cart table (add/remove/update qty)
    - Top: Hold, Checkout buttons
  - Test keyboard navigation:
    - Enter to add
    - F2 focus search
    - F6 checkout
  - Conditional UI: if pharmacy mode → show expiry on cart items

**End of Day 4 Deliverable:** Customer management ready, basic POS UI functional.

---

### Day 5 (Fri): Polish & Integration Testing

**Morning (3h):**
- [ ] Add role-based visibility throughout:
  - Purchase price: staff → hidden, manager → visible
  - Settings resource: admin only
  - Batch deletion: admin/manager only
  - Sale editing/disabling: admin only (no edits allowed for old sales)
- [ ] Implement soft deletes where needed:
  - Products, Variants, Batches: soft delete
  - Restore capability in Filament
- [ ] Add audit logging (simple table):
  - `audit_logs`: user_id, action, model_type, model_id, changes_json, ip_address
  - Trigger on: batch create/update, sale create, stock adjustment
- [ ] Create Filament widgets:
  - "Expiring Soon" widget (top 5 batches)
  - "Low Stock" widget (top 10 variants)
  - "Today's Sales" widget (running total)

**Afternoon (3h):**
- [ ] Performance testing:
  - Load 100 products in variant grid → smooth?
  - Test batch receiving with 1000 existing batches
  - Check query count on sales index ( N+1 issues?)
- [ ] Write configuration docs:
  - `docs/filament-setup.md` - how to use admin panel
  - `docs/inventory-workflow.md` - step-by-step batch receiving
  - `docs/role-permissions.md` - what each role can do
- [ ] Create 5-10 test transactions covering:
  - FEFO deduction (verify earliest batch used)
  - Multi-batch sale (quantity > batch1 stock)
  - Expiry alert UI (batch expiring in 60d shows yellow)
  - Pharmacy mode toggle

**End of Day 5 Deliverable:** Polished admin panel, documentation, test coverage.

---

## 🔧 Technical Implementation Details

### Filament Resource Customizations

**ProductResource Form (tabs):**
```php
Form::schema([
    Section::make('Basic Info')
        ->schema([
            TextInput::make('name')->required(),
            TextInput::make('generic_name'),
            Select::make('category_id')->relationship('category', 'name'),
            Select::make('brand_id')->relationship('brand', 'name'),
            Toggle::make('is_medicine'),
            Toggle::make('has_variants'),
            FileUpload::make('image_path')->image(),
        ]),
    Section::make('Variants')
        ->schema([
            Repeater::make('variants')
                ->relationship()
                ->schema([
                    TextInput::make('variant_name')->required(),
                    Select::make('unit_id')->relationship('unit', 'short_name'),
                    TextInput::make('sku')->required()->unique(),
                    IntegerInput::make('min_stock_alert')->default(10),
                ])
                ->columns(2),
        ]),
]);
```

**InventoryBatchResource - Price Field Visibility:**
```php
TextInput::make('purchase_price')
    ->numeric()
    ->visible(fn (Navigator $navigator) => $navigator->getLivewire()->can('manage_inventory')),
```

**Receive Stock Page:**
```php
public static function getNavigationBadge(): ?string
{
    return static::getNavigationBadgeColor() === 'danger'
        ? 'New'
        : null;
}
```

### FEFO Logic in Livewire

When sale is created:
1. Controller calls `BatchService::deductStockTransaction($variant, $qty)`
2. Returns array of `[batchId => deductQty]`
3. Create SaleItem records with batch_id from allocations
4. Stock counts update automatically

### Caching Strategy

```php
// In BatchService - getFefoBatches
Cache::remember("fefo:variant:{$variant->id}", 300, function () use ($variant) {
    return $variant->batches()
        ->where('is_active', true)
        ->where('current_stock', '>', 0)
        ->orderBy('expiry_date', 'asc')
        ->get();
});
```

---

## 📁 Files to Create/Modify

### New Filament Resources
```
app/Filament/Resources/
├── CategoryResource.php
├── BrandResource.php
├── UnitResource.php
├── ProductResource.php
├── ProductVariantResource.php
├── InventoryBatchResource.php
├── CustomerResource.php
└── SaleResource.php
```

### Custom Pages (Non-CRUD)
```
app/Filament/Pages/
├── ReceiveStockPage.php
├── Reports/
│   ├── SalesDashboard.php
│   ├── ExpiryForecaster.php
│   └── LowStockMonitor.php
```

### Widgets
```
app/Filament/Widgets/
├── ExpiringSoonWidget.php
├── LowStockWidget.php
└── TodaySalesWidget.php
```

### Service Updates
```
app/Services/
├── BatchService.php (add caching)
├── StockService.php (add adjustment logging)
└── SaleService.php (new - handle sale creation logic)
```

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| FEFO performance with 1000s of batches | Slow page loads | Add database index, cache query results |
| Variant matrix UI too complex | Hard for staff to use | Start simple, add "quick add" shortcuts |
| Role-based field visibility buggy | Staff sees wrong prices | Test thoroughly with different roles |
| Batch receiving ambiguity (batch_no auto vs manual) | Duplicate batches | Clear UI: "Enter existing batch to append, new batch to create" |
| Soft delete breaks existing sales traceability | Lost data | Never truly delete, only deactivate. Keep foreign keys intact. |

---

## 🧪 Testing Strategy

**Unit Tests (Pest):**
- `BatchServiceTest.php` - FEFO ordering, deduction, multi-batch
- `SettingsServiceTest.php` - cache get/set/clear
- `SaleServiceTest.php` - sale creation, stock deduction, profit calc

**Feature Tests:**
- `Filament/ResourceAuthorizationTest.php` - role-based access
- `Inventory/BatchReceivingTest.php` - receive stock flow
- `Sales/FefoTraceabilityTest.php` - batch traceability in sale items

**Browser Tests:**
- `PosBasicFlowTest.php` - scan, add, checkout
- `Admin/ReceiveStockTest.php` - batch receiving UI

---

## 🎨 Design Considerations (Tailwind)

- Use Filament's default styling (TALL stack)
- Custom CSS only for:
  - Expiry badge colors: red (<90d), yellow (<180d), green (>180d)
  - Batch grid in receive page
  - POS cart highlighting for pharma mode
- Responsive: Works on tablet (Filament already responsive)

---

## 📚 Documentation to Create

1. `docs/filament-setup.md` - Installation, user roles, navigation
2. `docs/inventory-workflow.md` - How to receive stock, manage batches, handle expiry
3. `docs/product-management.md` - Creating products with variants, SKU strategy
4. `docs/reports.md` - Using the three report pages
5. `WEEK2_STATUS.md` - End-of-week summary (to be written)

---

## 🔄 Integration with Week 1

Week 2 **does not modify** database schema. All work is:
- Building UI layer on top of existing models
- Using existing services (Settings, Batch, Stock)
- Extending Filament resources

**No new migrations needed** unless we add:
- Audit log table (optional)
- Soft deletes already on models

---

## 🚀 Ready to Start?

**Prerequisites:**
✅ Week 1 database complete
✅ All models and services working
✅ Sample data seeded
✅ Spatie Permission configured

**Estimated Time:** 5 days × 6h = 30 hours

**Approval Required:** Yes — Confirm plan before proceeding

**When approved, I will:**
1. Install Filament (`php artisan filament:install`)
2. Start Day 1: Basic catalog resources
3. Report progress daily in `WEEK2_STATUS.md`
4. Stop after each day for review

---

**Do you approve this Week 2 plan?** (yes/no)
