<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceAnalyticsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $schoolId=$request->user()->school_id;
        $sessionIds=AttendanceSession::where('school_id',$schoolId)->pluck('id');
        $today=now()->startOfDay();
        $records=AttendanceRecord::whereIn('attendance_session_id',$sessionIds)->where('created_at','>=',$today)->get();
        $monthRecords=AttendanceRecord::whereIn('attendance_session_id',$sessionIds)->where('created_at','>=',now()->startOfMonth())->get();
        $latest=AttendanceSession::where('school_id',$schoolId)->with(['classroom.teachingAssignment.subject:id,code,name','classroom.teachingAssignment.classGroup:id,name'])->withCount('records')->latest('opens_at')->limit(12)->get();
        $summary=function($set){return ['records'=>$set->count(),'present'=>$set->where('status','present')->count(),'late'=>$set->where('status','late')->count(),'sick'=>$set->where('status','sick')->count(),'permission'=>$set->where('status','permission')->count(),'absent'=>$set->where('status','absent')->count(),'dispensation'=>$set->where('status','dispensation')->count()];};
        return response()->json(['data'=>['today'=>$summary($records),'month'=>$summary($monthRecords),'sessions_total'=>AttendanceSession::where('school_id',$schoolId)->count(),'latest_sessions'=>$latest]]);
    }
}
