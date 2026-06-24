<x-filament-panels::page>
    <x-filament::section heading="Mode maintenance" description="Activer/désactiver la page Coming Soon sur tous les sites">
        <div class="space-y-6">
            <div class="flex items-center gap-6">
                <x-filament::input.wrapper label="Maintenance">
                    <select wire:model="maintenanceMode" class="block w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                        <option value="0">Désactivé</option>
                        <option value="1">Activé</option>
                    </select>
                </x-filament::input.wrapper>
                @if($maintenanceMode === '1')
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
