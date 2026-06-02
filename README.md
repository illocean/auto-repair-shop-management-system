# Auto Repair Shop Management System

> **Information Management — Individual Project No. 3**
> A full-stack **Laravel 13** + **MySQL** web application that models the real-world workflow of an automobile repair shop, built around a published case study.

---

## Table of Contents
- [Case Study (Source of Truth)](#case-study-source-of-truth)
- [Course Requirements Met](#course-requirements-met)
- [Features](#features)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [ERD and Design Diagrams](#erd-and-design-diagrams)
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

The original brief is preserved at `docs/instruction.txt`. The original brief has been preserved verbatim in the full project paper at `docs/IM-INDIVIDUAL_PROJECT_NO3.pdf`.

---

## Course Requirements Met

| Requirement | Status | Implementation |
|-------------|:---:|----------------|
| 5.1 — MySQL database | Done | `config/database.php` uses `mysql` driver, DB `im_indivproject` |
| 5.1.1 — Integrated with PHP-Laravel | Done | Laravel 13.8 framework |
| 5.1.1 — Any frontend may be used | Done | Blade + Tailwind CSS 4 via Vite |
| 5.1.1 — Everything must use MySQL | Done | All queries routed through the MySQL connection |
| 5.2.a — Login form with authentication | Done | `AuthController` (session guard, bcrypt) |
| 5.2.b — CRUD maintenance modules | Done | `customers`, `vehicles`, `service_types`, `repair_orders`, `users` — full resource controllers |
| 5.2.c — Additional features | Done | Audit trail, role-based dashboard, status machine, multi-service orders |
| 5.2.d — Audit trail | Done | `audit_logs` table + `Auditable` Eloquent trait + `AuditHelper` |
| 5.2.e — Role permissions | Done | 4 roles (admin, manager, staff, customer) + `permissions` + `permission_role` |

---

## Features

- Session-based authentication (login, register, logout, bcrypt password hashing)
- Role-based access control with 4 roles and hierarchical permission enforcement
- Repair Order workflow with multi-service orders and auto-calculated `line_total = book_hours * rate_per_hour`
- Service catalog with pre-priced service types (`book_hours`, `rate_per_hour`)
- Customer and Vehicle management with full CRUD and cascading relationships
- Employee and User management, restricted to admin/manager with role-hierarchy enforcement
- Role-aware dashboard with distinct UI for staff and customer logins
- Real-time audit trail — every model event (created, updated, deleted) is auto-logged
- Status machine enforcing `open -> in_progress -> completed` transitions with `cancelled` allowed from any non-completed state
- User type distinction: staff users (admin, manager, staff roles) created by admins/managers, customer users register publicly

---

## System Architecture

The full architecture diagram (DrawIO source) is at `docs/ERD MODELS/architecture.drawio`.

```
Browser
  -> HTTP
routes/web.php
  -> Session Auth Middleware
  -> Controllers (Auth, Dashboard, Customer, Vehicle, RepairOrder, ServiceType, Users, Audit)
       |                              |
       | compact() data               | DB::table() queries
       v                              v
Blade Views                      MySQL - im_indivproject
  Template, Auth, Dashboard       RBAC:          users, roles, role_user,
  Customer, Vehicle, RepairOrder                permissions, permission_role
  ServiceType, User, Audit        Core Business: customers, vehicles,
                                   service_types, repair_orders,
                                   repair_order_services
                                 Audit:          audit_logs
```

Stack: Laravel 13, PHP 8.3+, Tailwind CSS v4, MySQL, Session Auth, Query Builder + Eloquent

---

## Database Schema

18 tables total (6 Laravel system + 12 custom). Full schema diagram: `docs/ERD MODELS/database-schema.drawio` and `docs/ERD MODELS/PhysicalModel erd.png`.

### Legend
- `*` = NOT NULL
- `PK` = Primary Key, `FK` = Foreign Key, `UQ` = Unique
- Purple = RBAC, Blue = Core business, Yellow = Orders/Services, Red = Audit

### Core Business Entities

#### customers
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| user_id | FK -> users | nullable, links customer login to their record (nullOnDelete) |
| first_name * | varchar | |
| last_name * | varchar | |
| email | UQ | |
| phone | varchar | |
| address | text | |
| created_at, updated_at | timestamps | |

#### vehicles
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| customer_id * | FK -> customers | one customer -> many vehicles |
| make * | varchar | |
| model * | varchar | |
| year | year | |
| license_plate | varchar | |
| vin | varchar | |
| created_at, updated_at | timestamps | |

#### service_types (catalog)
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| name * | varchar | e.g. "Oil Change", "Tire Rotation" |
| description | text | |
| book_hours * | decimal | pre-determined hours per the case study |
| rate_per_hour * | decimal | flat book rate, dollars per hour |
| created_at, updated_at | timestamps | |

#### repair_orders (core business entity)
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| customer_id * | FK -> customers | the customer bringing the vehicle |
| vehicle_id * | FK -> vehicles | the vehicle being serviced |
| service_advisor_name * | varchar | free-text per case-study wording |
| order_date * | date | |
| status | enum | `open`, `in_progress`, `completed`, `cancelled` |
| notes | text | |
| created_by | FK -> users | |
| updated_by | FK -> users | |
| created_at, updated_at | timestamps | |

#### repair_order_services (line items)
| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| repair_order_id * | FK -> repair_orders | |
| service_type_id | FK -> service_types | |
| book_hours * | decimal | auto-populated from service_types |
| rate_per_hour * | decimal | auto-populated from service_types |
| line_total * | decimal | auto-calculated as book_hours * rate_per_hour |
| created_at, updated_at | timestamps | |

### RBAC Tables

#### users
`id (PK), first_name *, last_name *, email (UQ) *, password *, is_active (default 1), last_login, remember_token, user_type (enum: customer/staff), timestamps`

#### roles
`id (PK), name (UQ) *, display_name *, timestamps`
Seeded: admin, manager, staff, customer

#### role_user (pivot)
`user_id (PK,FK), role_id (PK,FK)`

#### permissions
`id (PK), code (UQ) *, description, module *, timestamps`

#### permission_role (pivot)
`permission_id (PK,FK), role_id (PK,FK)`

### Audit Log

#### audit_logs
`id (PK), user_id (FK), username, action *, entity_type *, entity_id, summary, old_values (JSON), new_values (JSON), ip_address, user_agent, created_at`

Logs every `created`, `updated`, `deleted` model event via the `Auditable` trait, plus login/logout events via `AuditHelper::logAuth()`.

---

## ERD and Design Diagrams

| Diagram | Source | Preview |
|---------|--------|---------|
| Conceptual ERD | `docs/ERD MODELS/architecture.drawio` | `docs/ERD MODELS/conceptual model erd.png` |
| Logical ERD | — | `docs/ERD MODELS/logical model erd.png` |
| Database Schema | `docs/ERD MODELS/database-schema.drawio` | — |
| Physical Model (MySQL Workbench) | `docs/ERD MODELS/PhysicalModel.mwb` | `docs/ERD MODELS/PhysicalModel erd.png` |
| High-level DB design | `docs/auto-repair-shop-db-design.drawio` | `docs/drawio.png`, `docs/logical.png` |
| DrawIO XML | `docs/drawio.xml` | — |

---

## Business Rules Enforced

### Pricing Flow
1. Service advisor creates a repair order.
2. Selects the customer, then that customer's vehicle.
3. Adds services from the `service_types` catalog.
4. Each service line auto-fills from the catalog:
   - `book_hours` from `service_types.book_hours`
   - `rate_per_hour` from `service_types.rate_per_hour`
   - `line_total` as `book_hours * rate_per_hour`

### Status Machine
- Valid transitions: `open -> in_progress -> completed`
- `cancelled` is allowed from any non-completed state
- Backwards transitions (e.g. `completed -> open`) are forbidden
- Enforced in `RepairOrderController@update`

### Advisor Assignment
- When a staff user creates a repair order, `service_advisor_name` auto-fills from `session('first_name') . ' ' . session('last_name')`

### User Type Distinction
- Public registration creates a `user_type = 'customer'` user with the `customer` role
- Staff accounts are created only by admins/managers via `/users`
- `customers.user_id` (nullable FK) links a customer's login to their customer record

### Vehicle-Customer Constraint
- A vehicle must belong to the customer selected on the repair order
- Enforced in the create/edit form: the vehicle dropdown is filtered to the selected customer's vehicles

### Audit Logging
- Every `created`, `updated`, `deleted` Eloquent event is auto-logged via the `Auditable` trait on: `Customer`, `Vehicle`, `ServiceType`, `RepairOrder`, `RepairOrderService`, `User`
- Updates record `old_values` (JSON) and `new_values` (JSON, changed attributes only)
- Login/logout events are recorded via `AuditHelper::logAuth()`

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Framework | Laravel 13.8 |
| Language | PHP 8.3+ |
| Database | MySQL 8 (database name `im_indivproject`) |
| Authentication | Laravel session guard (bcrypt, `BCRYPT_ROUNDS=12`) |
| ORM | Eloquent (models) + raw Query Builder (`DB::table()`) for legacy controllers |
| Frontend | Blade templates, Tailwind CSS 4, vanilla JS |
| Build tool | Vite 8 |
| Testing | PHPUnit 12 |
| Code style | Laravel Pint |
| Diagrams | DrawIO, MySQL Workbench (`.mwb`) |

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
│   ├── ERD MODELS/                        # Conceptual, Logical, Physical, Schema
│   ├── auto-repair-shop-db-design.drawio
│   ├── drawio.png
│   ├── drawio.xml
│   ├── IM-INDIVIDUAL_PROJECT_NO3.docx
│   ├── IM-INDIVIDUAL_PROJECT_NO3.pdf
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
- MySQL 8 or compatible

### Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd auto-repair-shop-management-system

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Copy environment file and generate an app key
cp .env.example .env
php artisan key:generate

# 5. Configure your MySQL connection in .env, then run migrations and seeders
php artisan migrate --seed

# 6. Build frontend assets
npm run build

# 7. Serve the application
php artisan serve          # http://127.0.0.1:8000

# In another terminal, for HMR during development:
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
| Admin | admin@example.com | password | Full access |
| Manager | manager@example.com | password | CRUD except user deletion; can assign staff/manager roles |
| Staff | staff@example.com | password | Service advisor: create/read/update customers, vehicles, orders |
| Customer | customer@example.com | password | Read own vehicles and orders |

Change all default credentials before deploying to production.

---

## Permission Matrix

| Action | Admin | Manager | Staff | Customer |
|--------|:-----:|:-------:|:-----:|:--------:|
| Manage users (CRUD) | Yes | Yes (cannot assign admin) | No | No |
| Assign admin role | Yes | No | No | No |
| Manage customers | Yes | Yes | Yes | self only |
| Manage vehicles | Yes | Yes | Yes | self only |
| Manage service types | Yes | Yes | read | No |
| Create repair orders | Yes | Yes | Yes | No |
| View audit log | Yes | Yes | No | No |
| Public registration | — | — | — | Yes (self) |

---

## Project Documents

| Document | Path |
|----------|------|
| Full project paper (PDF) | `docs/IM-INDIVIDUAL_PROJECT_NO3.pdf` |
| Full project paper (DOCX) | `docs/IM-INDIVIDUAL_PROJECT_NO3.docx` |
| Database design (DrawIO) | `docs/auto-repair-shop-db-design.drawio` |
| Architecture diagram (DrawIO) | `docs/ERD MODELS/architecture.drawio` |
| Database schema (DrawIO) | `docs/ERD MODELS/database-schema.drawio` |
| Physical model (MySQL Workbench) | `docs/ERD MODELS/PhysicalModel.mwb` |
| Conceptual ERD (PNG) | `docs/ERD MODELS/conceptual model erd.png` |
| Logical ERD (PNG) | `docs/ERD MODELS/logical model erd.png` |
| Physical ERD (PNG) | `docs/ERD MODELS/PhysicalModel erd.png` |
| High-level DB design (PNG) | `docs/drawio.png` |
| Logical view (PNG) | `docs/logical.png` |
| DrawIO XML | `docs/drawio.xml` |

---

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT). The Laravel framework is also MIT-licensed.
