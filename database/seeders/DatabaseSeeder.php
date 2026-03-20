<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\DentistProfile;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@clinic.test'],
            [
                'name' => 'Clinic Admin',
                'role' => 'admin',
                'phone' => '09170000001',
                'password' => Hash::make('password'),
            ]
        );

        $dentist = User::firstOrCreate(
            ['email' => 'dentist@clinic.test'],
            [
                'name' => 'Dr. Bella Cruz',
                'role' => 'dentist',
                'phone' => '09170000002',
                'password' => Hash::make('password'),
            ]
        );

        DentistProfile::firstOrCreate([
            'user_id' => $dentist->id,
        ], [
            'specialty' => 'Orthodontics',
            'years_of_experience' => 9,
            'bio' => 'Focuses on aligners and braces for adults and teens.',
        ]);

        foreach ([1, 2, 3, 4, 5] as $day) {
            Availability::firstOrCreate([
                'dentist_id' => $dentist->id,
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
            ]);
        }

        $patient = User::firstOrCreate(
            ['email' => 'patient@clinic.test'],
            [
                'name' => 'Test Patient',
                'role' => 'patient',
                'phone' => '09170000003',
                'password' => Hash::make('password'),
            ]
        );

        PatientProfile::firstOrCreate([
            'user_id' => $patient->id,
        ], [
            'medical_history' => 'No major conditions',
        ]);
    }
}
