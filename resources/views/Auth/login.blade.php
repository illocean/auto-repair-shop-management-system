@extends('Template.main')
@section('title', 'Welcome')
@section('content')
    <div class="">
        <div class="login-center">
            <div class="login-card">

                @if (session('success'))
                    <div class="alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->has('error'))
                    <div class="alert-error">
                        @foreach ($errors->get('error') as $msg)
                            <p>{{ $msg }}</p>
                        @endforeach
                    </div>
                @endif

                @php $hasRegError = $errors->has('first_name') || $errors->has('last_name') || $errors->has('password') || $errors->has('phone') || $errors->has('address') || $errors->has('email') || $errors->has('error'); @endphp

                <div class="tab-bar">
                    <button id="tab-login-btn" class="tab-btn active" onclick="switchTab('login')">Sign In</button>
                    <button id="tab-register-btn" class="tab-btn" onclick="switchTab('register')">Create Account</button>
                </div>

                <div id="form-login" class="tab-pane active">
                    <form method="POST" action="{{ route('login.post') }}" class="card-form">
                        @csrf
                        <div>
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-input{{ $errors->has('email') ? ' input-error' : '' }}" required autofocus>
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                class="form-input{{ $errors->has('password') ? ' input-error' : '' }}" required>
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn-primary-full btn-submit">Sign In</button>
                    </form>
                </div>

                <div id="form-register" class="tab-pane">
                    <form method="POST" action="{{ route('register.post') }}" class="card-form">
                        @csrf
                        <div class="grid-2">
                            <div>
                                <label for="reg_first_name" class="form-label">First Name</label>
                                <input type="text" name="first_name" id="reg_first_name" value="{{ old('first_name') }}"
                                    class="form-input{{ $errors->has('first_name') ? ' input-error' : '' }}" required>
                                @error('first_name')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="reg_last_name" class="form-label">Last Name</label>
                                <input type="text" name="last_name" id="reg_last_name" value="{{ old('last_name') }}"
                                    class="form-input{{ $errors->has('last_name') ? ' input-error' : '' }}" required>
                                @error('last_name')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label for="reg_email" class="form-label">Email</label>
                            <input type="email" name="email" id="reg_email" value="{{ old('email') }}"
                                class="form-input{{ $errors->has('email') ? ' input-error' : '' }}" required>
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid-2">
                            <div>
                                <label for="reg_phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="reg_phone" value="{{ old('phone') }}"
                                    class="form-input{{ $errors->has('phone') ? ' input-error' : '' }}">
                                @error('phone')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="reg_address" class="form-label">Address</label>
                                <input type="text" name="address" id="reg_address" value="{{ old('address') }}"
                                    class="form-input{{ $errors->has('address') ? ' input-error' : '' }}">
                                @error('address')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label for="reg_password" class="form-label">Password</label>
                            <input type="password" name="password" id="reg_password"
                                class="form-input{{ $errors->has('password') ? ' input-error' : '' }}" required>
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="reg_password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="reg_password_confirmation"
                                class="form-input" required>
                        </div>
                        <button type="submit" class="btn-primary-full btn-submit">Create Account</button>
                    </form>
                </div>
            </div>

            {{-- Registered Users --}}
            <div class="h-screen flex items-center justify-center text-center">
                <div class="demo-table-wrap">
                    <table class="demo-table">
                        <thead>
                            <a> Demo accounts </a>
                            <tr class="demo-thead-row">
                                <th class="demo-th">Role</th>
                                <th class="demo-th">Email</th>
                                <th class="demo-th">Password</th>
                            </tr>
                        </thead>
                        <tbody class="demo-tbody">
                            @forelse ($users as $acct)
                                @php
                                    $roleColorClass = match ($acct->role_name) {
                                        'admin' => 'demo-role-admin',
                                        'manager' => 'demo-role-manager',
                                        'staff' => 'demo-role-staff',
                                        'customer' => 'demo-role-customer',
                                        default => 'demo-role-default',
                                    };
                                    $displayRole = $acct->role_display ?: 'Unassigned';
                                @endphp
                                <tr class="demo-tr">
                                    <td class="demo-td demo-td-role {{ $roleColorClass }}">{{ $displayRole }}</td>
                                    <td class="demo-td demo-td-email">{{ $acct->email }}</td>
                                    <td class="demo-td demo-td-pass">{{ $passwords[$acct->email] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="demo-empty">No users yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        var showReg = {{ (isset($showRegisterTab) && $showRegisterTab) || $hasRegError ? 'true' : 'false' }};

        function switchTab(tab) {
            document.getElementById('form-login').classList.toggle('active', tab === 'login');
            document.getElementById('form-register').classList.toggle('active', tab === 'register');
            document.getElementById('tab-login-btn').classList.toggle('active', tab === 'login');
            document.getElementById('tab-register-btn').classList.toggle('active', tab === 'register');
        }
        if (showReg) document.addEventListener('DOMContentLoaded', function() {
            switchTab('register');
        });
    </script>
@endsection
