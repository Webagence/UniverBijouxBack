<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageMaintenance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Maintenance';

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-maintenance';

    public bool $maintenanceMode = false;

    public string $maintenanceMessage = '';

    public function mount(): void
    {
        $this->maintenanceMode = file_exists('/var/www/francegems.com/maintenance.flag');
        $message = @file_get_contents('/var/www/francegems.com/maintenance/message.txt');
        $this->maintenanceMessage = $message ?: 'Notre équipe améliore actuellement la plateforme pour vous offrir une expérience encore plus exceptionnelle. Le site sera de retour très prochainement.';
    }

    public function save(): void
    {
        if ($this->maintenanceMode) {
            touch('/var/www/francegems.com/maintenance.flag');
        } else {
            @unlink('/var/www/francegems.com/maintenance.flag');
        }

        file_put_contents('/var/www/francegems.com/maintenance/message.txt', $this->maintenanceMessage);

        Notification::make()
            ->title($this->maintenanceMode ? 'Mode maintenance activé' : 'Mode maintenance désactivé')
            ->success()
            ->send();
    }
}
