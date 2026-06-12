<x-filament-panels::page>
    <x-filament::section heading="Configuration Shippingbo" description="Connectez votre application à Shippingbo pour la gestion des commandes et expéditions">
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Informations d'application</h3>
            <div class="space-y-6">
                <x-filament::input.wrapper label="Client ID">
                    <x-filament::input
                        type="text"
                        wire:model="data.client_id"
                        placeholder="Votre client_id Shippingbo"
                    />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-400 dark:text-gray-500 -mt-4">Fourni par Shippingbo lors de la création de votre application.</p>

                <x-filament::input.wrapper label="Client Secret">
                    <x-filament::input
                        type="password"
                        wire:model="data.client_secret"
                        placeholder="Votre client_secret Shippingbo"
                    />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-400 dark:text-gray-500 -mt-4">Gardez cette clé secrète. Ne la partagez jamais.</p>

                <x-filament::input.wrapper label="App ID">
                    <x-filament::input
                        type="text"
                        wire:model="data.app_id"
                        placeholder="Votre app_id Shippingbo"
                    />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-400 dark:text-gray-500 -mt-4">L'ID de votre application dans le dashboard Shippingbo.</p>

                <x-filament::input.wrapper label="Webhook Secret">
                    <x-filament::input
                        type="password"
                        wire:model="data.webhook_secret"
                        placeholder="Votre webhook_secret Shippingbo"
                    />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-400 dark:text-gray-500 -mt-4">Secret partagé pour vérifier les webhooks Shippingbo (X-Signature). Optionnel si la vérification IP est suffisante.</p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Connexion OAuth</h3>
            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg space-y-3">
                @if($authorizationUrl)
                    <p class="text-sm text-green-600 dark:text-green-400">
                        Cliquez sur le lien ci-dessous pour autoriser l'accès à votre compte Shippingbo :
                    </p>
                    <a href="{{ $authorizationUrl }}" target="_blank" class="inline-block px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        Autoriser l'accès à Shippingbo →
                    </a>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Après avoir enregistré les paramètres, cliquez sur "Connecter à Shippingbo" pour initier le flux OAuth.
                    </p>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Configuration du Webhook</h3>
            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg space-y-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">Configurez un webhook Shippingbo avec l'URL suivante :</p>
                <code class="block p-3 bg-white dark:bg-gray-900 border rounded text-sm font-mono break-all">
                    {{ url('/api/shippingbo/webhook') }}
                </code>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Événements à configurer : <code>Order.state</code> (mise à jour statut commande), <code>Product.stock</code> (mise à jour stock), <code>Shipment</code> (suivi livraison)
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Rendez-vous dans votre dashboard Shippingbo → Webhooks → Add → Sélectionnez "Order" et "state", puis entrez l'URL ci-dessus.
                </p>
            </div>
        </div>

        @if(!empty($syncStatus) && !isset($syncStatus['error']))
        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Statut de synchronisation</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <h4 class="font-medium dark:text-gray-200">Produits</h4>
                    <div class="mt-2 space-y-1 text-sm">
                        <p class="text-gray-600 dark:text-gray-300">Total : <strong>{{ $syncStatus['products']['total'] ?? 0 }}</strong></p>
                        <p class="text-green-600 dark:text-green-400">Synchronisés : <strong>{{ $syncStatus['products']['synced'] ?? 0 }}</strong></p>
                        <p class="text-amber-600 dark:text-amber-400">En attente : <strong>{{ $syncStatus['products']['pending'] ?? 0 }}</strong></p>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <h4 class="font-medium dark:text-gray-200">Commandes</h4>
                    <div class="mt-2 space-y-1 text-sm">
                        <p class="text-gray-600 dark:text-gray-300">Total : <strong>{{ $syncStatus['orders']['total'] ?? 0 }}</strong></p>
                        <p class="text-green-600 dark:text-green-400">Synchronisées : <strong>{{ $syncStatus['orders']['synced'] ?? 0 }}</strong></p>
                        <p class="text-amber-600 dark:text-amber-400">En attente : <strong>{{ $syncStatus['orders']['pending'] ?? 0 }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="mt-6 flex justify-end gap-3">
            @if(!empty($syncStatus) && !isset($syncStatus['error']))
            <x-filament::button wire:click="syncAllProducts" color="secondary">
                Synchroniser tous les produits
            </x-filament::button>
            @endif

            @if(!\App\Models\ShippingboSetting::isConnected())
            <x-filament::button wire:click="connect" color="primary">
                Connecter à Shippingbo
            </x-filament::button>
            @endif

            <x-filament::button wire:click="save" color="primary">
                Enregistrer les paramètres
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
