<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'id_photo_path_front','id_photo_path_back', 'profile_photo_path', 'is _verified'];
    protected $casts = [
        'is_verified' => 'boolean',
    ];
    /**
     * Get the user that owns the customer.
     */
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
