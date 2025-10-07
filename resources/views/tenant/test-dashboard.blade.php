<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Portal Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">🎉 Tenant Portal Working!</h1>
                    <p class="text-lg text-gray-600 mb-6">Welcome to your tenant dashboard, {{ auth()->user()->name }}!</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <a href="{{ route('tenant.bills') }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg border border-blue-200">
                            <h3 class="font-semibold text-blue-900">📄 My Bills</h3>
                            <p class="text-blue-700 text-sm">View your billing history</p>
                        </a>
                        
                        <a href="{{ route('tenant.rent.details') }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg border border-green-200">
                            <h3 class="font-semibold text-green-900">💰 Rent Details</h3>
                            <p class="text-green-700 text-sm">Current rent information</p>
                        </a>
                        
                        <a href="{{ route('tenant.rent.utilities') }}" class="bg-yellow-50 hover:bg-yellow-100 p-4 rounded-lg border border-yellow-200">
                            <h3 class="font-semibold text-yellow-900">⚡ Utilities</h3>
                            <p class="text-yellow-700 text-sm">Utility readings & consumption</p>
                        </a>
                    </div>
                    
                    <div class="border-t pt-4">
                        <h2 class="text-xl font-semibold mb-3">Rent Information Menu:</h2>
                        <div class="space-y-2">
                            <a href="{{ route('tenant.rent.details') }}" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded border">
                                📋 Rent Details - Current rent amount, bill status, due dates
                            </a>
                            <a href="{{ route('tenant.rent.utilities') }}" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded border">
                                ⚡ Utility Details - Latest utility readings and consumption
                            </a>
                            <a href="{{ route('tenant.rent.room-info') }}" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded border">
                                🏠 Room Information - Room details, roommates, guidelines
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>