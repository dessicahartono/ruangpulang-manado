<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = ['name', 'description', 'position'];

    protected function casts(): array
    {
        return ['description' => 'array'];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class)->orderBy('position')->orderBy('id');
    }
}
