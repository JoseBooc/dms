@props([
    'title' => null,
    'width' => 'md',
])

<x-filament::layouts.base :title="$title">
    <link rel="stylesheet" href="{{ asset('css/auth-background.css') }}">
    
    <div class="filament-login-page auth-bg-dormitory flex items-center justify-center min-h-screen py-12 relative">
        
        <!-- Pattern Overlay -->
        <div class="absolute inset-0 auth-pattern-overlay"></div>
        
        <div @class([
            'w-screen px-6 -mt-16 space-y-8 md:mt-0 md:px-2 relative z-10',
            match($width) {
                'xs' => 'max-w-xs',
                'sm' => 'max-w-sm',
                'md' => 'max-w-md',
                'lg' => 'max-w-lg',
                'xl' => 'max-w-xl',
                '2xl' => 'max-w-2xl',
                '3xl' => 'max-w-3xl',
                '4xl' => 'max-w-4xl',
                '5xl' => 'max-w-5xl',
                '6xl' => 'max-w-6xl',
                '7xl' => 'max-w-7xl',
                default => $width,
            },
        ])>
            <!-- Enhanced Branding Header -->
            <div class="text-center mb-8 space-y-3">
                <div class="flex items-center justify-center space-x-4">
                    <img src="{{ asset('media/logo.png') }}" alt="ABH-DMS Logo" class="h-16 w-16 md:h-20 md:w-20">
                    <h1 class="text-4xl md:text-5xl font-bold text-white drop-shadow-2xl">
                        ABH-DMS
                    </h1>
                </div>
                <p class="text-xl text-orange-100 font-medium tracking-wide drop-shadow-lg">
                    Secure • Efficient • Reliable
                </p>
            </div>

            <div @class([
                'p-8 space-y-6 bg-white border border-gray-200 shadow-2xl rounded-2xl relative',
                'dark:bg-gray-900 dark:border-gray-700 dark:text-white' => config('filament.dark_mode'),
            ])>
                @if (filled($title))
                    <h2 class="text-3xl font-bold tracking-tight text-center text-gray-900 mb-2">
                        {{ $title }}
                    </h2>
                    <p class="text-center text-gray-700 mb-6 font-medium">Welcome back! Please sign in to your account</p>
                @endif

                <div {{ $attributes->class(['text-gray-900']) }}>
                    {{ $slot }}
                </div>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-400 font-light">
                        Image by redgreystock on Freepik
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preload the background image for better performance -->
    <link rel="preload" href="{{ asset('media/dormitory-background.jpg') }}" as="image">
    
    @livewire('notifications')
</x-filament::layouts.base>
