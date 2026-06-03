# Database & Query Walkthrough -- Auto Repair Shop Management System

> **Author:** Kim Phillip G. Andador
> **Framework:** Laravel 13.8 (PHP 8.3+) | **Database:** MySQL 8 (im_indivproject)
> **ORM:** Eloquent + Laravel Query Builder | **Frontend:** Blade + Tailwind CSS 4
> **Auth:** Session-based (bcrypt, BCRYPT_ROUNDS=12)

---

## Quick Reference

**Project:** Full-stack web application for modeling automobile repair shop workflows. Staff manage customers, vehicles, service catalogs, and repair orders. Customers register, view their vehicles, and create repair requests. Every database write is recorded in an immutable audit trail.

**Tech Stack:** Laravel 13.8 / PHP 8.3+ / MySQL 8 / Blade + Tailwind CSS 4 + Vite 8

**Database Connection (.env):**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=im_indivproject
DB_USERNAME=root
DB_PASSWORD=1234
```

**18 Tables at a Glance:**

| Group | Tables | Count |
|-------|--------|-------|
| Laravel System | users, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs | 8 |
| Core Business | customers, vehicles, service_types | 3 |
| Orders | repair_orders, repair_order_services | 2 |
| RBAC | roles, role_user, permissions, permission_role | 4 |
| Audit | audit_logs | 1 |

**Also uses database for:** cache driver (CACHE_STORE=database), queue driver (QUEUE_CONNECTION=database)

---

## 1. LOGIN FORM

Traces every database interaction across the authentication lifecycle -- from page load through login, registration, session guard, and logout.

### 1.1 Login Page -- GET /login

**File:** app/Http/Controllers/AuthController.php (lines 17-46)

The login page queries all registered users to display a login reference table (convenience for demo/testing):

```php
$users = DB::table('users')
    ->leftJoin('role_user', 'users.id', 'role_user.user_id')
    ->leftJoin('roles', 'role_user.role_id', 'roles.id')
    ->orderBy('roles.display_name')
    ->orderBy('users.email')
    ->select('users.*', 'roles.display_name as role_display', 'roles.name as role_name')
    ->get();
```

| Query Type | Tables | Purpose |
|------------|--------|---------|
| DB::table() JOIN | users, role_user, roles | Show all registered users with their roles on the login screen |

### 1.2 Login Submit -- POST /login

**File:** app/Http/Controllers/AuthController.php (lines 81-131)

**Step 1 -- User Lookup:**

```php
$user = User::where('email', $request->email)->first();
```

Eloquent query against users table. Returns the user record or null.

**Step 2 -- Password Verification:**

```php
if (!$user || !Hash::check($request->password, $user->password)) { ... }
```

Uses Laravel's Hash::check() to verify against the bcrypt hash stored in users.password. Uses BCRYPT_ROUNDS=12.

**Step 3 -- Active Account Guard:**

```php
if (!$user->is_active) {
    return back()->withErrors(['email' => 'This account is deactivated.']);
}
```

Checks the users.is_active boolean column before allowing login.

**Step 4 -- RBAC Role Lookup:**

```php
$roleData = DB::table('role_user')
    ->join('roles', 'role_user.role_id', '=', 'roles.id')
    ->where('role_user.user_id', $user->id)
    ->select('roles.name as role_name', 'roles.display_name as role_display')
    ->first();
