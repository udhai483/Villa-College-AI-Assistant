<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class)->orderBy('created_at', 'desc');
    }

    public function isAuthorizedDomain(): bool
    {
        $authorizedDomains = ['@villacollege.edu.mv', '@students.villacollege.edu.mv'];
        
        foreach ($authorizedDomains as $domain) {
            if (str_ends_with($this->email, $domain)) {
                return true;
            }
        }
        
        return false;
    }
}
