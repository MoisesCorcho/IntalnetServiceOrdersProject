<?php

namespace Database\Factories;

use App\Enums\EnumServiceOrderStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkInDate = Carbon::instance(fake()->dateTimeBetween('-3 months', 'now'));
        $scheduledAt = fake()->optional(0.6)->dateTimeBetween(
            $checkInDate,
            $checkInDate->copy()->addWeeks(2)
        );
        $state = fake()->randomElement(EnumServiceOrderStatus::values());
        $baseForCompletion = $scheduledAt
            ? Carbon::instance($scheduledAt)
            : $checkInDate;
        $completedAt = $state === EnumServiceOrderStatus::CLOSED->value
            ? fake()->dateTimeBetween($baseForCompletion, $baseForCompletion->copy()->addHours(48))
            : null;

        return [
            'order_number' => fake()->unique()->bothify('SO-#####'),
            'title' => fake()->sentence(6),
            'description' => fake()->optional()->paragraph(),
            'state' => $state,
            'check_in_date' => $checkInDate->format('Y-m-d'),
            'scheduled_at' => $scheduledAt,
            'assigned_user_id' => User::factory(),
            'customer_id' => Customer::factory()->has(Address::factory(), 'addresses'),
            'customer_name_snapshot' => null,
            'customer_address_snapshot' => null,
            'customer_phone_snapshot' => null,
            'customer_email_snapshot' => null,
            'completed_at' => $completedAt,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(fn (ServiceOrder $serviceOrder) => $this->fillCustomerSnapshots($serviceOrder))
            ->afterCreating(function (ServiceOrder $serviceOrder): void {
                $this->fillCustomerSnapshots($serviceOrder);
                $serviceOrder->save();
            });
    }

    private function fillCustomerSnapshots(ServiceOrder $serviceOrder): void
    {
        $customer = $serviceOrder->customer;

        if (!$customer) {
            return;
        }

        if (!$customer->relationLoaded('addresses')) {
            $customer->load('addresses');
        }

        $serviceOrder->customer_name_snapshot = $customer->full_name ?? trim("{$customer->first_name} {$customer->last_name}");
        $serviceOrder->customer_phone_snapshot = $customer->phone;
        $serviceOrder->customer_email_snapshot = $customer->email;

        $address = $customer->addresses->first();
        $serviceOrder->customer_address_snapshot = $address?->full_address;
    }
}
