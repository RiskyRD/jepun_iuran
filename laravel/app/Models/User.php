<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser 
{
    use HasRoles;
    use HasPanelShield;
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'name',
        'gang',
        'telephone',
        'status',
        'gang_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public function wajibPayments()
    {
        return $this->hasMany(Income::class, 'user_id')->where('category', 'wajib');
    }

    // Relationship for sampah payments
    public function sampahPayments()
    {
        return $this->hasMany(Income::class, 'user_id')->where('category', 'sampah');
    }

    public function successions()
    {
        return $this->hasMany(Succession::class, 'from_id');
    }

    public function successor()
    {
        return $this->belongsTo(User::class, 'successor_id');
    }

    public function predecessors()
    {
        return $this->hasMany(Succession::class, 'to_id');
    }

    public function gang()
    {
        return $this->belongsTo(Gang::class, 'gang_id', 'id');
    }
}
