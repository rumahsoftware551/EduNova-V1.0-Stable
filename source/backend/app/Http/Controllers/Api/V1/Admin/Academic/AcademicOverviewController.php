<?php
namespace App\Http\Controllers\Api\V1\Admin\Academic;
use App\Http\Controllers\Controller;
use App\Models\{AcademicYear,ClassGroup,Major,Subject,User};
use Illuminate\Http\JsonResponse;
class AcademicOverviewController extends Controller
{
 public function __invoke(): JsonResponse { $schoolId=request()->user()->school_id; return response()->json(['data'=>[
  'academic_years'=>AcademicYear::where('school_id',$schoolId)->count(), 'classes'=>ClassGroup::where('school_id',$schoolId)->count(), 'majors'=>Major::where('school_id',$schoolId)->count(), 'subjects'=>Subject::where('school_id',$schoolId)->count(), 'teachers'=>User::where('school_id',$schoolId)->where('role','teacher')->count(), 'students'=>User::where('school_id',$schoolId)->where('role','student')->count(),
 ]]); }
}
