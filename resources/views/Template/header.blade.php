@if (session('user_id'))
<nav class="nav-bar">
    <div class="container-main">
        <div class="nav-header">
            <a class="nav-brand" href="{{ route('dashboard') }}">Auto Repair Shop</a>

            {{-- Desktop nav --}}
            <div class="nav-desktop">
                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>

                @if (session('role') === 'customer')
                    <a class="nav-link" href="{{ route('vehicles.index') }}">My Vehicles</a>
                    <a class="nav-link" href="{{ route('repair-orders.index') }}">My Orders</a>
                @else
                    <a class="nav-link" href="{{ route('customers.index') }}">Customers</a>
                    <a class="nav-link" href="{{ route('vehicles.index') }}">Vehicles</a>
                    <a class="nav-link" href="{{ route('repair-orders.index') }}">Orders</a>
                    <a class="nav-link" href="{{ route('service-types.index') }}">Services</a>
                    <a class="nav-link" href="{{ route('appointments.calendar') }}">Appointments</a>
                    <a class="nav-link" href="{{ route('supplies.index') }}">Supplies</a>
                @endif

                @if (in_array(session('role'), ['admin', 'manager']))
                    <a class="nav-link" href="{{ route('users.index') }}">Users</a>
                @endif

                @if (session('role') === 'admin')
                    <a class="nav-link" href="{{ route('audit.index') }}">Audit</a>
                @endif

                <div class="nav-divider"></div>

                <span class="nav-user-name">{{ session('first_name') }} {{ session('last_name') }}</span>
                <span class="nav-user-role">({{ session('role_name') }})</span>
                <a class="nav-logout" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>

            {{-- Mobile area --}}
            <div class="nav-mobile-area">
                <span class="text-xs text-slate-400">{{ session('first_name') }}</span>
                <button id="menu-toggle" aria-label="Toggle menu" class="nav-mobile-toggle" aria-expanded="false">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="nav-mobile-menu">
            <a class="nav-link-mobile" href="{{ route('dashboard') }}">Dashboard</a>

            @if (session('role') === 'customer')
                <a class="nav-link-mobile" href="{{ route('vehicles.index') }}">My Vehicles</a>
                <a class="nav-link-mobile" href="{{ route('repair-orders.index') }}">My Orders</a>
            @else
                <a class="nav-link-mobile" href="{{ route('customers.index') }}">Customers</a>
                <a class="nav-link-mobile" href="{{ route('vehicles.index') }}">Vehicles</a>
                <a class="nav-link-mobile" href="{{ route('repair-orders.index') }}">Orders</a>
                <a class="nav-link-mobile" href="{{ route('service-types.index') }}">Services</a>
                <a class="nav-link-mobile" href="{{ route('appointments.calendar') }}">Appointments</a>
                <a class="nav-link-mobile" href="{{ route('supplies.index') }}">Supplies</a>
            @endif

            @if (in_array(session('role'), ['admin', 'manager']))
                <a class="nav-link-mobile" href="{{ route('users.index') }}">Users</a>
            @endif

            @if (session('role') === 'admin')
                <a class="nav-link-mobile" href="{{ route('audit.index') }}">Audit</a>
            @endif

            <div class="nav-mobile-footer">
                <span class="block px-3 text-xs text-slate-500">{{ session('first_name') }} {{ session('last_name') }} ({{ session('role_name') }})</span>
                <a class="block px-3 pt-1 text-xs text-slate-500 hover:text-slate-300" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">Log out</a>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('mobile-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            const expanded = toggle.getAttribute('aria-expanded') === 'true' ? false : true;
            toggle.setAttribute('aria-expanded', expanded);
            menu.classList.toggle('open', expanded);
        });
    }
});
</script>
@endif
