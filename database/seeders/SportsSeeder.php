<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sports = [
            [
                'name' => 'Fútbol',
                'slug' => 'futbol',
                'emoji' => '⚽',
                'is_outdoor' => true,
            ],
            [
                'name' => 'Basketball',
                'slug' => 'basketball',
                'emoji' => '🏀',
                'is_outdoor' => true,
            ],
            [
                'name' => 'Tenis',
                'slug' => 'tenis',
                'emoji' => '🎾',
                'is_outdoor' => true,
            ],
            [
                'name' => 'Pádel',
                'slug' => 'padel',
                'emoji' => '🏸',
                'is_outdoor' => true,
            ],
            [
                'name' => 'Volleyball',
                'slug' => 'volleyball',
                'emoji' => '🏐',
                'is_outdoor' => true,
            ],
            [
                'name' => 'Billar',
                'slug' => 'billar',
                'emoji' => '🎱',
                'is_outdoor' => false,
            ],
            [
                'name' => 'Ping Pong',
                'slug' => 'pingpong',
                'emoji' => '🏓',
                'is_outdoor' => false,
            ],
        ];

        foreach ($sports as $sport) {
            \App\Models\Sport::create($sport);
        }
    }
}
