<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Backoffice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['hotel_id', 'username', 'password'];

    protected $hidden = ['password'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}

