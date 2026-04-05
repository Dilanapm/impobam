<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) (env('ADMIN_NAME') ?: 'Administrador');
        $email = (string) (env('ADMIN_EMAIL') ?: 'admin@impobam.test');
        $password = env('ADMIN_PASSWORD');

        if (! is_string($password) || trim($password) === '') {
            if (app()->environment('local')) {
                $password = 'admin123';
                $this->command?->warn('ADMIN_PASSWORD no está configurado. Usando contraseña por defecto (solo local): admin123');
            } else {
                $this->command?->warn('ADMIN_PASSWORD no está configurado. No se creó el usuario administrador.');
                return;
            }
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
            ]
        );

        $this->command?->info("Usuario administrador listo: {$email}");
    }
}
