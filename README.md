# Auto Repair Shop Management System

> **Information Management — Individual Project No. 3**
> A full-stack **Laravel 13** + **MySQL** web application that models the real-world workflow of an automobile repair shop, built around a published case study.

<p align="left">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-13.8-FF2D20?logo=laravel&logoColor=white">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white">
  <img alt="MySQL" src="https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white">
  <img alt="Tailwind" src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white">
  <img alt="Vite" src="https://img.shields.io/badge/Vite-8-646CFF?logo=vite&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/license-MIT-green">
</p>

---

## 📋 Table of Contents
- [Case Study (Source of Truth)](#case-study-source-of-truth)
- [Course Requirements Met](#course-requirements-met)
- [Features](#features)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
  - [Core Business Entities](#core-business-entities)
  - [RBAC Tables](#rbac-tables)
  - [Audit Log](#audit-log)
- [ERD & Design Diagrams](#erd--design-diagrams)
- [Business Rules Enforced](#business-rules-enforced)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Default Seeded Accounts](#default-seeded-accounts)
- [Permission Matrix](#permission-matrix)
- [Project Documents](#project-documents)
- [License](#license)

---

## Case Study (Source of Truth)

> *"You are designing a database for an automobile repair shop. When a customer brings in a vehicle, a service advisor will write up a repair order. This order will identify the customer and the vehicle, along with the date of service and the name of the advisor. A vehicle might need several different types of service in a single visit. These could include oil change, lubrication, rotate tires, and so on. Each type of service is billed at a pre-determined number of hours work, regardless of the actual time spent by the technician. Each type of service also has a flat book rate of dollars-per-hour that is charged."*

The original brief is preserved at [`docs/instruction.txt`](docs/instruction.txt). The full project paper lives at [`docs/IM-INDIVIDUAL_PROJECT_NO3.pdf`](docs/IM-INDIVIDUAL%20PROJECT%20NO3.pdf).

---

## Course Requirements Met

| Requirement | Status | Implementation |
|-------------|:-----:|----------------|
| **5.1** MySQL database | ✅ | `config/database.php` → `mysql` driver, DB `im_indivproject` |
| **5.1.1** Integrated with PHP-Laravel | ✅ | Laravel 13.8 framework |
| **5.1.1** Any frontend may be used | ✅ | Blade + Tailwind CSS 4 via Vite |
| **5.1.1** Everything must use MySQL | ✅ | All queries routed through the MySQL connection |
| **5.2.a** Login form with authentication | ✅ | `AuthController` (session guard, bcrypt) |
| **5.2.b** Landing page with CRUD maintenance modules | ✅ | `customers`, `vehicles`, `service_types`, `repair_orders`, `users` — full resource controllers |
| **5.2.c** Additional features | ✅ | Audit trail, role-based dashboard, status machine, multi-service orders |
| **5.2.d** Audit trail | ✅ | `audit_logs` table + `Auditable` Eloquent trait + `AuditHelper` |
| **5.2.e** Role permissions | ✅ | 4 roles (admin / manager / staff / customer) + `permissions` + `permission_role` |

---

## Features

### Core
- 🔐 **Session-based authentication** — login, register, logout, password hashing (bcrypt)
- 👥 **Role-based access control** — 4 roles with hierarchical permission enforcement
- 🚗 **Repair Order workflow** — multi-service orders with auto-calculated `line_total = book_hours × rate_per_hour`
- 💰 **Service catalog** — pre-priced service types with `book_hours` and `rate_per_hour`
- 👤 **Customer & Vehicle management** — full CRUD with cascading relationships
- 👨‍💼 **Employee / User management** — admin/manager only, role-hierarchy enforced
- 📊 **Role-aware dashboard** — distinct UI for staff vs. customer logins
- 📝 **Real-time audit trail** — every model event (created/updated/deleted) is logged automatically
- 🎨 **Status row tinting** — visual row coloring by repair-order status
- 🔄 **Status machine** — `open → in_progress → completed` with `cancelled` allowed from any state

### User Type Distinction
- **`staff`** users (admin, manager, staff roles) — created by admins/managers, manage the shop
- **`customer`** users — register publicly, see only their own vehicles & orders

---

## System Architecture

The full architecture diagram (DrawIO source) lives at [`docs/ERD MODELS/architecture.drawio`](docs/ERD%20MODELS/architecture.drawio).

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTP
       ▼
┌─────────────────┐
│  routes/web.php │
└────────┬────────┘
         │
         ▼
┌────────────────────────┐
│ Session Auth Middleware│
└────────┬───────────────┘
         │
         ▼
┌──────────────────────────────────────────────────────────┐
│  Controllers (app/Http/Controllers)                      │
│  ┌────────┬───────────┬────────┬─────────┬──────────┐    │
│  │  Auth  │ Dashboard │Customer│ Vehicle │Repair    │    │
│  ├────────┼───────────┼────────┼─────────┼──────────┤    │
│  │ServiceType│ Users  │ Audit │         │  Order   │    │
│  └────────┴───────────┴────────┴─────────┴──────────┘    │
└──────┬──────────────────────────────────┬────────────────┘
       │ compact() data                   │ DB::table() queries
       ▼                                  ▼
┌─────────────────────────┐    ┌────────────────────────────┐
│  Blade Views            │    │  MySQL — im_indivproject   │
│  (resources/views)      │    │  ┌──────────────────────┐  │
│  Template · Auth        │    │  │ RBAC                 │  │
│  Dashboard · Customer   │    │  │ users · roles        │  │
│  Vehicle · RepairOrder  │    │  │ role_user            │  │
│  ServiceType · User     │    │  │ permissions          │  │
│  Audit                  │    │  │ permission_role      │  │
└─────────────────────────┘    │  ├──────────────────────┤  │
                               │  │ Core Business        │  │
                               │  │ customers · vehicles │  │
                               │  │ service_types        │  │
                               │  │ repair_orders        │  │
                               │  │ repair_order_services│  │
                               │  ├──────────────────────┤  │
                               │  │ Audit                │  │
                               │  │ audit_logs           │  │
                               │  └──────────────────────┘  │
                               └────────────────────────────┘
```

**Stack footer:** Laravel 13 · PHP 8.3+ · Tailwind CSS v4 · MySQL · Session Auth · Query Builder + Eloquent

---

## Database Schema

**18 tables total** (6 Laravel system + 12 custom). Full schema diagram: [`docs/ERD MODELS/database-schema.drawio`](docs/ERD%20MODELS/database-schema.drawio) and [`docs/ERD MODELS/PhysicalModel erd.png`](docs/ERD%20MODELS/PhysicalModel%20erd.png).

### Legend
- `*` = NOT NULL
- `PK` = Primary Key · `FK` = Foreign Key · `UQ` = Unique
- 🟣 Purple = RBAC · 🔵 Blue = Core business · 🟡 Yellow = Orders/Services · 🔴 Red = Audit

### Core Business Entities

#### `customers`
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| user_id | FK → users | nullable, links customer login to their record (nullOnDelete) |
| first_name * | varchar | |
| last_name * | varchar | |
| email | UQ | |
| phone | varchar | |
| address | text | |
| created_at / updated_at | timestamps | |

#### `vehicles`
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| customer_id * | FK → customers | one customer → many vehicles |
| make * | varchar | |
| model * | varchar | |
| year | year | |
| license_plate | varchar | |
| vin | varchar | |
| created_at / updated_at | timestamps | |

#### `service_types` (catalog)
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| name * | varchar | e.g. "Oil Change", "Tire Rotation" |
| description | text | |
| book_hours * | decimal | pre-determined hours per the case study |
| rate_per_hour * | decimal | flat book rate, $ per hour |
| created_at / updated_at | timestamps | |

#### `repair_orders` (core business entity)
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| customer_id * | FK → customers | the customer bringing the vehicle |
| vehicle_id * | FK → vehicles | the vehicle being serviced |
| service_advisor_name * | varchar | free-text per case-study wording |
| order_date * | date | |
| status | enum | `open` · `in_progress` · `completed` · `cancelled` |
| notes | text | |
| created_by | FK → users | |
| updated_by | FK → users | |
| created_at / updated_at | timestamps | |

#### `repair_order_services` (line items)
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| repair_order_id * | FK → repair_orders | |
| service_type_id | FK → service_types | |
| book_hours * | decimal | **auto-populated** from service_types |
| rate_per_hour * | decimal | **auto-populated** from service_types |
| line_total * | decimal | **auto-calculated** = book_hours × rate_per_hour |
| created_at / updated_at | timestamps | |

### RBAC Tables

#### `users`
`id (PK) · first_name * · last_name * · email (UQ) * · password * · is_active (default 1) · last_login · remember_token · user_type (enum: customer/staff) · timestamps`

#### `roles`
`id (PK) · name (UQ) * · display_name * · timestamps`
Seeded: **admin**, **manager**, **staff**, **customer**

#### `role_user` (pivot)
`user_id (PK,FK) · role_id (PK,FK)`

#### `permissions`
`id (PK) · code (UQ) * · description · module * · timestamps`

#### `permission_role` (pivot)
`permission_id (PK,FK) · role_id (PK,FK)`

### Audit Log

#### `audit_logs`
`id (PK) · user_id (FK) · username · action * · entity_type * · entity_id · summary · old_values (JSON) · new_values (JSON) · ip_address · user_agent · created_at`

Logs every `created` / `updated` / `deleted` model event via the `Auditable` trait, plus login/logout events via `AuditHelper::logAuth()`.

---

## ERD & Design Diagrams

All diagrams are committed in three forms: **drawio source** (editable), **PNG** (preview), and **MySQL Workbench** (where applicable).

| Diagram | DrawIO Source | PNG |
|---------|---------------|-----|
| Conceptual ERD | [`docs/ERD MODELS/architecture.drawio`](docs/ERD%20MODELS/architecture.drawio) | [`docs/ERD MODELS/conceptual model erd.png`](docs/ERD%20MODELS/conceptual%20model%20erd.png) |
| Logical ERD | — | [`docs/ERD MODELS/logical model erd.png`](docs/ERD%20MODELS/logical%20model%20erd.png) |
| Database Schema | [`docs/ERD MODELS/database-schema.drawio`](docs/ERD%20MODELS/database-schema.drawio) | — |
| Physical Model | [`docs/ERD MODELS/PhysicalModel.mwb`](docs/ERD%20MODELS/PhysicalModel.mwb) | [`docs/ERD MODELS/PhysicalModel erd.png`](docs/ERD%20MODELS/PhysicalModel%20erd.png) |
| High-level Design | [`docs/auto-repair-shop-db-design.drawio`](docs/auto-repair-shop-db-design.drawio) | [`docs/drawio.png`](docs/drawio.png), [`docs/logical.png`](docs/logical.png) |
| DrawIO XML | [`docs/drawio.xml`](docs/drawio.xml) | — |

---

## Business Rules Enforced

Per the case study wording, the following rules are baked into the controllers and `Auditable` trait:

### Pricing Flow
1. Service advisor creates a repair order
2. Selects customer → selects that customer's vehicle
3. Adds services from the `service_types` catalog
4. Each service line auto-fills from the catalog:
   - `book_hours` ← `service_types.book_hours`
   - `rate_per_hour` ← `service_types.rate_per_hour`
   - `line_total` ← `book_hours × rate_per_hour` (auto-calculated)

### Status Machine
- **Valid transitions:** `open → in_progress → completed`
- `cancelled` is allowed from any non-completed state
- No backwards transitions (e.g., `completed → open` is **forbidden**)
- Enforced in `RepairOrderController@update`

### Advisor Assignment
- When a staff user creates a repair order, `service_advisor_name` is auto-filled from `session('first_name') . ' ' . session('last_name')`
- The advisor is the logged-in user who wrote the order

### User Type Distinction
- Public registration → `user_type = 'customer'`, role `'customer'`
- Staff accounts → created ONLY by admins/managers via `/users`
- `customers.user_id` (nullable FK) links a customer's login to their customer record

### Vehicle ↔ Customer Constraint
- A vehicle must belong to the customer selected on the repair order
- Enforced in the create/edit form (dropdown is filtered to selected customer's vehicles)

### Audit Logging
- Every `created` / `updated` / `deleted` Eloquent event is auto-logged via the `Auditable` trait on: `Customer`, `Vehicle`, `ServiceType`, `RepairOrder`, `RepairOrderService`, `User`
- Updates log `old_values` (JSON) and `new_values` (JSON, changed attrs only)
- Login/logout events are logged via `AuditHelper::logAuth()`

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Framework** | Laravel 13.8 |
| **Language** | PHP 8.3+ |
| **Database** | MySQL 8 (DB: `im_indivproject`) |
| **Auth** | Laravel session guard (bcrypt, `BCRYPT_ROUNDS=12`) |
| **ORM** | Eloquent (new models) + raw Query Builder (`DB::table()`) for legacy controllers |
| **Frontend** | Blade templates · Tailwind CSS 4 · vanilla JS |
| **Build tool** | Vite 8 |
| **Testing** | PHPUnit 12 |
| **Code style** | Laravel Pint |
| **Diagrams** | DrawIO · MySQL Workbench (.mwb) |

---

## Project Structure

```
.
├── app/
│   ├── Helpers/
│   │   └── AuditHelper.php                # Centralized auth-event logger
│   ├── Http/
│   │   ├── Controllers/                   # 8 controllers
│   │   │   ├── AuditController.php
│   │   │   ├── AuthController.php
│   │   │   ├── Controller.php
│   │   │   ├── CustomerController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── RepairOrderController.php
│   │   │   ├── ServiceTypeController.php
│   │   │   ├── UsersController.php
│   │   │   └── VehicleController.php
│   │   ├── Middleware/                    # auth.session
│   │   └── Requests/                      # 6 form-request validators
│   │       ├── CustomerRequest.php
│   │       ├── RegisterRequest.php
│   │       ├── ServiceTypeRequest.php
│   │       ├── StoreUserRequest.php
│   │       ├── UpdateUserRequest.php
│   │       └── VehicleRequest.php
│   ├── Models/
│   │   ├── Traits/Auditable.php           # Auto-logs model events
│   │   ├── Customer.php
│   │   ├── RepairOrder.php
│   │   ├── RepairOrderService.php
│   │   ├── Role.php
│   │   ├── ServiceType.php
│   │   ├── User.php
│   │   └── Vehicle.php
│   └── Providers/
├── bootstrap/
├── config/                                # 10 config files
├── database/
│   ├── factories/
│   ├── migrations/                        # 16 migrations
│   └── seeders/                           # 8 seeders
├── docs/
│   ├── ERD MODELS/                        # Conceptual / Logical / Physical / Schema
│   ├── auto-repair-shop-db-design.drawio
│   ├── drawio.png
│   ├── drawio.xml
│   ├── IM-INDIVIDUAL_PROJECT_NO3.docx
│   ├── IM-INDIVIDUAL_PROJECT_NO3.pdf
│   ├── instruction.txt                    # Case study + course brief
│   └── logical.png
├── public/
│   ├── build/                             # Compiled Vite assets
│   └── index.php
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/                             # 24 Blade templates
│       ├── Audit/
│       ├── Auth/
│       ├── Customer/
│       ├── Dashboard/
│       ├── RepairOrder/
│       ├── ServiceType/
│       ├── Template/                      # Layout, header, footer
│       ├── User/
│       └── Vehicle/
├── routes/
│   ├── console.php
│   └── web.php                            # 2 guest + 11 auth routes
├── storage/
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Getting Started

### Prerequisites
- PHP 8.3 or higher
- Composer 2
- Node.js 20+ and npm
- MySQL 8 (or compatible)

### Installation

```bash
# 1. Clone
git clone https://github.com/pandadoor/auto-repair-shop-management-system.git
cd auto-repair-shop-management-system

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Copy environment file & generate app key
cp .env.example .env
php artisan key:generate

# 5. Configure your MySQL connection in .env, then:
php artisan migrate --seed

# 6. Build frontend assets
npm run build

# 7. Serve
php artisan serve          # → http://127.0.0.1:8000
# In another terminal, for HMR:
npm run dev
```

### Database Configuration

The default `.env` expects:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=im_indivproject
DB_USERNAME=root
DB_PASSWORD=1234
```

---

## Default Seeded Accounts

| Role | Email | Password | Capabilities |
|------|-------|----------|--------------|
| **Admin** | `admin@example.com` | `password` | Full access |
| **Manager** | `manager@example.com` | `password` | CRUD except user deletion; assign staff/manager |
| **Staff** | `staff@example.com` | `password` | Service advisor: create/read/update customers, vehicles, orders |
| **Customer** | `customer@example.com` | `password` | Read own vehicles and orders |

> ⚠️ **Change all default credentials before deploying to production.**

---

## Permission Matrix

| Action | Admin | Manager | Staff | Customer |
|--------|:-----:|:-------:|:-----:|:--------:|
| Manage users (CRUD) | ✅ | ✅ (no admin role) | ❌ | ❌ |
| Assign admin role | ✅ | ❌ | ❌ | ❌ |
| Manage customers | ✅ | ✅ | ✅ | self only |
| Manage vehicles | ✅ | ✅ | ✅ | self only |
| Manage service types | ✅ | ✅ | read | ❌ |
| Create repair orders | ✅ | ✅ | ✅ | ❌ |
| View audit log | ✅ | ✅ | ❌ | ❌ |
| Public registration | — | — | — | ✅ (self) |

---

## Project Documents

| Document | File |
|----------|------|
| Course brief (case study + requirements) | [`docs/instruction.txt`](docs/instruction.txt) |
| Full project paper (PDF) | [`docs/IM-INDIVIDUAL_PROJECT_NO3.pdf`](docs/IM-INDIVIDUAL%20PROJECT%20NO3.pdf) |
| Full project paper (DOCX) | [`docs/IM-INDIVIDUAL_PROJECT_NO3.docx`](docs/IM-INDIVIDUAL%20PROJECT%20NO3.docx) |
| Database design (DrawIO) | [`docs/auto-repair-shop-db-design.drawio`](docs/auto-repair-shop-db-design.drawio) |
| Architecture diagram (DrawIO) | [`docs/ERD MODELS/architecture.drawio`](docs/ERD%20MODELS/architecture.drawio) |
| Database schema (DrawIO) | [`docs/ERD MODELS/database-schema.drawio`](docs/ERD%20MODELS/database-schema.drawio) |
| Physical model (MySQL Workbench) | [`docs/ERD MODELS/PhysicalModel.mwb`](docs/ERD%20MODELS/PhysicalModel.mwb) |
| Conceptual ERD | [`docs/ERD MODELS/conceptual model erd.png`](docs/ERD%20MODELS/conceptual%20model%20erd.png) |
| Logical ERD | [`docs/ERD MODELS/logical model erd.png`](docs/ERD%20MODELS/logical%20model%20erd.png) |
| Physical ERD | [`docs/ERD MODELS/PhysicalModel erd.png`](docs/ERD%20MODELS/PhysicalModel%20erd.png) |
| High-level DB design | [`docs/drawio.png`](docs/drawio.png) |
| Logical view | [`docs/logical.png`](docs/logical.png) |
| DrawIO XML | [`docs/drawio.xml`](docs/drawio.xml) |

---

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT). The Laravel framework is also MIT-licensed.

---

## Acknowledgments

- **Laravel** — Taylor Otwell and contributors
- **Tailwind CSS** — for the utility-first design system
- **DrawIO** — for the ERD and architecture diagrams
- **Information Management** course — for the case study brief
