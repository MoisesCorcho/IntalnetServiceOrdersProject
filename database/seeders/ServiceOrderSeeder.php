<?php

namespace Database\Seeders;

use App\Enums\EnumServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    private const ORDERS_PER_TECHNICIAN = 10;
    private const CLOSED_PER_TECHNICIAN = 5;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $technicians = User::query()->role('tecnico')->get();

        if ($technicians->isEmpty()) {
            $this->createUnassignedOrders();
            $this->createFutureAssignedAppointments();
            $this->createHistoricalCompletedOrders();
            return;
        }

        $now = now()->startOfHour();

        foreach ($technicians as $technician) {
            for ($index = 0; $index < self::ORDERS_PER_TECHNICIAN; $index++) {
                $dayOffset = intdiv($index, 5);
                $slotIndex = $index % 5;

                $checkInDateTime = $now->copy()
                    ->subDays($dayOffset)
                    ->setTime(9 + $slotIndex, 0, 0);

                $scheduledAt = $checkInDateTime->copy()->addMinutes(30);
                $isClosed = $index < self::CLOSED_PER_TECHNICIAN;
                $completedAt = $isClosed
                    ? $scheduledAt->copy()->addMinutes(fake()->numberBetween(30, 120))
                    : null;

                $state = $isClosed
                    ? EnumServiceOrderStatus::CLOSED->value
                    : fake()->randomElement([
                        EnumServiceOrderStatus::RECEIVED->value,
                        EnumServiceOrderStatus::ON_THE_WAY->value,
                        EnumServiceOrderStatus::AT_DESTINATION->value,
                    ]);

                ServiceOrder::factory()
                    ->state([
                        'assigned_user_id' => $technician->id,
                        'state' => $state,
                        'check_in_date' => $checkInDateTime->toDateString(),
                        'scheduled_at' => $scheduledAt,
                        'completed_at' => $completedAt,
                        'created_at' => $checkInDateTime,
                        'updated_at' => $completedAt ?? $scheduledAt,
                    ])
                    ->create();
            }
        }

        $this->createUnassignedOrders();
        $this->createFutureAssignedAppointments($technicians);
        $this->createHistoricalCompletedOrders($technicians);
    }

    private function createUnassignedOrders(): void
    {
        $openStates = [
            EnumServiceOrderStatus::RECEIVED->value,
            EnumServiceOrderStatus::ON_THE_WAY->value,
            EnumServiceOrderStatus::AT_DESTINATION->value,
            EnumServiceOrderStatus::PROCESS_STARTED->value,
        ];

        ServiceOrder::factory()
            ->count(7)
            ->state(fn () => [
                'assigned_user_id' => null,
                'scheduled_at' => null,
                'state' => fake()->randomElement($openStates),
                'completed_at' => null,
                'check_in_date' => now()->toDateString(),
            ])
            ->create();
    }

    private function createFutureAssignedAppointments(?iterable $technicians = null): void
    {
        $technicians = $technicians ?? User::query()->role('tecnico')->get();

        if (empty($technicians)) {
            return;
        }

        $futureDate = now()->addDays(1)->setTime(10, 0);

        ServiceOrder::factory()
            ->count(5)
            ->state(function () use ($technicians, $futureDate) {
                $techList = $technicians instanceof \Illuminate\Support\Collection ? $technicians->all() : $technicians;
                $technician = $techList[array_rand($techList)];

                return [
                    'assigned_user_id' => $technician->id,
                    'scheduled_at' => $futureDate->copy()->addHours(fake()->numberBetween(0, 4)),
                    'state' => fake()->randomElement([
                        EnumServiceOrderStatus::RECEIVED->value,
                        EnumServiceOrderStatus::ON_THE_WAY->value,
                        EnumServiceOrderStatus::AT_DESTINATION->value,
                        EnumServiceOrderStatus::PROCESS_STARTED->value,
                    ]),
                    'completed_at' => null,
                    'check_in_date' => $futureDate->toDateString(),
                ];
            })
            ->create();
    }

    private function createHistoricalCompletedOrders(?iterable $technicians = null): void
    {
        $technicians = $technicians ?? User::query()->role('tecnico')->get();

        if (empty($technicians)) {
            return;
        }

        $techList = $technicians instanceof \Illuminate\Support\Collection ? $technicians->all() : $technicians;

        ServiceOrder::factory()
            ->count(300)
            ->state(function () use ($techList) {
                /** @var \App\Models\User $technician */
                $technician = $techList[array_rand($techList)];
                $daysAgo = fake()->numberBetween(10, 365);
                $checkInDateTime = now()->subDays($daysAgo)->setTime(fake()->numberBetween(8, 15), fake()->randomElement([0, 30]), 0);
                $scheduledAt = $checkInDateTime->copy()->addMinutes(fake()->numberBetween(15, 60));
                $completedAt = $scheduledAt->copy()->addMinutes(fake()->numberBetween(60, 120));

                return [
                    'assigned_user_id' => $technician->id,
                    'customer_id' => \App\Models\Customer::factory(),
                    'state' => EnumServiceOrderStatus::CLOSED->value,
                    'check_in_date' => $checkInDateTime->toDateString(),
                    'scheduled_at' => $scheduledAt,
                    'completed_at' => $completedAt,
                    'created_at' => $checkInDateTime,
                    'updated_at' => $completedAt,
                ];
            })
            ->create();
    }
}
