@if (session('user_id'))
<nav class="nav-bar">
    <div class="container-main">
        <div class="nav-header">
            <a class="nav-brand" href="{{ route('dashboard') }}">
                NO. 3. Auto Repair Shop
            </a>

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
                @endif

                @if (in_array(session('role'), ['admin', 'manager']))
                    <a class="nav-link" href="{{ route('users.index') }}">Users</a>
                @endif

                @if (session('role') === 'admin')
                    <a class="nav-link" href="{{ route('audit.index') }}">Audit</a>
                @endif

                <span class="mx-2 text-gray-300">|</span>
                <span class="text-sm text-gray-500 whitespace-nowrap">{{ session('first_name') }} {{ session('last_name') }}</span>
                <span class="text-xs text-gray-400 ml-1">({{ session('role_name') }})</span>
                <a class="btn-logout" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>

            <div class="nav-mobile-area">
                <span class="text-xs text-gray-500 hidden sm:inline">{{ session('first_name') }}</span>
                <button id="menu-button" aria-label="Toggle menu" class="nav-mobile-toggle"
                        onclick="document.getElementById('mobile-menu').classList.toggle('show')">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                  </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="nav-mobile-menu">
            <a class="nav-link-mobile" href="{{ route('dashboard') }}">Dashboard</a>

            @if (session('role') === 'customer')
                <a class="nav-link-mobile" href="{{ route('vehicles.index') }}">My Vehicles</a>
                <a class="nav-link-mobile" href="{{ route('repair-orders.index') }}">My Orders</a>
            @else
                <a class="nav-link-mobile" href="{{ route('customers.index') }}">Customers</a>
                <a class="nav-link-mobile" href="{{ route('vehicles.index') }}">Vehicles</a>
                <a class="nav-link-mobile" href="{{ route('repair-orders.index') }}">Repair Orders</a>
                <a class="nav-link-mobile" href="{{ route('service-types.index') }}">Services</a>
            @endif

            @if (in_array(session('role'), ['admin', 'manager']))
                <a class="nav-link-mobile" href="{{ route('users.index') }}">Users</a>
            @endif

            @if (session('role') === 'admin')
                <a class="nav-link-mobile" href="{{ route('audit.index') }}">Audit</a>
            @endif

            <hr class="my-2 border-gray-200">
            <span class="block px-3 py-1 text-sm text-gray-500">{{ session('first_name') }} {{ session('last_name') }} ({{ session('role_name') }})</span>
            <a class="nav-link-mobile-logout" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">Logout</a>
            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </div>
</nav>
@endif
