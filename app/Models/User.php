<?php

namespace App\Models;

use Filament\Panel;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\Models\Role;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasPanelShield;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setPasswordAttribute($value)
    {
        if (Hash::needsRehash($value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole(Utils::getSuperAdminName()) && $panel->getId() === 'admin') {
            return true;
        } else if ($panel->getId() === 'user') {
            return $this->hasRole(config('filament-shield.blog_user.name', 'blog_user'));
        }
        return false;
    }

    protected static function booted(): void
    {
        if (config('filament-shield.blog_user.enabled', false) && Role::exists('super_admin')) {
            FilamentShield::createRole(name: config('filament-shield.blog_user.name', 'blog_user'));

            static::created(function (User $user) {
                $user->assignRole(config('filament-shield.blog_user.name', 'blog_user'));
            });

            static::deleting(function (User $user) {
                $user->removeRole(config('filament-shield.blog_user.name', 'blog_user'));
            });
        }
    }
}
