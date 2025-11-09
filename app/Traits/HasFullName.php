<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasFullName
{
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $primaryField = $this->fullNamePrimaryAttribute();
                $secondaryField = $this->fullNameSecondaryAttribute();

                $primary = $primaryField ? $this->getAttribute($primaryField) : null;
                $secondary = $secondaryField ? $this->getAttribute($secondaryField) : null;

                return match (true) {
                    filled($primary) && filled($secondary) => trim("{$primary} {$secondary}"),
                    filled($primary) => $primary,
                    filled($secondary) => $secondary,
                    default => null,
                };
            }
        );
    }

    protected function fullNamePrimaryAttribute(): ?string
    {
        return 'first_name';
    }

    protected function fullNameSecondaryAttribute(): ?string
    {
        return 'last_name';
    }
}

