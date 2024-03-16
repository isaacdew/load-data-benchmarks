<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Benchmark;
use Isaacdew\LoadData\LoadData;

class LoadDataBenchmark extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-data-benchmark';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads data from CSV into the DB using the LoadData package which uses MySQL\'s LOAD DATA INFILE statement.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $milliseconds = Benchmark::measure($this->loadData(...), 3);

        $minutes = round($milliseconds / 1000, 0, PHP_ROUND_HALF_UP) / 60;

        $this->line("Duration: {$minutes} minutes");
    }

    protected function loadData()
    {
        // Clear the people table
        Person::truncate();

        LoadData::from(storage_path('app/test.csv'))
            ->to(Person::class)
            ->fieldsTerminatedBy(',')
            ->fieldsEnclosedBy('"', true)
            ->useFileHeaderForColumns()
            ->onlyLoadColumns([
                'first_name',
                'last_name',
                'email',
                'phone',
                'street_address',
                'city',
                'state',
                'postal_code',
                'country',
                'date_of_birth',
            ])
            ->setColumn('date_of_birth', "STR_TO_DATE(@date_of_birth, '%c/%d/%Y')")
            ->load();
    }
}
