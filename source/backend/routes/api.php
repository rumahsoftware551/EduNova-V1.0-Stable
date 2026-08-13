<?php

use App\Http\Controllers\Api\V1\CurrentUserController;
use App\Http\Controllers\Api\V1\MaterialFileController;
use App\Http\Controllers\Api\V1\AssignmentFileController;
use App\Http\Controllers\Api\V1\Admin\Academic\{AcademicOverviewController,AcademicYearController,ClassGroupController,EnrollmentController,MajorController,PeopleController,SemesterController,SubjectController,TeachingAssignmentController};
use App\Http\Controllers\Api\V1\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Api\V1\Teacher\CourseSectionController as TeacherCourseSectionController;
use App\Http\Controllers\Api\V1\Teacher\LearningMaterialController as TeacherLearningMaterialController;
use App\Http\Controllers\Api\V1\Student\ClassroomController as StudentClassroomController;
use App\Http\Controllers\Api\V1\Student\MaterialController as StudentMaterialController;
use App\Http\Controllers\Api\V1\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Api\V1\Teacher\RubricController as TeacherRubricController;
use App\Http\Controllers\Api\V1\Teacher\SubmissionReviewController as TeacherSubmissionReviewController;
use App\Http\Controllers\Api\V1\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Api\V1\Teacher\QuizController as TeacherQuizController;
use App\Http\Controllers\Api\V1\Teacher\QuestionBankController as TeacherQuestionBankController;
use App\Http\Controllers\Api\V1\Teacher\QuizAttemptController as TeacherQuizAttemptController;
use App\Http\Controllers\Api\V1\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Api\V1\Teacher\GradebookController as TeacherGradebookController;
use App\Http\Controllers\Api\V1\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Api\V1\Admin\LearningAnalyticsController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\AttendanceEvidenceController;
use App\Http\Controllers\Api\V1\Teacher\CommunicationController as TeacherCommunicationController;
use App\Http\Controllers\Api\V1\Student\CommunicationController as StudentCommunicationController;
use App\Http\Controllers\Api\V1\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Api\V1\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Api\V1\Admin\AttendanceAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status'=>'ok','service'=>'EduNova API','version'=>'1.0.0','phase'=>10]));

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', CurrentUserController::class);
        Route::get('/material-files/{id}', MaterialFileController::class);
        Route::get('/assignment-files/{kind}/{id}', AssignmentFileController::class)->where('kind','attachment|submission');
        Route::get('/student/ping', fn()=>response()->json(['message'=>'student-ok']))->middleware('role:student');
        Route::get('/teacher/ping', fn()=>response()->json(['message'=>'teacher-ok']))->middleware('role:teacher');
        Route::get('/admin/ping', fn()=>response()->json(['message'=>'admin-ok']))->middleware('role:admin');

        Route::get('/notifications', [NotificationController::class,'index']);
        Route::post('/notifications/read-all', [NotificationController::class,'readAll']);
        Route::post('/notifications/{id}/read', [NotificationController::class,'read']);
        Route::get('/attendance-evidence/{recordId}', AttendanceEvidenceController::class);

        Route::prefix('admin/academic')->middleware('role:admin')->group(function () {
            Route::get('/overview', AcademicOverviewController::class);
            Route::apiResource('academic-years', AcademicYearController::class)->except(['show']);
            Route::apiResource('semesters', SemesterController::class)->except(['show']);
            Route::apiResource('majors', MajorController::class)->except(['show']);
            Route::apiResource('classes', ClassGroupController::class)->except(['show']);
            Route::apiResource('subjects', SubjectController::class)->except(['show']);
            Route::get('/students', [PeopleController::class,'students']);
            Route::post('/students', [PeopleController::class,'storeStudent']);
            Route::put('/students/{id}', [PeopleController::class,'updateStudent']);
            Route::delete('/students/{id}', [PeopleController::class,'destroyStudent']);
            Route::get('/teachers', [PeopleController::class,'teachers']);
            Route::post('/teachers', [PeopleController::class,'storeTeacher']);
            Route::put('/teachers/{id}', [PeopleController::class,'updateTeacher']);
            Route::delete('/teachers/{id}', [PeopleController::class,'destroyTeacher']);
            Route::get('/enrollments', [EnrollmentController::class,'index']);
            Route::post('/enrollments', [EnrollmentController::class,'store']);
            Route::delete('/enrollments/{id}', [EnrollmentController::class,'destroy']);
            Route::get('/teaching-assignments', [TeachingAssignmentController::class,'index']);
            Route::post('/teaching-assignments', [TeachingAssignmentController::class,'store']);
            Route::delete('/teaching-assignments/{id}', [TeachingAssignmentController::class,'destroy']);
            Route::get('/learning-analytics', LearningAnalyticsController::class);
            Route::get('/attendance-analytics', AttendanceAnalyticsController::class);
        });

        Route::prefix('teacher')->middleware('role:teacher')->group(function () {
            Route::get('/classrooms/meta', [TeacherClassroomController::class, 'meta']);
            Route::get('/classrooms', [TeacherClassroomController::class, 'index']);
            Route::post('/classrooms', [TeacherClassroomController::class, 'store']);
            Route::get('/classrooms/{id}', [TeacherClassroomController::class, 'show']);
            Route::put('/classrooms/{id}', [TeacherClassroomController::class, 'update']);
            Route::post('/classrooms/{id}/publish', [TeacherClassroomController::class, 'publish']);
            Route::delete('/classrooms/{id}', [TeacherClassroomController::class, 'destroy']);

            Route::post('/classrooms/{classroomId}/sections', [TeacherCourseSectionController::class, 'store']);
            Route::put('/sections/{id}', [TeacherCourseSectionController::class, 'update']);
            Route::delete('/sections/{id}', [TeacherCourseSectionController::class, 'destroy']);

            Route::post('/sections/{sectionId}/materials', [TeacherLearningMaterialController::class, 'store']);
            Route::post('/materials/{id}', [TeacherLearningMaterialController::class, 'update']);
            Route::post('/materials/{id}/publish', [TeacherLearningMaterialController::class, 'publish']);
            Route::delete('/materials/{id}', [TeacherLearningMaterialController::class, 'destroy']);


            Route::get('/assignments', [TeacherAssignmentController::class, 'index']);
            Route::post('/classrooms/{classroomId}/assignments', [TeacherAssignmentController::class, 'store']);
            Route::get('/assignments/{id}', [TeacherAssignmentController::class, 'show']);
            Route::put('/assignments/{id}', [TeacherAssignmentController::class, 'update']);
            Route::post('/assignments/{id}/publish', [TeacherAssignmentController::class, 'publish']);
            Route::delete('/assignments/{id}', [TeacherAssignmentController::class, 'destroy']);
            Route::post('/assignments/{id}/attachments', [TeacherAssignmentController::class, 'addAttachment']);
            Route::delete('/assignment-attachments/{attachmentId}', [TeacherAssignmentController::class, 'removeAttachment']);
            Route::put('/assignments/{id}/rubric', [TeacherRubricController::class, 'sync']);
            Route::get('/assignments/{assignmentId}/submissions', [TeacherSubmissionReviewController::class, 'index']);
            Route::get('/submissions/{id}', [TeacherSubmissionReviewController::class, 'show']);
            Route::post('/submissions/{id}/grade', [TeacherSubmissionReviewController::class, 'grade']);
            Route::post('/submissions/{id}/return', [TeacherSubmissionReviewController::class, 'returnToStudent']);

            Route::get('/quizzes', [TeacherQuizController::class, 'index']);
            Route::post('/classrooms/{classroomId}/quizzes', [TeacherQuizController::class, 'store']);
            Route::get('/quizzes/{id}', [TeacherQuizController::class, 'show']);
            Route::put('/quizzes/{id}', [TeacherQuizController::class, 'update']);
            Route::post('/quizzes/{id}/publish', [TeacherQuizController::class, 'publish']);
            Route::delete('/quizzes/{id}', [TeacherQuizController::class, 'destroy']);
            Route::get('/question-bank', [TeacherQuestionBankController::class, 'index']);
            Route::post('/question-bank', [TeacherQuestionBankController::class, 'store']);
            Route::put('/question-bank/{id}', [TeacherQuestionBankController::class, 'update']);
            Route::delete('/question-bank/{id}', [TeacherQuestionBankController::class, 'destroy']);
            Route::post('/quizzes/{quizId}/questions', [TeacherQuestionBankController::class, 'createForQuiz']);
            Route::post('/quizzes/{quizId}/questions/from-bank', [TeacherQuestionBankController::class, 'addToQuiz']);
            Route::delete('/quizzes/{quizId}/questions/{quizQuestionId}', [TeacherQuestionBankController::class, 'removeFromQuiz']);
            Route::get('/quizzes/{quizId}/attempts', [TeacherQuizAttemptController::class, 'index']);
            Route::get('/quiz-attempts/{id}', [TeacherQuizAttemptController::class, 'show']);
            Route::post('/quiz-attempts/{id}/grade', [TeacherQuizAttemptController::class, 'grade']);
            Route::post('/quizzes/{quizId}/release-results', [TeacherQuizAttemptController::class, 'release']);

            Route::get('/gradebooks', [TeacherGradebookController::class, 'index']);
            Route::get('/gradebooks/{classroomId}', [TeacherGradebookController::class, 'show']);
            Route::put('/gradebooks/{classroomId}/settings', [TeacherGradebookController::class, 'updateSettings']);
            Route::put('/gradebooks/{classroomId}/students/{studentId}/adjustment', [TeacherGradebookController::class, 'adjust']);
            Route::post('/gradebooks/{classroomId}/publish', [TeacherGradebookController::class, 'publish']);
            Route::post('/gradebooks/{classroomId}/unpublish', [TeacherGradebookController::class, 'unpublish']);

            Route::get('/communications/classrooms', [TeacherCommunicationController::class,'classrooms']);
            Route::get('/communications/classrooms/{classroomId}/announcements', [TeacherCommunicationController::class,'announcements']);
            Route::post('/communications/classrooms/{classroomId}/announcements', [TeacherCommunicationController::class,'storeAnnouncement']);
            Route::post('/communications/announcements/{id}/publish', [TeacherCommunicationController::class,'publishAnnouncement']);
            Route::delete('/communications/announcements/{id}', [TeacherCommunicationController::class,'destroyAnnouncement']);
            Route::get('/communications/classrooms/{classroomId}/discussion', [TeacherCommunicationController::class,'discussion']);
            Route::post('/communications/classrooms/{classroomId}/discussion', [TeacherCommunicationController::class,'postDiscussion']);

            Route::get('/attendance/classrooms', [TeacherAttendanceController::class,'classrooms']);
            Route::get('/attendance/sessions', [TeacherAttendanceController::class,'index']);
            Route::post('/attendance/sessions', [TeacherAttendanceController::class,'store']);
            Route::get('/attendance/sessions/{id}', [TeacherAttendanceController::class,'show']);
            Route::post('/attendance/sessions/{id}/start', [TeacherAttendanceController::class,'start']);
            Route::post('/attendance/sessions/{id}/close', [TeacherAttendanceController::class,'close']);
            Route::get('/attendance/sessions/{id}/qr', [TeacherAttendanceController::class,'qr']);
            Route::put('/attendance/sessions/{id}/records', [TeacherAttendanceController::class,'manualRecord']);
        });

        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('/classrooms', [StudentClassroomController::class, 'index']);
            Route::get('/classrooms/{id}', [StudentClassroomController::class, 'show']);
            Route::get('/materials/{id}', [StudentMaterialController::class, 'show']);
            Route::post('/materials/{id}/progress', [StudentMaterialController::class, 'progress']);

            Route::get('/assignments', [StudentAssignmentController::class, 'index']);
            Route::get('/assignments/{id}', [StudentAssignmentController::class, 'show']);
            Route::post('/assignments/{id}/submission', [StudentAssignmentController::class, 'save']);
            Route::delete('/submission-files/{fileId}', [StudentAssignmentController::class, 'removeFile']);

            Route::get('/quizzes', [StudentQuizController::class, 'index']);
            Route::get('/quizzes/{id}', [StudentQuizController::class, 'show']);
            Route::post('/quizzes/{id}/start', [StudentQuizController::class, 'start']);
            Route::get('/quiz-attempts/{attemptId}', [StudentQuizController::class, 'attempt']);
            Route::post('/quiz-attempts/{attemptId}/answer', [StudentQuizController::class, 'saveAnswer']);
            Route::post('/quiz-attempts/{attemptId}/submit', [StudentQuizController::class, 'submit']);
            Route::get('/quiz-attempts/{attemptId}/result', [StudentQuizController::class, 'result']);

            Route::get('/grades', [StudentGradeController::class, 'index']);
            Route::get('/grades/{classroomId}', [StudentGradeController::class, 'show']);

            Route::get('/communications/classrooms', [StudentCommunicationController::class,'classrooms']);
            Route::get('/communications/classrooms/{classroomId}/announcements', [StudentCommunicationController::class,'announcements']);
            Route::get('/communications/classrooms/{classroomId}/discussion', [StudentCommunicationController::class,'discussion']);
            Route::post('/communications/classrooms/{classroomId}/discussion', [StudentCommunicationController::class,'postDiscussion']);

            Route::get('/attendance/active', [StudentAttendanceController::class,'active']);
            Route::get('/attendance/history', [StudentAttendanceController::class,'history']);
            Route::post('/attendance/sessions/{id}/check-in', [StudentAttendanceController::class,'checkIn']);
        });
    });
});
