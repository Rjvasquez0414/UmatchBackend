<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Administrador
        \App\Models\User::create([
            'name' => 'Admin CSU',
            'email' => 'admin@unab.edu.co',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'full_name' => 'Administrador CSU UNAB',
            'bio' => 'Administrador del Centro de Servicios Universitarios',
            'program' => 'Administración Deportiva',
            'semester' => null,
            'code' => 'CSU2024',
            'avatar_color' => '#E8551E',
        ]);

        // Usuario Estudiante de prueba
        \App\Models\User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@unab.edu.co',
            'password' => bcrypt('password'),
            'role' => 'student',
            'full_name' => 'Juan Carlos Pérez González',
            'bio' => 'Estudiante de Ingeniería de Sistemas, apasionado por el fútbol',
            'program' => 'Ingeniería de Sistemas',
            'semester' => 6,
            'code' => 'U00123456',
            'avatar_color' => '#3498db',
        ]);

        // Usuario Estudiante 2
        \App\Models\User::create([
            'name' => 'María Rodríguez',
            'email' => 'maria.rodriguez@unab.edu.co',
            'password' => bcrypt('password'),
            'role' => 'student',
            'full_name' => 'María Fernanda Rodríguez López',
            'bio' => 'Me encanta el volleyball y el basketball',
            'program' => 'Administración de Empresas',
            'semester' => 4,
            'code' => 'U00789012',
            'avatar_color' => '#e74c3c',
        ]);
    }
}
