<?php
namespace Database\Seeders;
use App\Models\Assignment; use App\Models\AssignmentSubmission; use App\Models\Classroom; use App\Models\RubricCriterion; use App\Models\RubricScore; use App\Models\User; use Illuminate\Database\Seeder;
class Phase06AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $teacher=User::where('username','susanto')->firstOrFail();$student=User::where('username','andi.saputra')->firstOrFail();
        $classroom=Classroom::where('created_by',$teacher->id)->where('status','published')->firstOrFail();
        $a1=Assignment::updateOrCreate(['classroom_id'=>$classroom->id,'title'=>'Tugas 01 — Simulasi Segitiga Exposure'],[
            'created_by'=>$teacher->id,'instructions'=>"Ambil tiga foto objek yang sama dengan kombinasi aperture, shutter speed, dan ISO berbeda. Jelaskan alasan pemilihan setting pada setiap foto.\n\nKumpulkan file foto dan refleksi singkat.",'opens_at'=>now()->subDay(),'due_at'=>now()->addDays(7)->setTime(23,59),'max_score'=>100,'allow_text'=>true,'allow_link'=>true,'allow_file'=>true,'allow_resubmit'=>true,'max_file_mb'=>20,'status'=>'published','published_at'=>now()->subDay()]);
        $criteria=[['Teknis exposure','Ketepatan exposure dan kontrol highlight/shadow.',40],['Komposisi','Penerapan framing dan visual hierarchy.',30],['Refleksi','Kejelasan alasan pemilihan setting kamera.',30]];
        foreach($criteria as $i=>[$title,$description,$points]) RubricCriterion::updateOrCreate(['assignment_id'=>$a1->id,'position'=>$i+1],['title'=>$title,'description'=>$description,'max_points'=>$points]);
        $a2=Assignment::updateOrCreate(['classroom_id'=>$classroom->id,'title'=>'Analisis Komposisi Foto'],[
            'created_by'=>$teacher->id,'instructions'=>'Pilih satu foto terbaik hasil praktik. Analisis rule of thirds, balance, leading lines, dan focal point dalam 200–300 kata.','opens_at'=>now()->subDays(10),'due_at'=>now()->subDays(2)->setTime(23,59),'max_score'=>100,'allow_text'=>true,'allow_link'=>false,'allow_file'=>false,'allow_resubmit'=>true,'max_file_mb'=>10,'status'=>'published','published_at'=>now()->subDays(10)]);
        $submission=AssignmentSubmission::updateOrCreate(['assignment_id'=>$a2->id,'student_id'=>$student->id],[
            'status'=>'returned','text_answer'=>'Foto menggunakan leading lines dari koridor sekolah untuk mengarahkan perhatian ke subjek utama. Posisi subjek ditempatkan pada titik sepertiga kanan dan ruang negatif digunakan untuk menjaga keseimbangan visual.','submitted_at'=>now()->subDays(3),'is_late'=>false,'attempt_no'=>1,'score'=>88,'feedback'=>'Analisis sudah kuat. Pada tugas berikutnya, jelaskan hubungan focal point dengan kontras cahaya secara lebih spesifik.','graded_by'=>$teacher->id,'graded_at'=>now()->subDay(),'returned_at'=>now()->subHours(20)]);
        $rubrics=[['Analisis visual','Ketepatan membaca elemen komposisi.',60],['Argumentasi','Kejelasan alasan dan penggunaan istilah visual.',40]];
        foreach($rubrics as $i=>[$title,$description,$points]){$c=RubricCriterion::updateOrCreate(['assignment_id'=>$a2->id,'position'=>$i+1],['title'=>$title,'description'=>$description,'max_points'=>$points]);RubricScore::updateOrCreate(['submission_id'=>$submission->id,'rubric_criterion_id'=>$c->id],['points'=>$i===0?52:36,'comment'=>$i===0?'Pembacaan elemen visual tepat.':'Argumentasi jelas dan runtut.']);}
        Assignment::updateOrCreate(['classroom_id'=>$classroom->id,'title'=>'Brief Foto Produk — Draft'],[
            'created_by'=>$teacher->id,'instructions'=>'Tugas ini masih draft dan belum terlihat oleh siswa.','due_at'=>now()->addDays(14),'max_score'=>100,'allow_text'=>true,'allow_link'=>true,'allow_file'=>true,'allow_resubmit'=>true,'max_file_mb'=>20,'status'=>'draft']);
    }
}
