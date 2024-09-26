<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorizationCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 
        'expiration',
        'scopes',
        'user_id',
        'client_id',
        'redirect_uri',
        'code_challenge',
        'code_challenge_method',
        'refresh'
    ];


    // Relationships

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
