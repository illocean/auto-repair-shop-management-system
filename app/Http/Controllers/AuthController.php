<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Http\Requests\RegisterRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    public function loginPage()
    {
        try {
            try {
                // All registered users (login reference table)
                $users = DB::table('users')
                    ->leftJoin('role_user', 'users.id', 'role_user.user_id')
                    ->leftJoin('roles', 'role_user.role_id', 'roles.id')
                    ->orderBy('roles.display_name')
                    ->orderBy('users.email')
                    ->select('users.*', 'roles.display_name as role_display', 'roles.name as role_name')
                    ->get();
            } catch (Throwable $e) {
                $users = collect();
            }

            $passwords = [
                'admin@system.local'   => 'admin123',
                'juan@repairshop.local' => 'password',
                'maria@repairshop.local' => 'password',
                'pedro@repairshop.local' => 'password',
                'ana@repairshop.local'  => 'password',
            ];

            return view('Auth.login', compact('users', 'passwords'));   
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withErrors(['error' => 'Something went wrong.']);
        }
    }

    public function registerPage()
    {
        try {
            $showRegisterTab = true;

            try {
                // All registered users (login reference table)
                $users = DB::table('users')
                    ->leftJoin('role_user', 'users.id', 'role_user.user_id')
                    ->leftJoin('roles', 'role_user.role_id', 'roles.id')
                    ->orderBy('roles.display_name')
                    ->orderBy('users.email')
                    ->select('users.*', 'roles.display_name as role_display', 'roles.name as role_name')
                    ->get();
            } catch (Throwable $e) {
                $users = collect();
            }

            $passwords = [
                'admin@system.local'   => 'admin123',
                'juan@repairshop.local' => 'password',
                'maria@repairshop.local' => 'password',
                'pedro@repairshop.local' => 'password',
                'ana@repairshop.local'  => 'password',
            ];

            return view('Auth.login', compact('showRegisterTab', 'users', 'passwords'));
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withErrors(['error' => 'Something went wrong.']);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return back()->withErrors(['email' => 'Invalid email or password.'])
                    ->withInput($request->only('email'));
            }

            if (!$user->is_active) {
                return back()->withErrors(['email' => 'This account is deactivated.'])
                    ->withInput($request->only('email'));
            }

            $role = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.user_id', $user->id)
                ->select('roles.name as role_name', 'roles.display_name as role_display')
                ->first();

            session([
                'user_id'    => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'role'       => $role->role_name ?? $user->user_type ?? 'customer',
                'role_name'  => $role->role_display ?? ucfirst($user->user_type ?? 'Customer'),
            ]);

            AuditHelper::logAuth('LOGIN');
            Log::info("Login: {$user->email}");

            return redirect()->route('dashboard');
        } catch (Throwable $error) {
            Log::error($error->getMessage());

            $message = 'Login failed. Please try again.';
            if (config('app.debug')) {
                $message .= ' Error: ' . $error->getMessage();
            }

            return back()->withErrors(['error' => $message])
                ->withInput($request->only('email'));
        }
    }

    public function register(RegisterRequest $request)
    {
        try {
            $exists = User::where('email', $request->email)->exists();
            if ($exists) {
                return back()->withErrors(['email' => 'An account with this email already exists.'])
                    ->withInput($request->except('password', 'password_confirmation'));
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'is_active'  => true,
                'user_type'  => 'customer',
            ]);

            $customerRole = DB::table('roles')->where('name', 'customer')->first();
            if ($customerRole) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $customerRole->id,
                ]);
                AuditHelper::log('CREATE', 'role_user', $user->id, 'Customer role assigned');
            } else {
                Log::warning("Customer role not found — new user #{$user->id} has no role assigned.");
            }

            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone ?? '',
                'address'    => $request->address ?? '',
                'user_id'    => $user->id,
            ]);

            Log::info("Customer registered: {$request->email} (customer #{$customer->id})");

            return redirect()->route('login')
                ->with('success', 'Account created successfully! Please sign in.');
        } catch (Throwable $error) {
            Log::error($error->getMessage());

            $message = 'Registration failed. Please try again.';

            if (config('app.debug')) {
                $message .= ' Error: ' . $error->getMessage();
            }

            return back()->withErrors(['error' => $message])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function logout()
    {
        try {
            AuditHelper::logAuth('LOGOUT');
            Log::info("Logout: " . (session('email') ?? 'unknown'));
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        }

        session()->flush();
        return redirect('/');
    }
}
