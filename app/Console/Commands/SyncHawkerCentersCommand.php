<?php

namespace App\Console\Commands;

use App\Models\HawkerCenter;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncHawkerCentersCommand extends Command
{
    protected $signature = 'hawker:sync';

    protected $description = 'Sync hawker center cleaning and maintenance schedules from data.gov.sg';

    private const API_URL = 'https://data.gov.sg/api/action/datastore_search';

    private const RESOURCE_ID = 'b80cb643-a732-480d-86b5-e03957bc82aa';

    public function handle(): int
    {
        $this->info('Fetching hawker center data from data.gov.sg...');

        $offset = 0;
        $limit = 200;
        $totalSynced = 0;

        try {
            do {
                $response = Http::timeout(30)->get(self::API_URL, [
                    'resource_id' => self::RESOURCE_ID,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

                if (! $response->successful()) {
                    Log::error('Failed to fetch hawker center data', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    $this->error('Failed to fetch data from API. Existing data preserved.');

                    return self::FAILURE;
                }

                $data = $response->json();
                $records = $data['result']['records'] ?? [];
                $total = $data['result']['total'] ?? 0;

                foreach ($records as $record) {
                    $this->syncRecord($record);
                    $totalSynced++;
                }

                $offset += $limit;
                $this->info("Synced {$totalSynced} / {$total} records...");

            } while ($offset < $total);

            $this->info("Successfully synced {$totalSynced} hawker centers.");

            return self::SUCCESS;

        } catch (\Exception $e) {
            Log::error('Exception while syncing hawker centers', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('An error occurred: '.$e->getMessage());
            $this->info('Existing data has been preserved.');

            return self::FAILURE;
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function syncRecord(array $record): void
    {
        $hawkerCenter = HawkerCenter::updateOrCreate(
            ['external_id' => $record['_id']],
            [
                'name' => $record['name'] ?? '',
                'address' => $record['address_myenv'] ?? null,
                'latitude' => $this->parseCoordinate($record['latitude_hc'] ?? null),
                'longitude' => $this->parseCoordinate($record['longitude_hc'] ?? null),
                'photo_url' => $record['photourl'] ?? null,
            ]
        );

        $hawkerCenter->closures()->delete();

        $this->syncClosures($hawkerCenter, $record);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function syncClosures(HawkerCenter $hawkerCenter, array $record): void
    {
        $closures = [];

        foreach (['q1', 'q2', 'q3', 'q4'] as $quarter) {
            $start = $this->parseDate($record["{$quarter}_cleaningstartdate"] ?? null);
            $end = $this->parseDate($record["{$quarter}_cleaningenddate"] ?? null);

            if ($start && $end) {
                $closures[] = [
                    'type' => 'cleaning',
                    'start_date' => $start,
                    'end_date' => $end,
                    'remarks' => null,
                ];
            }
        }

        $otherStart = $this->parseDate($record['other_works_startdate'] ?? null);
        $otherEnd = $this->parseDate($record['other_works_enddate'] ?? null);

        if ($otherStart && $otherEnd) {
            $closures[] = [
                'type' => 'other_works',
                'start_date' => $otherStart,
                'end_date' => $otherEnd,
                'remarks' => $record['remarks_other_works'] ?? null,
            ];
        }

        foreach ($closures as $closure) {
            $hawkerCenter->closures()->create($closure);
        }
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (empty($value) || in_array(strtoupper($value), ['TBC', 'NA', 'N/A', '-'])) {
            return null;
        }

        try {
            // j/n/Y = day of month without leading zero / month without leading zero / 4 digits year
            return Carbon::createFromFormat('j/n/Y', $value);
        } catch (\Exception) {
            return null;
        }
    }

    private function parseCoordinate(?string $value): ?float
    {
        if (empty($value) || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
