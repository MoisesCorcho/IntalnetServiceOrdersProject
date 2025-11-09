<?php

use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('generates the full name combining name and last name', function (): void {
    $user = User::factory()->create([
        'name' => 'Laura',
        'last_name' => 'Gonzalez',
    ]);

    expect($user->full_name)->toBe('Laura Gonzalez');
});

it('falls back to the available component when full name is incomplete', function (): void {
    $userWithOnlyName = User::factory()->create([
        'name' => 'Carlos',
        'last_name' => '',
    ]);

    $userWithOnlyLastName = User::factory()->create([
        'name' => '',
        'last_name' => 'Diaz',
    ]);

    expect($userWithOnlyName->full_name)->toBe('Carlos')
        ->and($userWithOnlyLastName->full_name)->toBe('Diaz');
});

it('returns the correct initials', function (): void {
    $user = User::factory()->create([
        'name' => 'Monica',
        'last_name' => 'Perez',
    ]);

    expect($user->initials())->toBe('MP');
});

it('exposes the service orders assigned to the user', function (): void {
    $user = User::factory()->create();
    ServiceOrder::factory()->count(3)->create([
        'assigned_user_id' => $user->id,
    ]);

    expect($user->refresh()->serviceOrders)->toHaveCount(3);
});

it('scope technicians only returns users with the tecnico role', function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Role::firstOrCreate([
        'name' => 'tecnico',
        'guard_name' => 'web',
    ]);

    $technician = User::factory()->create();
    $technician->assignRole('tecnico');

    $nonTechnician = User::factory()->create();

    $result = User::query()->technicians()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->is($technician))->toBeTrue()
        ->and($result->contains($nonTechnician))->toBeFalse();
});
