<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class UsersController extends Controller
{
    private function authorizeAccess(): void
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403, 'Unauthorized.');
        }
    }

    private function getRoleLevel(string $roleName): int
    {
        return match($roleName) {
            'admin'    => 4,
            'manager'  => 3,
            'staff'    => 2,
            'customer' => 1,
            default     => 0,
        };
    }

    private function getAssignableRoleIds(): array
    {
        $sessionLevel = $this->getRoleLevel(session('role'));
        $maxLevel = session('role') === 'admin' ? 4 : $sessionLevel - 1;

        return DB::table('roles')
            ->get()
            ->filter(fn($r) => $this->getRoleLevel($r->name) <= $maxLevel)
            ->pluck('id')
            ->toArray();
    }

    public function index()
    {
        $this->authorizeAccess();

        $users = collect();
        try {
            Log::debug("=================== user list ===================");
            $users = DB::table('users')
                ->leftJoin('role_user', 'users.id', 'role_user.user_id')
                ->leftJoin('roles', 'role_user.role_id', 'roles.id')
                ->select('users.*', 'roles.name as role_name', 'roles.display_name as role_display')
                ->orderBy('users.email')
                ->get();
            Log::info("User list: " . count($users) . " records");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end user list ===================");
        }
        return view('User.index', compact('users'));
    }

    public function create()
    {
        $this->authorizeAccess();

        $roles = collect();
        try {
            Log::debug("=================== user create form ===================");
            $allRoles = DB::table('roles')->orderBy('display_name')->get();
            $allowed = $this->getAssignableRoleIds();
            $roles = $allRoles->filter(fn($r) => in_array($r->id, $allowed));
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end user create form ===================");
        }
        return view('User.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorizeAccess();

        try {
            Log::debug("=================== user create ===================");

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'is_active'  => $request->boolean('is_active', true),
                'user_type'  => 'staff',
            ]);

            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $request->role_id,
            ]);
            AuditHelper::log('CREATE', 'role_user', $user->id, 'Role assigned to user');

            Log::info("User created: {$request->email}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create user.']);
        } finally {
            Log::debug("=================== end user create ===================");
        }
        return redirect()->route('users.index');
    }

    public function edit($id)
    {
        $this->authorizeAccess();

        $user = null; $roles = collect(); $userRole = null;
        try {
            Log::debug("=================== user edit form ===================");
            $user = DB::table('users')->find($id);
            if (!$user) return redirect()->route('users.index');

            if (session('role') !== 'admin') {
                $targetRole = DB::table('role_user')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->where('role_user.user_id', $id)
                    ->value('roles.name');
                $targetLevel = $this->getRoleLevel($targetRole ?? '');
                $sessionLevel = $this->getRoleLevel(session('role'));
                if ($targetLevel >= $sessionLevel) {
                    abort(403, 'You cannot edit users with equal or higher role.');
                }
            }

            $allRoles = DB::table('roles')->orderBy('display_name')->get();
            $allowed = $this->getAssignableRoleIds();
            $roles = $allRoles->filter(fn($r) => in_array($r->id, $allowed));
            $userRole = DB::table('role_user')->where('user_id', $id)->first();
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end user edit form ===================");
        }
        return view('User.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $this->authorizeAccess();

        if (session('role') !== 'admin') {
            $targetRole = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.user_id', $id)
                ->value('roles.name');
            $targetLevel = $this->getRoleLevel($targetRole ?? '');
            $sessionLevel = $this->getRoleLevel(session('role'));
            if ($targetLevel >= $sessionLevel) {
                abort(403, 'You cannot modify users with equal or higher role.');
            }
        }

        try {
            Log::debug("=================== user update ===================");

            $updateData = [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'is_active'  => $request->boolean('is_active', true),
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            User::findOrFail($id)->update($updateData);
            DB::table('role_user')->where('user_id', $id)->update(['role_id' => $request->role_id]);
            AuditHelper::log('UPDATE', 'role_user', $id, 'Role updated for user');

            Log::info("User updated: #{$id} {$request->email}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update user.']);
        } finally {
            Log::debug("=================== end user update ===================");
        }
        return redirect()->route('users.index');
    }

    public function destroy($id)
    {
        $this->authorizeAccess();

        if ($id == session('user_id')) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        if (session('role') !== 'admin') {
            $targetRole = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.user_id', $id)
                ->value('roles.name');
            $targetLevel = $this->getRoleLevel($targetRole ?? '');
            $sessionLevel = $this->getRoleLevel(session('role'));
            if ($targetLevel >= $sessionLevel) {
                abort(403, 'You cannot delete users with equal or higher role.');
            }
        }
        try {
            Log::debug("=================== user delete ===================");
            $user = User::find($id);
            if (!$user) return redirect()->route('users.index');
            DB::table('role_user')->where('user_id', $id)->delete();
            AuditHelper::log('DELETE', 'role_user', $id, 'Role unassigned from user');
            $user->delete();
            Log::info("User deleted: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end user delete ===================");
        }
        return redirect()->route('users.index');
    }
}
