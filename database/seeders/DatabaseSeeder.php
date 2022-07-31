<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Group;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Group::factory()->count(500)->create();
        User::factory(500)->create();
        Event::factory(1000)->create();
        Image::factory(2500)->create();
        DB::table('group_event')->insert(
            Group::all()->map(function () {
                return [
                    'group_id' => Group::inRandomOrder()->first()->id,
                    'event_id' => Event::inRandomOrder()->first()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray()
        );

        $events = Event::all();
        $images = Image::all();
        foreach ($events as $event) {
            $event->image_id = $images->random()->id;
            $event->save();
        }
    }
}
