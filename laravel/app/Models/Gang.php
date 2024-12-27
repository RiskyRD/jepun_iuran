<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gang extends Model
{
    use HasFactory;

    protected $fillable = [
        'gang_name',
        'coordinator',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'gang_id', 'id');
    }
}
