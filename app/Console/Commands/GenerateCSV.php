<?php

namespace App\Console\Commands;

use Faker\Generator;
use Illuminate\Console\Command;

class GenerateCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-csv {--lines=10000000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a large CSV.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $faker)
    {
        $lines = $this->option('lines');

        $progressBar = $this->output->createProgressBar($lines);

        $path = storage_path('app/test.csv');

        $progressBar->start();

        $file = fopen($path, 'w');

        // Add column headings
        fputcsv($file, [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Street Address',
            'City',
            'State',
            'Postal Code',
            'Country',
            'Date of Birth',
        ]);

        for ($i = 0; $i <= $lines; $i++) {
            fputcsv($file, [
                $i,
                $firstName = $faker->firstName(),
                $lastName = $faker->lastName(),
                $firstName.'.'.$lastName.'@example.com',
                $faker->phoneNumber(),
                $faker->streetAddress(),
                $faker->city(),
                $faker->state(),
                $faker->postcode(),
                'United States',
                $faker->date('m/d/Y'),
            ]);

            $progressBar->advance();
        }

        fclose($file);

        $progressBar->finish();
    }
}
