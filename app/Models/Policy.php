<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Policy extends Model
{
    protected $fillable = ['name', 'request_url'];

    public function userPolicies(): HasMany
    {
        return $this->hasMany(UserPolicy::class);
    }
}