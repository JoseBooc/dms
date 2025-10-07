<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Tenant Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h2 class="font-semibold text-xl text-gray-800">
                                {{ config('app.name', 'DMS') }} - Tenant Portal
                            </h2>
                        </div>
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="{{ route('tenant.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tenant.dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Dashboard
                            </a>
                            
                            <!-- Rent Information Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="px-3 py-2 rounded-md text-sm font-medium flex items-center {{ request()->routeIs('tenant.rent.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                                    Rent Information
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute z-10 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                                    <a href="{{ route('tenant.rent.details') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('tenant.rent.details') ? 'bg-gray-50' : '' }}">Rent Details</a>
                                    <a href="{{ route('tenant.rent.utilities') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('tenant.rent.utilities') ? 'bg-gray-50' : '' }}">Utility Details</a>
                                    <a href="{{ route('tenant.rent.room-info') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('tenant.rent.room-info') ? 'bg-gray-50' : '' }}">Room Information</a>
                                </div>
                            </div>

                            <a href="{{ route('tenant.bills.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tenant.bills.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Bills
                            </a>
                            <a href="{{ route('tenant.maintenance.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tenant.maintenance.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Maintenance
                            </a>
                            <a href="{{ route('tenant.complaints.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tenant.complaints.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                                Complaints
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <span class="text-gray-700">{{ Auth::user()->first_name ?? Auth::user()->name ?? Auth::user()->email }}</span>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-600 hover:text-gray-900 text-sm">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Alpine.js for dropdown functionality -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>