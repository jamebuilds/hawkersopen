<?php

use App\Models\Closure;
use App\Models\HawkerCenter;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('syncs hawker centers from api', function () {
    Http::fake([
        'data.gov.sg/*' => Http::response([
            'result' => [
                'total' => 2,
                'records' => [
                    [
                        '_id' => 1,
                        'name' => 'Test Hawker Centre',
                        'address_myenv' => '123 Test Street',
                        'latitude_hc' => '1.3456',
                        'longitude_hc' => '103.8765',
                        'q1_cleaningstartdate' => '15/1/2026',
                        'q1_cleaningenddate' => '18/1/2026',
                    ],
                    [
                        '_id' => 2,
                        'name' => 'Another Hawker Centre',
                        'address_myenv' => '456 Another Street',
                        'other_works_startdate' => '1/2/2026',
                        'other_works_enddate' => '28/2/2026',
                        'remarks_other_works' => 'Renovation works',
                    ],
                ],
            ],
        ]),
    ]);

    $this->artisan('hawker:sync')
        ->expectsOutput('Fetching hawker center data from data.gov.sg...')
        ->expectsOutput('Synced 2 / 2 records...')
        ->expectsOutput('Successfully synced 2 hawker centers.')
        ->assertSuccessful();

    expect(HawkerCenter::count())->toBe(2);
    expect(Closure::count())->toBe(2);

    $hawker1 = HawkerCenter::where('external_id', 1)->first();
    expect($hawker1->name)->toBe('Test Hawker Centre')
        ->and($hawker1->address)->toBe('123 Test Street');

    $closure1 = $hawker1->closures->first();
    expect($closure1->type)->toBe('cleaning')
        ->and($closure1->start_date->format('Y-m-d'))->toBe('2026-01-15')
        ->and($closure1->end_date->format('Y-m-d'))->toBe('2026-01-18');

    $hawker2 = HawkerCenter::where('external_id', 2)->first();
    $closure2 = $hawker2->closures->first();
    expect($closure2->type)->toBe('other_works')
        ->and($closure2->start_date->format('Y-m-d'))->toBe('2026-02-01')
        ->and($closure2->remarks)->toBe('Renovation works');
});

it('handles api failure gracefully', function () {
    Http::fake([
        'data.gov.sg/*' => Http::response([], 500),
    ]);

    $this->artisan('hawker:sync')
        ->expectsOutput('Failed to fetch data from API. Existing data preserved.')
        ->assertFailed();
});

it('handles invalid dates gracefully', function () {
    Http::fake([
        'data.gov.sg/*' => Http::response([
            'result' => [
                'total' => 1,
                'records' => [
                    [
                        '_id' => 1,
                        'name' => 'Hawker With TBC Dates',
                        'q1_cleaningstartdate' => 'TBC',
                        'q1_cleaningenddate' => 'NA',
                    ],
                ],
            ],
        ]),
    ]);

    $this->artisan('hawker:sync')->assertSuccessful();

    $hawker = HawkerCenter::where('external_id', 1)->first();
    expect($hawker->closures)->toBeEmpty();
});

it('updates existing records on subsequent syncs', function () {
    $hawker = HawkerCenter::factory()->create([
        'external_id' => 1,
        'name' => 'Old Name',
    ]);
    Closure::factory()->for($hawker)->create();

    Http::fake([
        'data.gov.sg/*' => Http::response([
            'result' => [
                'total' => 1,
                'records' => [
                    [
                        '_id' => 1,
                        'name' => 'Updated Name',
                        'q1_cleaningstartdate' => '1/3/2026',
                        'q1_cleaningenddate' => '3/3/2026',
                    ],
                ],
            ],
        ]),
    ]);

    $this->artisan('hawker:sync')->assertSuccessful();

    expect(HawkerCenter::count())->toBe(1);
    expect(HawkerCenter::first()->name)->toBe('Updated Name');
    expect(Closure::count())->toBe(1);
    expect(Closure::first()->start_date->format('Y-m-d'))->toBe('2026-03-01');
});