```

| Query Type | Tables | Purpose |
|------------|--------|---------|
| DB::table() JOIN | role_user, roles | Fetch the user's role name and display name for session storage |

**Step 5 -- Session Write:**

```php
session([
    'user_id'    => $user->id,
    'first_name' => $user->first_name,
    'last_name'  => $user->last_name,
    'email'      => $user->email,
    'role'       => $roleData->role_name ?? $user->user_type ?? 'customer',
    'role_name'  => $roleData->role_display ?? ucfirst($user->user_type ?? 'Customer'),
]);
```

No database write here -- session driver is file (not database-backed).

**Step 6 -- Audit Log:**

```php
AuditHelper::logAuth('LOGIN');
// Performs DB::table('audit_logs')->insert([...action=>'LOGIN', entity_type=>'auth'...])
```

Inserts a login event into the audit_logs table with the user's ID, IP address, and user agent.

### 1.3 Registration -- POST /register

**File:** app/Http/Controllers/AuthController.php (lines 133-187)

**Step 1 -- Duplicate Guard:**

```php
$exists = User::where('email', $request->email)->exists();
```

Eloquent query checking users.email uniqueness before creating.

**Step 2 -- Create User:**

```php
$user = User::create([
    'first_name' => $request->first_name,
    'last_name'  => $request->last_name,
    'email'      => $request->email,
    'password'   => Hash::make($request->password),
    'is_active'  => true,
    'user_type'  => 'customer',
]);
```

Eloquent create() -- automatically audited by the Auditable trait. Stores bcrypt-hashed password.

**Step 3 -- Assign Customer Role:**

```php
$role = DB::table('roles')->where('name', 'customer')->first();
if ($role) {
    DB::table('role_user')->insert([
        'user_id' => $user->id,
        'role_id' => $role->id,
    ]);
    AuditHelper::log('CREATE', 'role_user', $user->id, 'Customer role assigned');
}
```

| Query Type | Tables | Purpose |
|------------|--------|---------|
| DB::table() SELECT | roles | Look up the 'customer' role ID |
| DB::table() INSERT | role_user | Assign the new user to the customer role |

**Step 4 -- Create Customer Record:**

```php
$customer = Customer::create([
    'first_name' => $request->first_name,
    'last_name'  => $request->last_name,
    'email'      => $request->email,
    'phone'      => $request->phone ?? '',
    'address'    => $request->address ?? '',
    'user_id'    => $user->id,
]);
```

Eloquent create() on customers table -- automatically audited. Links back to the users table via user_id.

### 1.4 Session Guard -- Middleware

**File:** app/Http/Middleware/SessionAuth.php (lines 11-18)

```php
public function handle(Request $request, Closure $next): Response
{
    if (!session()->has('user_id')) {
        return redirect()->route('login');
    }
    return $next($request);
}
```

Applied to all routes except login/register via Route::middleware('auth.session'). No database query -- purely session-based. Protects all CRUD routes for customers, vehicles, repair orders, service types, users, and the audit log.

### 1.5 Logout -- POST /logout

**File:** app/Http/Controllers/AuthController.php (lines 189-201)

```php
AuditHelper::logAuth('LOGOUT');
session()->flush();
return redirect('/');
```

Logs the logout event to audit_logs, then flushes all session data.

### 1.6 Auth Query Summary

| Endpoint | Query Pattern | Tables Touched | Eloquent or QB |
|----------|--------------|----------------|----------------|
| GET /login | DB::table()->leftJoin()->leftJoin() | users, role_user, roles | Query Builder |
| POST /login | User::where()->first() | users | Eloquent |
| POST /login | DB::table()->join() | role_user, roles | Query Builder |
| POST /login | AuditHelper::logAuth() -> DB::table()->insert() | audit_logs | Query Builder |
| POST /register | User::where()->exists() | users | Eloquent |
| POST /register | User::create() | users | Eloquent |
| POST /register | DB::table()->where()->first() | roles | Query Builder |
| POST /register | DB::table()->insert() | role_user | Query Builder |
| POST /register | Customer::create() | customers | Eloquent |
| POST /logout | AuditHelper::logAuth() -> DB::table()->insert() | audit_logs | Query Builder |

---

## 2. MAINTENANCE

Covers the database lifecycle -- how tables are created, seeded, altered, and maintained over time.

### 2.1 Migration Pipeline (16 files)

All migrations are in database/migrations/. They execute in timestamp order:

**Framework Migrations (created by Laravel, unmodified):**

| Timestamp | Table(s) Created | Purpose |
|-----------|-----------------|---------|
| 0001_01_01_000000 | users, password_reset_tokens, sessions | Auth infrastructure |
| 0001_01_01_000001 | cache, cache_locks | Framework cache |
| 0001_01_01_000002 | jobs, job_batches, failed_jobs | Queue infrastructure |

**Custom Business Migrations:**

| Timestamp | Table / Modification | Group |
|-----------|---------------------|-------|
| 2026_05_30_000001 | ALTER users -- add first_name, last_name, is_active, last_login | Schema |
| 2026_05_30_000002 | roles | RBAC |
| 2026_05_30_000003 | role_user (pivot) | RBAC |
| 2026_05_30_000004 | permissions | RBAC |
| 2026_05_30_000005 | permission_role (pivot) | RBAC |
| 2026_05_30_000006 | customers | Business |
| 2026_05_30_000007 | vehicles | Business |
| 2026_05_30_000008 | service_types | Business |
| 2026_05_30_000009 | repair_orders | Orders |
| 2026_05_30_000010 | repair_order_services | Orders |
| 2026_05_30_000011 | audit_logs | Audit |

**Later Alter Migrations:**

| Timestamp | Modification |
|-----------|-------------|
| 2026_05_31_000001 | ALTER users -- add user_type varchar(20) default 'staff' |
| 2026_05_31_000002 | ALTER customers -- add user_id FK -> users (nullable) |

**Migration Anatomy -- repair_orders example:**

```php
Schema::create('repair_orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->onDelete('cascade');
    $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
    $table->string('service_advisor_name', 100);
    $table->date('order_date');
    $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    // Named indexes for performance
    $table->index('customer_id', 'idx_ro_customer');
    $table->index('vehicle_id', 'idx_ro_vehicle');
    $table->index('order_date', 'idx_ro_date');
});
```

### 2.2 Seeding Strategy (8 seeders)

**File:** database/seeders/DatabaseSeeder.php

```php
public function run(): void
{
    $this->call([
        RoleSeeder::class,          // 1. Create roles first
        CustomerRoleSeeder::class,  // 2. Add customer role
        PermissionSeeder::class,    // 3. Create permission codes
        RolePermissionSeeder::class,// 4. Map permissions to roles
        UserSeeder::class,          // 5. Create staff users + assign roles
        ServiceTypeSeeder::class,   // 6. Seed service catalog
        SampleDataSeeder::class,    // 7. Demo customers + vehicles
    ]);
}
```

**What each seeder does:**

| Seeder | Method | Key Detail |
|--------|--------|------------|
| **RoleSeeder** | DB::table('roles')->insert([...]) | Creates 3 roles: admin, manager, staff |
| **CustomerRoleSeeder** | DB::table('roles')->insert(...) if missing | Adds 'customer' role (checks existence first) |
| **PermissionSeeder** | DB::table('permissions')->insert([...]) | 19 permission codes across 6 modules |
| **RolePermissionSeeder** | DB::table('permission_role')->insert([...]) | admin=all 19, manager=11, staff=5 |
| **UserSeeder** | DB::table('users')->insertGetId() + Hash::make() | 5 staff users with role assignments |
| **ServiceTypeSeeder** | DB::table('service_types')->insert([...]) | 8 service types (Oil Change, Brake Inspection, etc.) |
| **SampleDataSeeder** | DB::table('customers')->insertGetId() + DB::table('vehicles')->insert() | 3 customers, 4 vehicles |

**Hardcoded Credentials (displayed on login page for demo):**

| Email | Password | Role |
|-------|----------|------|
| admin@system.local | admin123 | Administrator |
| juan@repairshop.local | password | Manager |
| maria@repairshop.local | password | Manager |
| pedro@repairshop.local | password | Staff |
| ana@repairshop.local | password | Staff |

### 2.3 Later-Modification Pattern

When a new column is needed on an existing table, create a new migration that alters it. Example from 2026_05_31_000002_add_user_id_to_customers_table.php:

```php
Schema::table('customers', function (Blueprint $table) {
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
});
```

This pattern is used instead of rolling back and re-creating the migration -- safe for production databases with existing data.

### 2.4 Cache & Queue Tables

These four tables are managed entirely by Laravel's framework:

| Table | Config | Driver |
|-------|--------|--------|
| cache, cache_locks | CACHE_STORE=database | Database cache driver |
| jobs, job_batches, failed_jobs | QUEUE_CONNECTION=database | Database queue driver |

No manual maintenance needed -- Laravel handles read/write/expiry automatically.

### 2.5 Common Maintenance Tasks

| Task | Command / SQL |
|------|---------------|
| Full reset + reseed | php artisan migrate:fresh --seed |
| Add new permission | INSERT INTO permissions (...) + INSERT INTO permission_role (...) |
| Add new service type | INSERT INTO service_types (name, description, book_hours, rate_per_hour) VALUES (...) |
| Create new migration | php artisan make:migration add_column_to_table |
| Check table contents (diagnostic) | php check_tables.php (standalone PDO script) |

---

## 3. ROLE PERMISSION

The RBAC system uses four tables to implement role-based access control across 19 permission codes, with hierarchical enforcement in controllers.

### 3.1 RBAC Schema -- The 4 Tables

```
roles              role_user            users
+----------+       +----------+       +----------+
| id (PK)  |       | user_id  |---+   | id (PK)  |
| name     |---+   | role_id  |   |   | email    |
| display  |   +---| (compo.  |   +---| password |
+----------+   |   |  PK)     |       | is_active|
               |   +----------+       | user_type|
               |                      +----------+
               |   permission_role
               |   +-------------+
               |   | role_id     |
               +---| perm_id     |
                   | (compo. PK) |
                   +------+------+
                          |
                   +------+------+
                   | permissions |
                   | id (PK)     |
                   | code (uniq) |
                   | description |
                   | module      |
                   +-------------+
