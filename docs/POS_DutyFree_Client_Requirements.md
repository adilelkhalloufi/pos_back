# POS DutyFree — Client Requirements & Gap Analysis

Date: 2026-03-04  
Project: `pos_back`

## 1) Goal

Translate client analysis into a clear delivery scope: what already exists, what is missing, and what should be added next in backend/API.

## 2) Assumptions (from client notes)

- “annulation command” = cancel/void sale order after creation.
- “recette fentaire” is interpreted as **recette financière / cash summary**.
- “cassia” is interpreted as **caisse / cashier** filtering.
- “annexe” is interpreted as **category/family grouping** (unless business defines separate annex entities).

## 3) What Already Exists (confirmed in current backend)

### 3.1 Core modules available

- Products CRUD (`/products`) with current fields: `name`, `reference`, `codebar`, `price`, `stock_min`, `stock_max`, `category_id`, active/archive.
- Sales orders CRUD (`/orders`) with payments (`/addPaymentToOrder/{id}`), invoice conversion (`/updateToInvoice/{id}`), and date filtering support.
- Purchase orders with approve/cancel.
- Inventory stack already present: transfers, adjustments, inventories (`/store/*`).
- Suppliers CRUD exists.
- Payment methods (`/paid_methods`) exists.
- Basic dashboard analytics exists (`/dashboard`) and caisse list exists (`/caisse`) with date filtering.
- Store data export endpoint exists (`/export/data`).

### 3.2 Useful foundation already present

- Generic date range filtering trait (`date_start`, `date_end`) reusable for all report endpoints.
- Stock movement service with in/out movement tracking.

## 4) Missing / Incomplete vs Client Request

## 4.1 Product master data (high priority)

Client asks product creation with:

- `supplier_code` (code fournisseur)
- `name`
- `pv` (sale price 1)
- `pa` (purchase price / cost)
- `pv2` (sale price 2)
- `stockable` (yes/no)
- `stock_min`, `stock_max`
- `impression` (print routing flag/profile)
- `components list` (composants/BOM)
- `unit` (unité)
- `category`

Current gap:

- No explicit `supplier_code`, `pa`, `pv2`, `stockable`, print routing metadata.
- No units table/relationship.
- No product component/BOM structure.

## 4.2 Sales lifecycle (high priority)

Client asks:

- Receipt/cash summary support.
- Ability to cancel/void sale order.
- Print order exists already.
- Print list of orders between two dates.

Current gap:

- No sale cancellation endpoint/service for `OrderSale` (purchase cancel exists, sale cancel not found).
- No dedicated “print list between dates” API payload (can be derived from order listing but needs explicit printable dataset/format).

## 4.3 Reports (“états”) with date range for all (high priority)

Client requires all with `date_start/date_end`:

1. Sales by article and family.
2. Sales by annexe (interpreted as category grouping and totals).
3. Sales by article/family by cashier (`caissier`).
4. End-of-day state (`état fin journée`).
5. Sales journal with **Margin HT** and **Margin Taux %**.
6. Print all commands with **price 1** and **price 2**.

Current gap:

- No dedicated reporting controllers/services for these exact outputs.
- Margin journal needs reliable cost source (`pa` or stock movement average cost policy).

## 4.4 Application settings / POS parameters (high priority)

Client asks settings for:

- Print mapping: which article prints on which printer/list.
- Number of copies allowed in POS.
- Secondary display (affichage secondaire) with COM port or network and coordinates/sizing (`x`, `y`, `width`, `height`).
- Payment modes.
- Application currency/devise.
- PassportReader integration toggle/config.

Current gap:

- `settings` table currently has numbering prefixes, currency, header/footer/company only.
- Missing printer profiles/routes, copy limits, secondary display device config, PassportReader config.

## 4.5 New features (medium-high priority)

Client asks:

- Import product data from provider/supplier.
- Price change module.

Current gap:

- No provider import workflow (file/API mapping + validation + preview + apply).
- No explicit price change batch/history/audit feature.

## 5) What Should Be Added (Delivery Backlog)

## Phase 1 — Core Data & Sales Control

1. Extend product schema/API:
   - Add: `supplier_code`, `price_buy`(PA), `price_sell_1`(PV), `price_sell_2`(PV2), `is_stockable`, `print_profile_id`.
   - Keep backward compatibility for existing `price` (map to `price_sell_1` during transition).
2. Add units module:
   - `units` table + relation from product.
3. Add product components/BOM module:
   - `product_components` table (parent product, component product, qty).
4. Add sale cancellation feature:
   - Endpoint: `PUT /orders/{id}/cancel`.
   - Rules: status validation, stock reversal for stockable products, payment reversal strategy, audit trail.

## Phase 2 — Reports & Printing

5. Build reporting endpoints (all date-range enabled):
   - `/reports/sales-by-item-family`
   - `/reports/sales-by-annexe`
   - `/reports/sales-by-item-family-cashier`
   - `/reports/end-of-day`
   - `/reports/sales-journal-margin`
   - `/reports/commands-print-list`
6. Add printable order list endpoint for period:
   - `/orders/print-list?date_start=&date_end=`
7. Ensure report outputs include both `price_sell_1` and `price_sell_2` where required.

## Phase 3 — POS Parameterization

8. Extend settings/config model:
   - Printer routing per product/category.
   - `max_print_copies`.
   - Secondary display config (`enabled`, `connection_type`, `com_port`, `x`, `y`, `width`, `height`).
   - PassportReader config (`enabled`, provider, connection params).
9. Add settings endpoints/resources and validation.

## Phase 4 — Imports & Price Change

10. Supplier/provider import module:
   - CSV/XLSX import, field mapping, dry-run validation, error report, commit import.
11. Price change module:
   - Batch update by filter (supplier/category/list), effective date, change reason, audit log/history.

## 6) Data Model Additions (minimum)

- `products` new columns: `supplier_code`, `price_buy`, `price_sell_1`, `price_sell_2`, `is_stockable`, `unit_id`, `print_profile_id`.
- `units` table.
- `product_components` table.
- `print_profiles` (or printer routing table).
- `settings` extension for POS device/print limits/passport reader.
- `price_change_logs` table.
- Optional: `imports` + `import_rows` for traceability.

## 7) Acceptance Criteria (key)

1. User can create/update product with PA/PV/PV2, stockable, min/max, supplier code, unit, print profile.
2. Sale cancellation reverses stock and keeps financial traceability.
3. Every requested report works with date range and export/print-ready response.
4. Margin journal returns:
   - `margin_ht = total_ht - cost_ht`
   - `margin_rate = margin_ht / total_ht * 100`
5. POS settings control print copies and device parameters without code change.
6. Provider import supports validation before commit.
7. Price changes are traceable (who, when, old/new values).

## 8) Suggested Priority Order

1. Product fields + pricing model + stockable + units.
2. Sale cancellation.
3. Required reports with date range.
4. POS print/display/passport settings.
5. Provider import.
6. Price change module with audit.

## 9) Clarifications Needed from Client (before implementation)

1. Exact meaning of “annexe” (category vs physical annex/store section).
2. Exact meaning of “recette fentaire” (financial closing? shift closing?).
3. Margin formula source for cost (`PA`, weighted average, FIFO?).
4. Should cancellation be allowed after payment/invoice, and what accounting rule applies?
5. Printer routing granularity: by article, family, or both.
6. PassportReader device protocol/provider and expected data fields.
