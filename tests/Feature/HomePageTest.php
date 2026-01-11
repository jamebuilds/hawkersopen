<?php

use App\Models\Closure;
use App\Models\HawkerCenter;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('displays the home page', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Hawker Centers Closing Soon');
});

it('shows message when no hawker centers are closing soon', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('No hawker centers are scheduled to close in the next 30 days');
});

it('displays hawker centers with upcoming cleaning', function () {
    $hawker = HawkerCenter::factory()->create([
        'name' => 'Maxwell Food Centre',
        'address' => '1 Kadayanallur Street',
    ]);
    Closure::factory()->for($hawker)->cleaning()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(8),
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Maxwell Food Centre')
        ->assertSee('1 Kadayanallur Street')
        ->assertSee('Cleaning');
});

it('displays hawker centers with upcoming other works', function () {
    $hawker = HawkerCenter::factory()->create([
        'name' => 'Chinatown Complex',
    ]);
    Closure::factory()->for($hawker)->otherWorks()->create([
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(30),
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Chinatown Complex')
        ->assertSee('Other Works');
});

it('does not show hawker centers closing after 30 days', function () {
    $hawker = HawkerCenter::factory()->create([
        'name' => 'Far Future Hawker',
    ]);
    Closure::factory()->for($hawker)->create([
        'start_date' => now()->addDays(45),
        'end_date' => now()->addDays(48),
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertDontSee('Far Future Hawker');
});

it('does not show hawker centers that finished closing', function () {
    $hawker = HawkerCenter::factory()->create([
        'name' => 'Past Hawker',
    ]);
    Closure::factory()->for($hawker)->create([
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(7),
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertDontSee('Past Hawker');
});