```

Both pivot tables (role_user, permission_role) use composite primary keys and cascade delete on both foreign keys.

### 3.2 The 19 Permission Codes

Seeded by PermissionSeeder (database/seeders/PermissionSeeder.php):

| Module | Permission Code | Description |
|--------|----------------|-------------|
| **customer** | customer.create | Create customer records |
| | customer.read | View customer records |
| | customer.update | Edit customer records |
| | customer.delete | Delete customer records |
| **vehicle** | vehicle.create | Create vehicle records |
| | vehicle.read | View vehicle records |
| | vehicle.update | Edit vehicle records |
| | vehicle.delete | Delete vehicle records |
| **repair_order** | repair_order.create | Create repair orders |
| | repair_order.read | View repair orders |
| | repair_order.update | Edit repair orders |
| | repair_order.delete | Delete repair orders |
| **service_type** | service_type.create | Create service type definitions |
| | service_type.read | View service type definitions |
| | service_type.update | Edit service type definitions |
| | service_type.delete | Delete service type definitions |
| **audit** | audit.view | View audit log |
| **users** | users.manage | Create and manage users |

### 3.3 Role -> Permission Mapping

Seeded by RolePermissionSeeder (database/seeders/RolePermissionSeeder.php):

**admin** -- all 19 permissions
```php
$permissions = DB::table('permissions')->pluck('id');
foreach ($permissions as $permId) {
    DB::table('permission_role')->insert([
        'role_id' => $adminRoleId,
        'permission_id' => $permId,
    ]);
}
```

**manager** -- 11 permissions (all view + create/edit on core, no deletes)
```php
$managerPerms = [
    'customer.create', 'customer.read', 'customer.update',
    'vehicle.create',  'vehicle.read',  'vehicle.update',
    'repair_order.create', 'repair_order.read', 'repair_order.update',
    'service_type.read',
    'audit.view',
];
```

**staff** -- 5 permissions (read-only + create)
```php
$staffPerms = [
    'customer.read',
    'vehicle.read',
    'repair_order.create', 'repair_order.read', 'repair_order.update',
];
```

**customer** -- not stored in permission_role table. The 'customer' role is enforced entirely in-controller via session('role') string comparison.

### 3.4 Hierarchical Access Control

The UsersController (app/Http/Controllers/UsersController.php) implements a role hierarchy for user management:

```php
private function getRoleLevel(string $role): int
{
    return match($role) {
        'admin'    => 4,
        'manager'  => 3,
        'staff'    => 2,
        'customer' => 1,
        default    => 0,
    };
}

