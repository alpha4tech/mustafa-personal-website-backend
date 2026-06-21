<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\ResetPasswordNotification;


class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable, CanResetPassword;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function promoteToAdmin()
    {
        $this->role = 'admin';
        return $this->save();
    }

    public function demoteToUser()
    {
        $this->role = 'user';
        return $this->save();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission($permission)
    {
        if ($this->role === 'admin') return true;
        return $this->permissions()->where('name', $permission)->exists();
    }

    public function sendPasswordResetNotification($token)
    {
        $url = config('app.frontend_url') . '/admin/auth/reset-password?token=' . $token . '&email=' . urlencode($this->email);
        $this->notify(new \App\Notifications\ResetPasswordNotification($url));
    }


}
