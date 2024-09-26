<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Token extends Model
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
        'type',
        'client_id'
    ];



    // Relationships

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
