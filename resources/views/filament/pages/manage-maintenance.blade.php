<x-filament-panels::page>
    <x-filament::section heading="Mode maintenance" description="Activer/désactiver la page Coming Soon sur tous les sites">
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="maintenanceMode" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    <span class="ml-3 text-sm font-medium {{ $maintenanceMode ? 'text-danger-600' : 'text-gray-500' }}">
                        {{ $maintenanceMode ? 'Activé' : 'Désactivé' }}
                    </span>
                </label>
                @if($maintenanceMode)
                    <span class="text-xs text-danger-500">⚠️ Tous les sites publics afficheront la page de maintenance</span>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Message personnalisé</label>
                <textarea wire:model="maintenanceMessage" rows="3" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" placeholder="Message affiché sur la page de maintenance..."></textarea>
            </div>

            <div class="flex justify-end">
                <x-filament::button wire:click="save" color="primary">
                    Enregistrer
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Sites concernés" description="Tous les sites seront redirigés vers la page Coming Soon">
        <ul class="space-y-2 text-sm">
            <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-400"></span> francegems.com (Portail)</li>
            <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-400"></span> bijoux.francegems.com (Boutique Bijoux)</li>
            <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-400"></span> pierres.francegems.com (Boutique Pierres)</li>
            <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-400"></span> admin.francegems.com (Administration) — <span class="text-success-600">toujours accessible</span></li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
