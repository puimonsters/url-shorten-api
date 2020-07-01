<?php

use App\UrlShortens;
use Illuminate\Database\Seeder;

class UrlShortensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        UrlShortens::truncate();

        $faker = \Faker\Factory::create();

        // And now, let's create a few articles in our database:
        for ($i = 0; $i < 50; $i++) {
            UrlShortens::create([
                'url' => $faker->url,
                'short_code' => substr(md5(uniqid(rand(), true)), 0, 6),
                'hits' => 0,
                'expiration_date' => $faker->date()
            ]);
        }
    }
}
