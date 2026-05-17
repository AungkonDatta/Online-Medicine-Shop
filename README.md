# Online Medicine Shop — Group 8
**Course:** Web Technologies | **Topic:** Online Medicine Shop

## Project Structure

```
online_medicine_shop/
├── index.php          ← Front controller / router
├── config.php         ← DB connection + auto-seed admin
├── models.php         ← All database functions (procedural mysqli)
├── controllers.php    ← All request logic (no OOP)
├── style.css          ← Complete stylesheet
├── database.sql       ← Run ONCE in phpMyAdmin
├── uploads/
│   ├── medicines/     ← Medicine images (auto-created)
│   └── profiles/      ← Profile pictures (auto-created)
└── views/
    ├── _navbar.php          ← Customer navbar partial
    ├── _admin_navbar.php    ← Admin navbar partial
    ├── login.php
    ├── register.php
    ├── home.php             ← Browse medicines (AJAX search + filter)
    ├── medicine_detail.php
    ├── cart.php             ← Cart (AJAX update/remove)
    ├── checkout.php         ← 3-step: address → invoice → payment
    ├── order_success.php
    ├── my_orders.php
    ├── order_detail.php     ← Shared by customer + admin
    ├── profile.php
    ├── admin_dashboard.php  ← Stats + recent orders
    ├── admin_medicines.php  ← Full CRUD + AJAX search
    ├── admin_categories.php ← Category CRUD (liquid/solid)
    ├── admin_customers.php  ← Customer list + delete + AJAX search
    └── admin_orders.php     ← Accept/reject + AJAX search
```

## Setup Instructions

### 1. Import the Database
- Open **phpMyAdmin**
- Import `database.sql`
- This creates the database `online_medicine_shop` and all tables with sample data

### 2. Place Project Folder
- Copy the `online_medicine_shop/` folder into your XAMPP/WAMP `htdocs/` directory

### 3. Configure DB (if needed)
- Open `config.php`
- Update credentials if your MySQL uses a different user/password:
  ```php
  $conn = mysqli_connect('localhost', 'root', '', 'online_medicine_shop');
  ```

### 4. Run the App
- Start Apache + MySQL in XAMPP
- Visit: `http://localhost/online_medicine_shop/`

## Default Credentials

| Role  | Email                   | Password |
|-------|-------------------------|----------|
| Admin | admin@medicine.com      | admin123 |

Customers register via the Register page.

## Features

### Admin
- Dashboard with live stats (medicines, customers, orders, pending)
- Full CRUD for medicines (with image upload)
- Category management with liquid/solid type segmentation
- Customer management (view + delete)
- Order management — accept or reject pending orders
- View full order details
- AJAX live search on all tables

### Customer
- Browse all medicines (public, no login needed)
- Search + filter by vendor and type (liquid/solid) via AJAX
- Medicine detail page
- Add to Cart (AJAX, with stock validation)
- Cart management: increase qty (+), decrease qty (−), remove (all AJAX)
- 3-step checkout: shipping address → invoice review → payment method
- Payment methods: Credit Card, bKash, Nagad, Bank Transfer, Cash on Delivery
- Order confirmation page with full invoice
- My Orders history
- Order detail view
- Profile management with picture upload

## Technical Requirements Met
- PHP procedural style — **no OOP**
- MVC-like structure: `models.php`, `controllers.php`, `views/`
- `password_hash()` / `password_verify()` for all passwords
- `mysqli` prepared statements on every query — no raw SQL concatenation
- Server-side validation on every form with inline error messages
- AJAX search endpoints for all admin tables and customer browse
- AJAX cart operations (add, update quantity, remove)
- Role-based access control (admin / customer gates)
- XSS protection via `htmlspecialchars()` on all output

## Group 8 Team
| Student ID  | Task                                          |
|-------------|-----------------------------------------------|
| 23-51903-2  | Auth, Profile, Home, Medicine Browse, Search  |
| 23-51882-2  | Admin — Medicines, Categories, Customers, Orders |
| 23-51293-1  | Customer — Cart, Checkout, Invoice, Payment, Orders |
