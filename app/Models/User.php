<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'approved',
        'company_name',
        'siret',
        'contact_name',
        'address',
        'city',
        'postal_code',
        'country',
        'vat_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved' => 'boolean',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isPro(): bool
    {
        return $this->hasRole('pro');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class, 'author_id');
    }

    public function getProfileAttribute(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'company_name' => $this->company_name ?? '',
            'siret' => $this->siret ?? '',
            'contact_name' => $this->contact_name ?? $this->name,
            'phone' => $this->phone ?? '',
            'address' => $this->address ?? '',
            'city' => $this->city ?? '',
            'postal_code' => $this->postal_code ?? '',
            'country' => $this->country ?? 'France',
            'vat_number' => $this->vat_number ?? '',
            'approved' => $this->approved,
        ];
    }
}
