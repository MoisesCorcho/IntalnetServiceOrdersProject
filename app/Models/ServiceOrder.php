<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\ServiceOrderObserver;
#[ObservedBy([ServiceOrderObserver::class])]
class ServiceOrder extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceOrderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'title',
        'description',
        'state',
        'check_in_date',
        'scheduled_at',
        'assigned_user_id',
        'customer_id',
        'customer_name_snapshot',
        'customer_address_snapshot',
        'customer_phone_snapshot',
        'customer_email_snapshot',
        'completed_at',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
