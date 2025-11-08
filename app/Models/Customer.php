<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAddressTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, HasAddressTrait, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'secondary_phone'
    ];

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => match (true) {
                filled($this->first_name) && filled($this->last_name) => trim("{$this->first_name} {$this->last_name}"),
                filled($this->first_name) => $this->first_name,
                filled($this->last_name) => $this->last_name,
                default => null,
            }
        );
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
