<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => collect([
                $this->street,
                $this->city,
                $this->state,
                $this->zip,
            ])
                ->filter(fn ($value) => filled($value))
                ->implode(', ') ?: null
        );
    }
}
