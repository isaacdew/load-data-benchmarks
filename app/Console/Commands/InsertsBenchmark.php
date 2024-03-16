<?php

namespace App\Console\Commands;

use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\DB;

class InsertsBenchmark extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:inserts-benchmark';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads the CSV into memory one line at a time and inserts the data into the DB in batches.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $milliseconds = Benchmark::measure($this->insertData(...), 3);

        $minutes = round($milliseconds / 1000, 0, PHP_ROUND_HALF_UP) / 60;

        $this->line("Duration: {$minutes} minutes");

    }

    protected function insertData()
    {
        // Clear the people table
        Person::truncate();

        // Disable unique checks and foreign key checks to speed up inserts
        DB::statement('SET @@session.unique_checks = 0');
        DB::statement('SET @@session.foreign_key_checks = 0');

        $file = fopen(storage_path('app/test.csv'), 'r');

        $i = 0;
        $batch = [];
        $batchSize = 1000;
        while ($line = fgetcsv($file)) {
            if ($i === 0) {
                $i++;

                continue;
            }

            $batch[] = [
                'first_name' => $line[1],
                'last_name' => $line[2],
                'email' => $line[3],
                'phone' => $line[4],
                'street_address' => $line[5],
                'city' => $line[6],
                'state' => $line[7],
                'postal_code' => $line[8],
                'country' => $line[9],
                'date_of_birth' => Carbon::parse($line[10])->format('Y-m-d'),
            ];

            $i++;

            if (($i % $batchSize) === 0) {
                Person::insert($batch);
                $batch = [];
            }

        }

        // Insert the remaining batch
        if (! empty($batch)) {
            Person::insert($batch);
        }
    }
}