private function authorizeAccess(): void
{
    if (!in_array(session('role'), ['admin', 'manager'])) {
        abort(403, 'Unauthorized.');
    }
}
```

**Rules enforced:**
- Only admin and manager can access the Users module
- You can only assign roles below your own level (getAssignableRoleIds())
- You cannot edit or delete users with equal or higher role level
- You cannot delete your own account

### 3.5 In-Controller Enforcement Patterns

**Route Protection -- SessionAuth middleware:**
```php
// routes/web.php
Route::middleware('auth.session')->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('repair-orders', RepairOrderController::class);
    // ...
});
```

**Role Gate -- abort(403) pattern:**
```php
if (session('role') === 'customer') {
    abort(403, 'Customers cannot create new customer records.');
}
```
Used in: CustomerController (create, store, edit, update, destroy), VehicleController (create, store, edit, update, destroy), RepairOrderController (edit, update, destroy).

**Data Scoping -- Row-level access:**
```php
if (session('role') === 'customer') {
    $customer = DB::table('customers')
        ->where('user_id', session('user_id'))->first();
    if ($customer) {
        $query->where('repair_orders.customer_id', $customer->id);
    }
}
```
Used in: CustomerController (index), RepairOrderController (index, show), DashboardController (customerDashboard).

### 3.6 Key RBAC Queries

| Operation | Query | File:Line |
|-----------|-------|-----------|
| **Login role lookup** | DB::table('role_user')->join('roles')->where('user_id', $id)->first() | AuthController:101-105 |
| **User list + roles** | DB::table('users')->leftJoin('role_user')->leftJoin('roles')->get() | UsersController:53-58 |
| **Registration role assign** | DB::table('roles')->where('name', 'customer')->first() + DB::table('role_user')->insert([...]) | AuthController:151-156 |
| **Assignable roles** | DB::table('roles')->get()->filter(fn($r) => $r->level <= maxLevel) | UsersController:39-44 |
| **Create user + assign role** | User::create() + DB::table('role_user')->insert([...]) | UsersController:93-105 |
| **Update user role** | DB::table('role_user')->where('user_id', $id)->update(['role_id' => ...]) | UsersController:183 |
| **Check target role level** | DB::table('role_user')->join('roles')->where('user_id', $id)->value('roles.name') | UsersController:129-132 |

---

## 4. AUDIT LOGS

The audit trail system combines an Eloquent trait (automatic) with a centralized helper (manual) to record every data change with before/after snapshots.

### 4.1 Audit Schema

**Table:** audit_logs

| Column | Type | Purpose |
|--------|------|---------|
| id | BIGINT PK | Auto-increment |
| user_id | BIGINT FK -> users (nullable, nullOnDelete) | Who performed the action |
| username | VARCHAR | Denormalized snapshot of full name at time of action |
| action | VARCHAR | CREATE, UPDATE, DELETE, LOGIN, LOGOUT |
| entity_type | VARCHAR | Table name: customers, repair_orders, auth, etc. |
| entity_id | VARCHAR (nullable) | Primary key of the affected row |
| summary | TEXT (nullable) | Human-readable description |
| old_values | JSON (nullable) | Row state before the change |
| new_values | JSON (nullable) | Row state after the change |
| ip_address | VARCHAR | Request IP |
| user_agent | TEXT | Request user agent |
| created_at | TIMESTAMP | When the action occurred |

**Indexes (for query performance):**

```php
$table->index(['entity_type', 'entity_id'], 'idx_audit_entity');
$table->index('user_id', 'idx_audit_user');
$table->index('created_at', 'idx_audit_created');
$table->index('action', 'idx_audit_action');
```

### 4.2 Architecture

The audit system has two layers:

```
                   Layer 1: Trait
          (app/Models/Traits/Auditable.php)

          bootAuditable() -> hooks Eloquent model events
            +----------------+   +----------------+
            |  created()     |   |  updated()     |
            |  ->auditCreate |   |  ->auditUpdate |
            +-------+--------+   +-------+--------+
                    v                    v
            +----------------------------------------+
            |        AuditHelper::log()              |
            |     (app/Helpers/AuditHelper.php)      |
            |                                        |
            |     DB::table('audit_logs')            |
            |       ->insert([...])                  |
            +----------------------------------------+
                    |         |
                    v         v
            Layer 2: Manual calls (in Controllers)
            AuditHelper::logAuth('LOGIN')
            AuditHelper::log('CREATE', 'role_user', ...)
