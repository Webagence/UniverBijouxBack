<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasFilamentPageAccess
{
    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;

        // Convert Page class name to permission name
        $class = class_basename(static::class);
        $perm = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class));

        // Check page-level permission first, fallback to view_ prefix
        if ($user->can('view_' . $perm)) return true;
        if ($user->can('edit_' . $perm)) return true;

        return false;
    }
}
