<?php

namespace App\Traits;

use App\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAddressTrait
{
    final public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'entity');
    }
}
