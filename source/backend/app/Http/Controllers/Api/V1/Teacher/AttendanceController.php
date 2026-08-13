<?php
namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Services\AttendanceService;
use App\Services\EduNovaNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance, private EduNovaNotificationService $notifications) {}

    private function ownedClassroom(Request $request, int $id): Classroom
    {
        return Classroom::where('school_id',$request->user()->school_id)
            ->whereHas('teachingAssignment',fn($q)=>$q->where('teacher_id',$request->user()->id))
            ->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name'])
            ->findOrFail($id);
    }

    private function ownedSession(Request $request, int $id): AttendanceSession
    {
        return AttendanceSession::where('school_id',$request->user()->school_id)
            ->whereHas('classroom.teachingAssignment',fn($q)=>$q->where('teacher_id',$request->user()->id))
            ->with(['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name'])
            ->findOrFail($id);
    }

    public function classrooms(Request $request): JsonResponse
    {
        $items=Classroom::where('school_id',$request->user()->school_id)->whereHas('teachingAssignment',fn($q)=>$q->where('teacher_id',$request->user()->id))->with(['teachingAssignment.subject:id,code,name','teachingAssignment.classGroup:id,name'])->orderBy('title')->get();
        return response()->json(['data'=>$items]);
    }

    public function index(Request $request): JsonResponse
    {
        $items=AttendanceSession::where('school_id',$request->user()->school_id)->whereHas('classroom.teachingAssignment',fn($q)=>$q->where('teacher_id',$request->user()->id))->with(['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name'])->withCount('records')->latest('opens_at')->limit(60)->get();
        return response()->json(['data'=>$items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data=$request->validate([
            'classroom_id'=>['required','integer'],'title'=>['required','string','max:180'],
            'opens_at'=>['required','date'],'late_after'=>['nullable','date'],'closes_at'=>['required','date','after:opens_at'],
            'allow_manual'=>['sometimes','boolean'],'require_gps'=>['sometimes','boolean'],'latitude'=>['nullable','numeric','between:-90,90'],'longitude'=>['nullable','numeric','between:-180,180'],'radius_meters'=>['nullable','integer','min:10','max:5000'],
            'require_dynamic_qr'=>['sometimes','boolean'],'qr_rotation_seconds'=>['nullable','integer','min:10','max:300'],'require_selfie'=>['sometimes','boolean'],
        ]);
        $classroom=$this->ownedClassroom($request,(int)$data['classroom_id']);
        if(($data['require_gps']??false) && (!isset($data['latitude'],$data['longitude']))) return response()->json(['message'=>'Koordinat wajib diisi jika GPS diwajibkan.'],422);
        $item=AttendanceSession::create([
            'school_id'=>$request->user()->school_id,'classroom_id'=>$classroom->id,'created_by'=>$request->user()->id,'title'=>$data['title'],
            'opens_at'=>$data['opens_at'],'late_after'=>$data['late_after']??null,'closes_at'=>$data['closes_at'],'status'=>'draft',
            'allow_manual'=>$data['allow_manual']??true,'require_gps'=>$data['require_gps']??false,'latitude'=>$data['latitude']??null,'longitude'=>$data['longitude']??null,'radius_meters'=>$data['radius_meters']??100,
            'require_dynamic_qr'=>$data['require_dynamic_qr']??false,'qr_rotation_seconds'=>$data['qr_rotation_seconds']??30,'qr_secret'=>Str::random(64),'require_selfie'=>$data['require_selfie']??false,
        ]);
        return response()->json(['data'=>$item->load(['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name'])],201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $session=$this->ownedSession($request,$id);
        $classGroupId=$session->classroom?->teachingAssignment?->class_group_id;
        $students=ClassEnrollment::where('class_group_id',$classGroupId)->where('status','active')->with('student:id,name,username')->get()->pluck('student');
        $records=AttendanceRecord::where('attendance_session_id',$session->id)->with('student:id,name,username')->get()->keyBy('student_id');
        $roster=$students->map(fn($student)=>['student'=>$student,'record'=>$records->get($student->id)]);
        return response()->json(['data'=>['session'=>$session,'roster'=>$roster,'summary'=>$this->summary($records->values())]]);
    }

    public function start(Request $request, int $id): JsonResponse
    {
        $session=$this->ownedSession($request,$id);
        $session->update(['status'=>'open','closed_at'=>null]);
        $this->notifications->notifyClassroomStudents($session->classroom,'attendance','Absensi dibuka: '.$session->title,'Silakan lakukan check-in sesuai metode yang ditetapkan guru.','/attendance',['attendance_session_id'=>$session->id]);
        return response()->json(['data'=>$session->fresh()]);
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $session=$this->ownedSession($request,$id);
        DB::transaction(function() use($session,$request){
            $classGroupId=$session->classroom?->teachingAssignment?->class_group_id;
            $studentIds=ClassEnrollment::where('class_group_id',$classGroupId)->where('status','active')->pluck('student_id');
            foreach($studentIds as $studentId){AttendanceRecord::firstOrCreate(['attendance_session_id'=>$session->id,'student_id'=>$studentId],['status'=>'absent','method'=>'system','verified_by'=>$request->user()->id,'note'=>'Tidak ada check-in sampai sesi ditutup.']);}
            $session->update(['status'=>'closed','closed_at'=>now()]);
        });
        return response()->json(['data'=>$session->fresh()]);
    }

    public function qr(Request $request, int $id): JsonResponse
    {
        $session=$this->ownedSession($request,$id);
        abort_unless($session->require_dynamic_qr,404);
        return response()->json(['data'=>$this->attendance->qrPayload($session)]);
    }

    public function manualRecord(Request $request, int $id): JsonResponse
    {
        $session=$this->ownedSession($request,$id);
        abort_unless($session->allow_manual,422,'Sesi ini tidak mengizinkan absensi manual.');
        $data=$request->validate(['student_id'=>['required','integer'],'status'=>['required','in:present,late,sick,permission,absent,dispensation'],'note'=>['nullable','string','max:2000']]);
        $classGroupId=$session->classroom?->teachingAssignment?->class_group_id;
        abort_unless(ClassEnrollment::where('class_group_id',$classGroupId)->where('student_id',$data['student_id'])->where('status','active')->exists(),422,'Siswa tidak terdaftar pada kelas ini.');
        $record=AttendanceRecord::updateOrCreate(['attendance_session_id'=>$session->id,'student_id'=>$data['student_id']],['status'=>$data['status'],'method'=>'manual','checked_in_at'=>in_array($data['status'],['present','late'])?now():null,'verified_by'=>$request->user()->id,'note'=>$data['note']??null]);
        return response()->json(['data'=>$record->load('student:id,name,username')]);
    }

    private function summary($records): array
    {
        $statuses=['present','late','sick','permission','absent','dispensation']; $out=['total'=>$records->count()]; foreach($statuses as $status)$out[$status]=$records->where('status',$status)->count(); return $out;
    }
}
