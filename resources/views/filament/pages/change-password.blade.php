<x-filament::page>
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-6">
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <div class="space-y-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Change Password</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Update your account password to keep your account secure. Make sure to use a strong password that you haven't used elsewhere.
                            </p>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <form wire:submit.prevent="submit" class="space-y-6">
                            {{ $this->form }}
                            
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <x-filament::button 
                                    color="gray" 
                                    tag="a" 
                                    :href="$this->getCancelButtonUrlProperty()"
                                    size="sm"
                                >
                                    Cancel
                                </x-filament::button>
                                
                                <x-filament::button 
                                    type="submit" 
                                    color="primary"
                                    size="sm"
                                >
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-s-key class="w-4 h-4" />
                                        <span>Update Password</span>
                                    </div>
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
