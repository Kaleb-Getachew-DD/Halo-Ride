<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\CustomResetPassword;
use App\Models\Role;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'role_id',
        'username',
        'password',
        'email',
        'phone',
        'address',
        'is_active',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function sendPasswordResetNotification($token)
{
    $this->notify(new CustomResetPassword($token));
}

    /**
     * JWT Identifier.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Custom JWT Claims.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Relationship: Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relationship: Staff (one-to-one)
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Relationship: Customer (one-to-one)
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }


}
