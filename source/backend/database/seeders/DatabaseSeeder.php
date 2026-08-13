<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->updateOrCreate(
            ['code' => 'SMKN6-TJT'],
            [
                'name' => 'SMK Negeri 6 Tanjung Jabung Timur',
                'npsn' => null,
                'slug' => 'smkn-6-tanjung-jabung-timur',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ],
        );

        $users = [
            [
                'name' => 'Andi Saputra',
                'username' => 'andi.saputra',
                'email' => 'andi@edunova.local',
                'role' => 'student',
                'subtitle' => 'XI DKV 1',
            ],
            [
                'name' => 'Susanto, S.Kom',
                'username' => 'susanto',
                'email' => 'susanto@edunova.local',
                'role' => 'teacher',
                'subtitle' => 'Guru DKV',
            ],
            [
                'name' => 'Administrator EduNova',
                'username' => 'admin',
                'email' => 'admin@edunova.local',
                'role' => 'admin',
                'subtitle' => 'Administrator Sekolah',
            ],
        ];

        foreach ($users as $data) {
            User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    ...$data,
                    'school_id' => $school->id,
                    'password' => 'edunova123',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
            );
        }

        $this->call([
            Phase04AcademicSeeder::class,
            Phase05DigitalClassroomSeeder::class,
            Phase06AssignmentSeeder::class,
            Phase07QuizSeeder::class,
            Phase08GradebookSeeder::class,
            Phase09CommunicationAttendanceSeeder::class,
        ]);
    }
}
