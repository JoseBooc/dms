@if (filled($brand = config('filament.brand')))
    <div @class([
        'filament-brand flex items-center space-x-3',
        'dark:text-white' => config('filament.dark_mode'),
    ])>
        <img src="{{ asset('media/logo.png') }}" alt="ABH-DMS Logo" class="h-8 w-8">
        <span class="text-xl font-bold tracking-tight">{{ $brand }}</span>
    </div>
@endif
