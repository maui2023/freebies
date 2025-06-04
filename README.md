# Freeblis API

Freeblis is a platform that enables vendors to give away free products to clients. Each product can be claimed via QR code until the stock runs out.

## 📚 API Documentation

See [API Documentation](#api-modules) below for available endpoints.

## 🔧 Tech Stack

- **Backend:** PHP 8.3
- **Database:** MariaDB 11
- **Server:** Nginx 1.24
- **Authentication:** JWT (JSON Web Token)
- **QR Scanning:** Unique token per product & stock

## 🎯 Use Case

Vendors list products on Freeblis. Clients can claim these products by scanning a QR code, subject to stock availability.

## 📂 API Modules

### 1. Auth Module
- `POST /register`
- `POST /login`
- `POST /logout`
- `POST /refresh-token`

### 2. User Module
- `GET /user/profile`
- `PUT /user/update`
- `POST /user/upgrade-to-vendor`

### 3. Vendor Module
- `GET /vendor/dashboard`
- `GET /vendor/products`
- `POST /vendor/product/add`
- `PUT /vendor/product/{id}/update`
- `DELETE /vendor/product/{id}/delete`

### 4. QR Claim Module
- `GET /product/{qr_code}/scan`
- `POST /product/{qr_code}/claim`

### 5. Product Module
- `GET /products`
- `GET /product/{id}`
- `GET /product/{id}/stock`

### 6. Admin Module (optional)
- `GET /admin/users`
- `PUT /admin/user/{id}/role`
- `DELETE /admin/user/{id}`

## 🔒 Authentication & Authorization

- **Public routes:** Register, login, product browsing.
- **Protected routes:** Product management, claiming, profile, dashboard.
- **Roles:** client, vendor, admin.

## 🧩 Database Tables

- **users:** username, password hash, role
- **user_profiles:** name, phone, address, dob, photo
- **products:** vendor_id, name, price, stock, category, created_at
- **claims:** user_id, product_id, claim_time, claim_token
- **qr_codes:** product_id, token, status (active/claimed)
- **sessions:** for token validation or JWT blacklist if needed

---

## 🚀 Getting Started

1. Clone the repository.
2. Install dependencies (using [Composer](https://getcomposer.org/)).
    ```
    composer require vlucas/phpdotenv
    ```
3. Copy `.env.example` to `.env` and update with your database settings:
    ```
    DB_HOST=localhost
    DB_PORT=3307
    DB_DATABASE=freeblis
    DB_USERNAME=root
    DB_PASSWORD=
    ```
4. Run migrations to set up the database.
5. Start the server.

## 📄 License

MIT
