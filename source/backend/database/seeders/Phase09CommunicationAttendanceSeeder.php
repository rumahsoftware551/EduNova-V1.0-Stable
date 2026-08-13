<?php
namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\DiscussionPost;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Phase09CommunicationAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $classroom=Classroom::with('teachingAssignment')->first();
        if(!$classroom) return;
        $teacher=User::where('id',$classroom->teachingAssignment?->teacher_id)->first() ?? User::where('role','teacher')->first();
        $studentId=ClassEnrollment::where('class_group_id',$classroom->teachingAssignment?->class_group_id)->where('status','active')->value('student_id');
        $student=$studentId?User::find($studentId):User::where('role','student')->first();
        if(!$teacher||!$student) return;

        $announcement=Announcement::firstOrCreate(
            ['classroom_id'=>$classroom->id,'title'=>'Persiapan Praktik Fotografi Minggu Ini'],
            ['author_id'=>$teacher->id,'body'=>'Pastikan baterai kamera terisi, kartu memori kosong, dan baca kembali materi Exposure Triangle sebelum praktik.','status'=>'published','is_pinned'=>true,'published_at'=>now()]
        );

        DiscussionPost::firstOrCreate(
            ['classroom_id'=>$classroom->id,'user_id'=>$teacher->id,'body'=>'Bagian mana dari Exposure Triangle yang masih paling membingungkan?'],
            ['parent_id'=>null]
        );
        DiscussionPost::firstOrCreate(
            ['classroom_id'=>$classroom->id,'user_id'=>$student->id,'body'=>'Saya masih perlu contoh kapan harus menaikkan ISO dibanding memperlambat shutter speed.'],
            ['parent_id'=>null]
        );

        UserNotification::firstOrCreate(
            ['user_id'=>$student->id,'type'=>'announcement','title'=>'Pengumuman baru: '.$announcement->title],
            ['body'=>'Buka pusat komunikasi untuk membaca informasi dari guru.','action_url'=>'/communications','data'=>['classroom_id'=>$classroom->id,'announcement_id'=>$announcement->id]]
        );

        $open=AttendanceSession::firstOrCreate(
            ['classroom_id'=>$classroom->id,'title'=>'Absensi Pertemuan Hari Ini'],
            ['school_id'=>$classroom->school_id,'created_by'=>$teacher->id,'opens_at'=>now()->subMinutes(5),'late_after'=>now()->addMinutes(15),'closes_at'=>now()->addMinutes(45),'status'=>'open','allow_manual'=>true,'require_gps'=>false,'radius_meters'=>100,'require_dynamic_qr'=>true,'qr_rotation_seconds'=>30,'qr_secret'=>Str::random(64),'require_selfie'=>false]
        );
        if(!$open->qr_secret){$open->update(['qr_secret'=>Str::random(64)]);}

        $history=AttendanceSession::firstOrCreate(
            ['classroom_id'=>$classroom->id,'title'=>'Absensi Pertemuan Sebelumnya'],
            ['school_id'=>$classroom->school_id,'created_by'=>$teacher->id,'opens_at'=>now()->subDay()->setTime(7,0),'late_after'=>now()->subDay()->setTime(7,15),'closes_at'=>now()->subDay()->setTime(8,0),'status'=>'closed','allow_manual'=>true,'require_gps'=>false,'radius_meters'=>100,'require_dynamic_qr'=>false,'qr_rotation_seconds'=>30,'qr_secret'=>Str::random(64),'require_selfie'=>false,'closed_at'=>now()->subDay()->setTime(8,0)]
        );
        AttendanceRecord::firstOrCreate(
            ['attendance_session_id'=>$history->id,'student_id'=>$student->id],
            ['status'=>'present','method'=>'manual','checked_in_at'=>now()->subDay()->setTime(7,4),'verified_by'=>$teacher->id,'note'=>'Data demo Phase 09']
        );
    }
}
