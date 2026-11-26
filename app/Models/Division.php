<?php

namespace App\Models;

use App\Models\Concerns\HasEnglish;
use Database\Factories\DivisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    /** @use HasFactory<DivisionFactory> */
    use HasEnglish;
    use HasFactory;

    protected $fillable = ['name', 'name_en', 'description', 'description_en', 'position'];

    protected array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return ['description' => 'array', 'description_en' => 'array'];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class)->orderBy('position')->orderBy('id');
    }
}
