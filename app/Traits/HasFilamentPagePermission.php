<?php

namespace App\Traits;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

trait HasFilamentPagePermission
{
    public static function getPermissionName(): string
    {
        $class = class_basename(static::class);
        // Convert 'ManageContent' -> 'manage_content', 'Stats' -> 'stats', etc.
        $name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $class);
        return 'view_' . strtolower($name);
    }

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if (!$user->isAdmin() && !$user->can(static::getPermissionName())) {
            abort(403);
        }
    }
}
