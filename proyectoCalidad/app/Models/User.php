<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    public function encuestas(): HasMany
    {
        return $this->hasMany(Encuesta::class, 'id_usuario');
    }

    public function envios(): HasMany
    {
        return $this->hasMany(Envios::class, 'id_usuario');
    }

    public function isAprendiz(): bool
    {
        return $this->role === 'aprendiz';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isAdministrador(): bool
    {
        return $this->role === 'administrador';
    }

    public function canManageSurveys(): bool
    {
        return $this->isInstructor() || $this->isAdministrador();
    }
}
