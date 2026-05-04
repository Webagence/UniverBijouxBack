<x-filament-panels::page>
    <x-filament::section heading="Configuration Stripe" description="Clés API Stripe pour le traitement des paiements en ligne">
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Mode</h3>
            <x-filament::input.wrapper label="Mode Stripe">
                <select wire:model="data.mode" class="fi-input block w-full border-none py-1.5 text-base text-gray-950 opacity-100 dark:text-white fi-color-gray bg-transparent">
                    <option value="test">Test (sandbox)</option>
                    <option value="live">Live (production)</option>
                </select>
            </x-filament::input.wrapper>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Utilisez le mode Test pour les développements et le mode Live pour la production.</p>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Clés API</h3>
            <div class="space-y-6">
                <x-filament::input.wrapper label="Clé publiable (Publishable Key)">
                    <x-filament::input
                        type="text"
                        wire:model="data.publishable_key"
                        placeholder="pk_test_..."
                    />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-400 dark:text-gray-500 -mt-4">Commence par <code>pk_test_</code> (test) ou <code>pk_live_</code> (production). Disponible dans votre dashboard Stripe → Developers → API keys.</p>

                <x-filament::input.wrapper label="Clé secrète (Secret Key)">
                    <x-filament::input
                        type="password"
                        wire:model="data.secret_key"
                        placeholder="sk_test_..."
                    />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-400 dark:text-gray-500 -mt-4">Commence par <code>sk_test_</code> (test) ou <code>sk_live_</code> (production). <strong>Ne partagez jamais cette clé.</strong></p>

                <x-filament::input.wrapper label="Webhook Secret">
                    <x-filament::input
                        type="password"
                        wire:model="data.webhook_secret"
                        placeholder="whsec_..."
                    />
                </x-filament::input.wrapper>
                <p class="text-xs text-gray-400 dark:text-gray-500 -mt-4">Commence par <code>whsec_</code>. Disponible dans votre dashboard Stripe → Developers → Webhooks → Endpoint details → Signing secret.</p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Configuration du Webhook</h3>
            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg space-y-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">Configurez un webhook Stripe avec l'URL suivante :</p>
                <code class="block p-3 bg-white dark:bg-gray-900 border rounded text-sm font-mono break-all">
                    {{ url('/api/stripe/webhook') }}
                </code>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Événements à écouter : <code>payment_intent.succeeded</code>, <code>payment_intent.payment_failed</code>
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="save" color="primary">
                Enregistrer les paramètres Stripe
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
