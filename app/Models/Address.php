<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'entity_id',
        'entity_type',
        'street',
        'city',
        'state',
        'zip',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
