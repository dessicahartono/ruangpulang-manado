<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $fillable = ['name', 'photo', 'description', 'link', 'position'];

    /** Data yang belum ada, supaya admin tahu apa yang perlu dilengkapi. */
    public function missingData(): array
    {
        return array_values(array_filter([
            $this->photo ? null : 'foto',
            $this->link ? null : 'link',
        ]));
    }
}
