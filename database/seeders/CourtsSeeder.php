<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourtsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $futbol = \App\Models\Sport::where('slug', 'futbol')->first();
        $basketball = \App\Models\Sport::where('slug', 'basketball')->first();
        $volleyball = \App\Models\Sport::where('slug', 'volleyball')->first();
        $tenis = \App\Models\Sport::where('slug', 'tenis')->first();
        $padel = \App\Models\Sport::where('slug', 'padel')->first();
        $billar = \App\Models\Sport::where('slug', 'billar')->first();
        $pingpong = \App\Models\Sport::where('slug', 'pingpong')->first();

        // Canchas Multiuso (futbol y basketball)
        for ($i = 1; $i <= 3; $i++) {
            $court = \App\Models\Court::create([
                'name' => "Cancha Multiuso $i",
                'slug' => "multiuso-$i",
                'type' => 'cancha',
                'is_outdoor' => true,
                'is_admin_only' => false,
            ]);
            $court->sports()->attach([$futbol->id, $basketball->id]);
        }

        // Cancha Volleyball
        $court = \App\Models\Court::create([
            'name' => 'Cancha Volleyball',
            'slug' => 'volleyball-1',
            'type' => 'cancha',
            'is_outdoor' => true,
            'is_admin_only' => false,
        ]);
        $court->sports()->attach($volleyball->id);

        // Cancha Tenis
        $court = \App\Models\Court::create([
            'name' => 'Cancha Tenis',
            'slug' => 'tenis-1',
            'type' => 'cancha',
            'is_outdoor' => true,
            'is_admin_only' => false,
        ]);
        $court->sports()->attach($tenis->id);

        // Cancha Pádel
        $court = \App\Models\Court::create([
            'name' => 'Cancha Pádel',
            'slug' => 'padel-1',
            'type' => 'cancha',
            'is_outdoor' => true,
            'is_admin_only' => false,
        ]);
        $court->sports()->attach($padel->id);

        // Mesas Billar (3)
        for ($i = 1; $i <= 3; $i++) {
            $court = \App\Models\Court::create([
                'name' => "Mesa Billar $i",
                'slug' => "billar-$i",
                'type' => 'mesa',
                'is_outdoor' => false,
                'is_admin_only' => false,
            ]);
            $court->sports()->attach($billar->id);
        }

        // Mesas Ping Pong (3)
        for ($i = 1; $i <= 3; $i++) {
            $court = \App\Models\Court::create([
                'name' => "Mesa Ping Pong $i",
                'slug' => "pingpong-$i",
                'type' => 'mesa',
                'is_outdoor' => false,
                'is_admin_only' => false,
            ]);
            $court->sports()->attach($pingpong->id);
        }

        // Coliseo CSU (admin only, futbol, basketball, volleyball)
        $court = \App\Models\Court::create([
            'name' => 'Coliseo CSU',
            'slug' => 'coliseo-1',
            'type' => 'cancha',
            'is_outdoor' => false,
            'is_admin_only' => true,
        ]);
        $court->sports()->attach([$futbol->id, $basketball->id, $volleyball->id]);
    }
}
