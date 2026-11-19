<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds a live site: real base content and the 2027 schedule (dates, cities,
 * hotels). No sample trainings, news, testimonials, partners or demo admin.
 * Create the first admin with: php artisan app:create-admin
 *
 *   php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([ContentSeeder::class, Schedule2027Seeder::class]);
    }
}
