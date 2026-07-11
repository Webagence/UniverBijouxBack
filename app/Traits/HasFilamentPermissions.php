<?php

namespace App\Traits;

use Filament\Panel;
use Illuminate\Support\Facades\Auth;

trait HasFilamentPermissions
{
    protected static function getPermissionPrefix(): string
    {
        // Convert 'ProductResource' -> 'product', 'UniverseResource' -> 'universe', etc.
        $class = class_basename(static::class);
        $name = str_replace('Resource', '', $class);
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        return $user->can('view_' . static::getPermissionPrefix());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        return $user->can('create_' . static::getPermissionPrefix());
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        return $user->can('edit_' . static::getPermissionPrefix());
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        return $user->can('delete_' . static::getPermissionPrefix());
    }
}
