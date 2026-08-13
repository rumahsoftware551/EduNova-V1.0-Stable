<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\CourseSection;
use App\Models\LearningMaterial;
use App\Models\MaterialProgress;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class Phase05DigitalClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('username', 'susanto')->firstOrFail();
        $student = User::where('username', 'andi.saputra')->firstOrFail();
        $assignment = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->with(['subject', 'classGroup'])
            ->firstOrFail();

        $classroom = Classroom::updateOrCreate(
            ['teaching_assignment_id' => $assignment->id],
            [
                'school_id' => $teacher->school_id,
                'created_by' => $teacher->id,
                'title' => 'Fotografi Digital',
                'code' => $assignment->subject?->code ?? 'FOT-DIG',
                'description' => 'Ruang belajar Fotografi Digital kelas XI DKV 1. Pelajari konsep kamera, exposure, komposisi, dan praktik fotografi secara bertahap.',
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        $sections = [
            ['title' => '01 • Fondasi Fotografi', 'description' => 'Mengenal kamera dan fondasi teknis sebelum praktik.', 'position' => 1],
            ['title' => '02 • Exposure Triangle', 'description' => 'Aperture, shutter speed, ISO, dan hubungan ketiganya.', 'position' => 2],
            ['title' => '03 • Komposisi & Praktik', 'description' => 'Menerapkan exposure dan komposisi pada situasi nyata.', 'position' => 3],
        ];

        $sectionModels = [];
        foreach ($sections as $data) {
            $sectionModels[$data['position']] = CourseSection::updateOrCreate(
                ['classroom_id' => $classroom->id, 'position' => $data['position']],
                [...$data, 'is_published' => true]
            );
        }

        $materials = [
            [1, 1, 'Anatomi Kamera Modern', 'text', 'Kenali bagian kamera dan fungsi kontrol utama.', "Kamera modern terdiri dari body, sensor, mount lensa, viewfinder, layar, shutter, serta tombol kontrol.\n\nFokuskan pemahaman pada fungsi setiap kontrol agar pengaturan manual menjadi lebih cepat saat praktik.", null, 15, true],
            [1, 2, 'Panduan Mode Manual', 'link', 'Referensi singkat sebelum praktik di lapangan.', null, 'https://www.canon-europe.com/get-inspired/tips-and-techniques/manual-mode/', 12, true],
            [2, 1, 'Memahami Exposure Triangle', 'video', 'Hubungan aperture, shutter speed, dan ISO. Guru dapat mengganti sumber video demo ini dari Course Builder.', null, null, 20, true],
            [2, 2, 'Ringkasan Exposure Triangle', 'text', 'Catatan inti untuk mengingat dampak setiap parameter.', "Aperture memengaruhi cahaya dan depth of field.\nShutter speed memengaruhi cahaya dan representasi gerak.\nISO meningkatkan sensitivitas sekaligus berpotensi menambah noise.", null, 10, true],
            [3, 1, 'Latihan Foto Produk', 'text', 'Brief praktik menggunakan mode manual.', "Ambil tiga foto produk di lingkungan sekolah. Gunakan tiga kombinasi exposure berbeda dan catat alasan pemilihan aperture, shutter speed, serta ISO.", null, 30, true],
            [3, 2, 'Komposisi & Framing', 'text', 'Rule of thirds, leading lines, balance, dan visual hierarchy.', "Komposisi membantu mengarahkan perhatian penonton. Gunakan rule of thirds sebagai titik awal, bukan aturan mutlak. Perhatikan ruang negatif, arah pandang, dan keseimbangan visual.", null, 18, false],
        ];

        $created = [];
        foreach ($materials as [$sectionNo, $position, $title, $type, $summary, $content, $url, $duration, $published]) {
            $created[] = LearningMaterial::updateOrCreate(
                ['course_section_id' => $sectionModels[$sectionNo]->id, 'position' => $position],
                [
                    'title' => $title,
                    'type' => $type,
                    'summary' => $summary,
                    'content' => $content,
                    'external_url' => $url,
                    'duration_minutes' => $duration,
                    'is_preview' => $position === 1 && $sectionNo === 1,
                    'is_published' => $published,
                    'published_at' => $published ? now() : null,
                ]
            );
        }

        foreach (array_slice($created, 0, 2) as $material) {
            MaterialProgress::updateOrCreate(
                ['learning_material_id' => $material->id, 'student_id' => $student->id],
                ['status' => 'completed', 'progress_percent' => 100, 'first_opened_at' => now()->subDays(3), 'last_opened_at' => now()->subDays(2), 'completed_at' => now()->subDays(2)]
            );
        }

        if (isset($created[2])) {
            MaterialProgress::updateOrCreate(
                ['learning_material_id' => $created[2]->id, 'student_id' => $student->id],
                ['status' => 'in_progress', 'progress_percent' => 70, 'last_position_seconds' => 504, 'first_opened_at' => now()->subDay(), 'last_opened_at' => now()]
            );
        }
    }
}