```

**Layer 1 -- The Auditable Trait** (app/Models/Traits/Auditable.php):

```php
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) { $model->auditCreate(); });
        static::updated(function (Model $model) { $model->auditUpdate(); });
        static::deleted(function (Model $model) { $model->auditDelete(); });
    }

    protected function auditUpdate(): void
    {
        $dirty = $this->getDirty();
        // Skip noise-only changes
        if ($this->getAuditIgnoreAttributes($dirty)) { return; }

        AuditHelper::log(
            'UPDATE',
            $this->getAuditEntityType(),  // defaults to table name
            $this->getKey(),
            class_basename($this) . ' updated',
            $this->getOriginalAuditValues(),  // OLD state
            $this->getAuditValues()            // NEW state
        );
    }
}
```

**Noise filter** -- ignores fields that change on every request:
```php
protected function getAuditIgnoreAttributes(array $dirty): bool
{
    $ignored = ['updated_at', 'created_at', 'last_login'];
    return count(array_diff(array_keys($dirty), $ignored)) === 0;
}
```

**Layer 2 -- The AuditHelper** (app/Helpers/AuditHelper.php):

```php
class AuditHelper
{
    public static function log(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?string $summary = null,
        $oldValues = null,
        $newValues = null
    ): void {
        try {
            $userId = session('user_id');
            $fullName = trim((session('first_name') ?? '') . ' ' . (session('last_name') ?? ''))
                ?: 'system';

            DB::table('audit_logs')->insert([
                'user_id'     => $userId,
                'username'    => $fullName,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => (string) $entityId,
                'summary'     => $summary,
                'old_values'  => $oldValues ? json_encode($oldValues) : null,
                'new_values'  => $newValues ? json_encode($newValues) : null,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'created_at'  => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('AuditHelper failed: ' . $e->getMessage());
        }
    }

    public static function logAuth(string $action): void
    {
        self::log($action, 'auth', null, "User {$action}");
    }
}
```

### 4.3 What Gets Audited

| Trigger | Mechanism | Entity Types |
|---------|-----------|--------------|
| Model created() | Auto (trait) | users, customers, vehicles, repair_orders, repair_order_services, service_types |
| Model updated() | Auto (trait) | Same 6 models (filtered: ignores updated_at/created_at/last_login) |
| Model deleted() | Auto (trait) | Same 6 models |
| Login | Manual (logAuth()) | auth |
| Logout | Manual (logAuth()) | auth |
| Role assigned | Manual (log()) | role_user |
| Role updated | Manual (log()) | role_user |
| Role unassigned | Manual (log()) | role_user |
| Service added to order | Manual (log()) | repair_order_services |
| Service removed from order | Manual (log()) | repair_order_services |

**Not audited:** The Role model intentionally does not use the Auditable trait (RBAC changes are infrequent and managed by seeders only).

### 4.4 Querying the Audit Log

**File:** app/Http/Controllers/AuditController.php

The only paginated query in the application (50 items per page):

```php
public function index(Request $request)
{
    $query = DB::table('audit_logs');

    // Dynamic filters
    if ($request->filled('action'))       $query->where('action', $request->action);
    if ($request->filled('entity_type'))  $query->where('entity_type', $request->entity_type);
    if ($request->filled('date_from'))    $query->whereDate('created_at', '>=', $request->date_from);
    if ($request->filled('date_to'))      $query->whereDate('created_at', '<=', $request->date_to);
    if ($request->filled('user_id'))      $query->where('audit_logs.user_id', $request->user_id);

    $logs = $query
        ->leftJoin('users', 'audit_logs.user_id', 'users.id')
        ->select('audit_logs.*', 'users.first_name', 'users.last_name')
        ->orderBy('audit_logs.created_at', 'desc')
        ->paginate(50);

    // Also fetches users list for the filter dropdown
    $users = DB::table('users')->orderBy('email')->get();

    return view('Audit.index', compact('logs', 'users'));
}
```

**Available filters:**
| Filter | Column | SQL |
|--------|--------|-----|
| Action | audit_logs.action | WHERE action = ? |
| Entity Type | audit_logs.entity_type | WHERE entity_type = ? |
| Date From | audit_logs.created_at | WHERE DATE(created_at) >= ? |
| Date To | audit_logs.created_at | WHERE DATE(created_at) <= ? |
| User | audit_logs.user_id | WHERE user_id = ? |

### 4.5 Audit Query Summary

| Operation | Query | File:Line |
|-----------|-------|-----------|
| **Log action** | DB::table('audit_logs')->insert([...]) | AuditHelper:26-38 |
| **Log auth event** | AuditHelper::log($action, 'auth', ...) -> same insert | AuditHelper:49-52 |
| **List with filters** | DB::table('audit_logs')->leftJoin('users')->where([...])->orderBy()->paginate(50) | AuditController:18-40 |
| **Model created** | Automatic via bootAuditable() -> auditCreate() -> AuditHelper::log() | Auditable:12-14, 25-35 |
| **Model updated** | Automatic via bootAuditable() -> auditUpdate() -> AuditHelper::log() (noise-filtered) | Auditable:16-18, 37-52 |
| **Model deleted** | Automatic via bootAuditable() -> auditDelete() -> AuditHelper::log() | Auditable:20-22, 54-64 |

---

## File Index

### Configuration

| File | Purpose |
|------|---------|
| .env | Database credentials (mysql) |
| config/database.php | Connection definitions (mysql primary, sqlite fallback) |
| config/cache.php | Cache driver: database (tables: cache, cache_locks) |
| config/queue.php | Queue driver: database (tables: jobs, job_batches, failed_jobs) |

### Migrations (database/migrations/)

| File | Table(s) |
|------|----------|
| 0001_01_01_000000_create_users_table.php | users, password_reset_tokens, sessions |
| 0001_01_01_000001_create_cache_table.php | cache, cache_locks |
| 0001_01_01_000002_create_jobs_table.php | jobs, job_batches, failed_jobs |
| 2026_05_30_000001_add_columns_to_users_table.php | ALTER users (first_name, last_name, is_active, last_login) |
| 2026_05_30_000002_create_roles_table.php | roles |
| 2026_05_30_000003_create_role_user_table.php | role_user |
| 2026_05_30_000004_create_permissions_table.php | permissions |
| 2026_05_30_000005_create_permission_role_table.php | permission_role |
| 2026_05_30_000006_create_customers_table.php | customers |
| 2026_05_30_000007_create_vehicles_table.php | vehicles |
| 2026_05_30_000008_create_service_types_table.php | service_types |
| 2026_05_30_000009_create_repair_orders_table.php | repair_orders |
| 2026_05_30_000010_create_repair_order_services_table.php | repair_order_services |
| 2026_05_30_000011_create_audit_logs_table.php | audit_logs |
| 2026_05_31_000001_add_user_type_to_users_table.php | ALTER users (user_type) |
| 2026_05_31_000002_add_user_id_to_customers_table.php | ALTER customers (user_id FK) |

### Models (app/Models/)

| File | Table | Auditable? | Key Relationships |
|------|-------|------------|-------------------|
| User.php | users | Yes | roles(), customer() |
| Customer.php | customers | Yes | user(), vehicles(), repairOrders() |
| Vehicle.php | vehicles | Yes | customer(), repairOrders() |
| RepairOrder.php | repair_orders | Yes | customer(), vehicle(), services() |
| RepairOrderService.php | repair_order_services | Yes | repairOrder(), serviceType() |
| ServiceType.php | service_types | Yes | repairOrderServices() |
| Role.php | roles | No | users() |

### Controllers (app/Http/Controllers/)

| File | Primary Query Style | Featured In |
|------|---------------------|-------------|
| AuthController.php | Mixed (Eloquent + QB) | 1. LOGIN FORM |
| DashboardController.php | Query Builder only | 2. MAINTENANCE |
| CustomerController.php | Mixed | 3. ROLE PERMISSION (scoping) |
| VehicleController.php | Mixed | - |
| RepairOrderController.php | Mixed | - |
| ServiceTypeController.php | Mixed | 2. MAINTENANCE (seeding) |
| UsersController.php | Mixed | 3. ROLE PERMISSION (hierarchy) |
| AuditController.php | Query Builder only | 4. AUDIT LOGS |

### Seeders (database/seeders/)

RoleSeeder, CustomerRoleSeeder, PermissionSeeder, RolePermissionSeeder, UserSeeder, ServiceTypeSeeder, SampleDataSeeder, DatabaseSeeder

### Other Key Files

| File | Purpose |
|------|---------|
| app/Models/Traits/Auditable.php | Auto-audit trait (hooks Eloquent events) |
| app/Helpers/AuditHelper.php | Centralized audit INSERT logic |
| app/Http/Middleware/SessionAuth.php | Session guard (protects all authenticated routes) |
| routes/web.php | Route definitions (2 guest + 14 authenticated) |
| check_tables.php | Standalone PDO diagnostic script |
| database/factories/UserFactory.php | Test data factory (for PHPUnit) |
