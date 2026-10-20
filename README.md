# POS Backend

A small Laravel backend for a point-of-sale system.

## Features

- REST API for inventory, sales, customers, and invoices
- Laravel Sanctum auth support
- CSV import and reporting helpers
- Public storefront API (landing page + direct online orders)
- Product variants for clothing/jewelry (color, size, material, gemstone, custom attributes)

## Quick start

1. Copy `.env.example` to `.env`
2. Run `composer install`
3. Run `php artisan migrate --seed`
4. Start with `php artisan serve`

## Notes

This repository contains the backend API only.

## New storefront endpoints

### Public (no auth)

- `GET /api/public/stores/{slug}`: get public landing + published products + variants.
- `POST /api/public/stores/{slug}/orders`: create an online order from storefront products.

### Private (auth + store middleware)

- `GET /api/storefront/settings`
- `PUT /api/storefront/settings`
- `GET /api/storefront/products`
- `POST /api/storefront/products`
- `PUT /api/storefront/products/{id}`
- `DELETE /api/storefront/products/{id}`
- `GET /api/storefront/orders`
- `GET /api/storefront/orders/{id}`
- `GET /api/products/{product}/variants`
- `POST /api/products/{product}/variants`
- `PUT /api/products/{product}/variants/{variant}`
- `DELETE /api/products/{product}/variants/{variant}`

## Frontend integration docs

- [Storefront Frontend Guide](./docs/storefront-frontend-guide.md)
