<?php
namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassEnrollment;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance) {}

    private function classGroupIds(Request $request)
    {
        return ClassEnrollment::where('student_id',$request->user()->id)->where('status','active')->pluck('class_group_id');
    }

    private function accessible(Request $request)
    {
        $ids=$this->classGroupIds($request);
        return AttendanceSession::where('school_id',$request->user()->school_id)->whereHas('classroom.teachingAssignment',fn($q)=>$q->whereIn('class_group_id',$ids));
    }

    public function active(Request $request): JsonResponse
    {
        $now=now();
        $items=$this->accessible($request)->where('status','open')->where('opens_at','<=',$now)->where('closes_at','>=',$now)->with(['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name'])->latest('opens_at')->get();
        $records=AttendanceRecord::where('student_id',$request->user()->id)->whereIn('attendance_session_id',$items->pluck('id'))->get()->keyBy('attendance_session_id');
        $items->each(fn($session)=>$session->setAttribute('my_record',$records->get($session->id)));
        return response()->json(['data'=>$items]);
    }

    public function history(Request $request): JsonResponse
    {
        $items=AttendanceRecord::where('student_id',$request->user()->id)->with(['session.classroom.teachingAssignment.subject:id,code,name','session.classroom.teachingAssignment.classGroup:id,name'])->latest('checked_in_at')->limit(80)->get();
        return response()->json(['data'=>$items]);
    }

    public function checkIn(Request $request, int $id): JsonResponse
    {
        $session=$this->accessible($request)->with('classroom')->findOrFail($id);
        $now=now();
        if($session->status!=='open'||$now->lt($session->opens_at)||$now->gt($session->closes_at)) return response()->json(['message'=>'Sesi absensi belum dibuka atau sudah ditutup.'],422);
        if(AttendanceRecord::where('attendance_session_id',$session->id)->where('student_id',$request->user()->id)->whereNotIn('status',['absent'])->exists()) return response()->json(['message'=>'Anda sudah tercatat pada sesi ini.'],422);

        $rules=['qr_token'=>['nullable','string','max:100'],'latitude'=>['nullable','numeric','between:-90,90'],'longitude'=>['nullable','numeric','between:-180,180'],'gps_accuracy'=>['nullable','numeric','min:0','max:10000'],'selfie'=>['nullable','image','max:8192']];
        $data=$request->validate($rules);

        if(!$this->attendance->validateQrToken($session,$data['qr_token']??null,$now)) return response()->json(['message'=>'QR/code sudah tidak valid. Minta kode terbaru dari guru.'],422);

        $distance=null;
        if($session->require_gps){
            if(!isset($data['latitude'],$data['longitude'])) return response()->json(['message'=>'Lokasi GPS wajib untuk sesi ini.'],422);
            $distance=$this->attendance->distanceMeters((float)$session->latitude,(float)$session->longitude,(float)$data['latitude'],(float)$data['longitude']);
            if($distance>(float)$session->radius_meters) return response()->json(['message'=>'Anda berada di luar radius absensi.','distance_meters'=>round($distance,1),'allowed_radius_meters'=>$session->radius_meters],422);
        }

        if($session->require_selfie && !$request->hasFile('selfie')) return response()->json(['message'=>'Selfie wajib untuk sesi ini.'],422);
        $selfiePath=$request->hasFile('selfie')?$request->file('selfie')->store('attendance/selfies/'.$session->id,'public'):null;
        $status=$session->late_after && $now->gt($session->late_after)?'late':'present';
        $methods=[]; if($session->require_dynamic_qr)$methods[]='qr'; if($session->require_gps)$methods[]='gps'; if($session->require_selfie)$methods[]='selfie'; if(!$methods)$methods[]='web';

        $record=AttendanceRecord::updateOrCreate(['attendance_session_id'=>$session->id,'student_id'=>$request->user()->id],[
            'status'=>$status,'method'=>implode('+',$methods),'checked_in_at'=>$now,'latitude'=>$data['latitude']??null,'longitude'=>$data['longitude']??null,'distance_meters'=>$distance,'gps_accuracy'=>$data['gps_accuracy']??null,'selfie_path'=>$selfiePath,'verified_by'=>null,'note'=>null,'meta'=>['ip'=>$request->ip(),'user_agent'=>mb_strimwidth((string)$request->userAgent(),0,500,'')],
        ]);
        return response()->json(['data'=>$record]);
    }
}
