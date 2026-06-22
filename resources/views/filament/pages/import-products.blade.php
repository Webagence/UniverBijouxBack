<x-filament-panels::page>
    <div class="space-y-8">

        {{-- Download Template --}}
        <x-filament::section heading="1. Télécharger le modèle" description="Fichier Excel vierge avec tous les champs nécessaires">
            <div class="flex items-center gap-4">
                <x-filament::button wire:click="downloadTemplate" color="primary" icon="heroicon-o-arrow-down-tray">
                    Télécharger le modèle Excel
                </x-filament::button>
                <span class="text-sm text-gray-500">.xlsx — colonnes : site, universe, name, price_ht, stock, etc.</span>
            </div>
        </x-filament::section>

        {{-- Upload --}}
        <x-filament::section heading="2. Importer le fichier" description="Remplissez le modèle et importez-le">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Fichier Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" wire:model="excelFile" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB</p>
                    @error('excelFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-start">
                    <x-filament::button wire:click="import" color="success" icon="heroicon-o-arrow-up-tray">
                        Importer les produits
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Results --}}
        @if($result)
            <x-filament::section heading="Résultat de l'import">
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-4 bg-success-50 rounded-lg text-center dark:bg-success-900/20">
                        <div class="text-2xl font-bold text-success-600">{{ $result['success'] }}</div>
                        <div class="text-xs text-success-600">Importés</div>
                    </div>
                    <div class="p-4 bg-danger-50 rounded-lg text-center dark:bg-danger-900/20">
                        <div class="text-2xl font-bold text-danger-600">{{ count($result['errors']) }}</div>
                        <div class="text-xs text-danger-600">Erreurs</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg text-center dark:bg-gray-800">
                        <div class="text-2xl font-bold">{{ $result['total'] }}</div>
                        <div class="text-xs text-gray-500">Total lignes</div>
                    </div>
                </div>

                @if(count($result['errors']) > 0)
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-danger-600 mb-2">Détail des erreurs :</h3>
                        <ul class="list-disc list-inside text-sm text-danger-500 space-y-1">
                            @foreach(array_slice($result['errors'], 0, 20) as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                            @if(count($result['errors']) > 20)
                                <li class="text-gray-500">... et {{ count($result['errors']) - 20 }} autre(s)</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </x-filament::section>
        @endif

        {{-- Info --}}
        <x-filament::section heading="Colonnes du fichier" description="Description de chaque colonne">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="text-left py-2 px-3 font-medium">Colonne</th>
                            <th class="text-left py-2 px-3 font-medium">Obligatoire</th>
                            <th class="text-left py-2 px-3 font-medium">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        <tr><td class="py-2 px-3">site</td><td class="py-2 px-3 text-danger-500">Oui</td><td class="py-2 px-3">Slug du site : bijoux ou pierres</td></tr>
                        <tr><td class="py-2 px-3">universe</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Slug de l'univers (ex: colliers, bagues, emeraudes...)</td></tr>
                        <tr><td class="py-2 px-3">name</td><td class="py-2 px-3 text-danger-500">Oui</td><td class="py-2 px-3">Nom du produit</td></tr>
                        <tr><td class="py-2 px-3">slug</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Généré automatiquement si vide</td></tr>
                        <tr><td class="py-2 px-3">reference</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Générée automatiquement si vide</td></tr>
                        <tr><td class="py-2 px-3">description</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Description du produit</td></tr>
                        <tr><td class="py-2 px-3">price_ht</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Prix unitaire HT</td></tr>
                        <tr><td class="py-2 px-3">vat_rate</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">TVA (%) — défaut: 20</td></tr>
                        <tr><td class="py-2 px-3">stock</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Quantité en stock</td></tr>
                        <tr><td class="py-2 px-3">moq</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Quantité minimale de commande — défaut: 1</td></tr>
                        <tr><td class="py-2 px-3">pack_size</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Conditionnement — défaut: 1</td></tr>
                        <tr><td class="py-2 px-3">material</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Matière (ex: Laiton doré)</td></tr>
                        <tr><td class="py-2 px-3">finish</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Finition (ex: Poli brillant)</td></tr>
                        <tr><td class="py-2 px-3">quality_grade</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Qualité</td></tr>
                        <tr><td class="py-2 px-3">tag</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">Étiquette (ex: Nouveauté, Best-seller)</td></tr>
                        <tr><td class="py-2 px-3">is_new</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">1 ou 0</td></tr>
                        <tr><td class="py-2 px-3">active</td><td class="py-2 px-3">Non</td><td class="py-2 px-3">1 (visible) ou 0 (caché) — défaut: 1</td></tr>
                    </tbody>
                </table>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
