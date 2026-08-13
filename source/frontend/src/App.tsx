import { lazy, Suspense } from 'react'
import { Route, Routes } from 'react-router'
import Login from '@/pages/Login'
import { PublicOnly, RequireAuth, RequireRole, RootRedirect } from '@/features/auth/RouteGuards'

const StudentDashboard = lazy(() => import('@/pages/StudentDashboard'))
const TeacherDashboard = lazy(() => import('@/pages/TeacherDashboard'))
const AdminDashboard = lazy(() => import('@/pages/AdminDashboard'))
const AdminAcademic = lazy(() => import('@/pages/AdminAcademic'))
const TeacherClassrooms = lazy(() => import('@/pages/TeacherClassrooms'))
const TeacherClassroomEditor = lazy(() => import('@/pages/TeacherClassroomEditor'))
const Courses = lazy(() => import('@/pages/Courses'))
const CourseDetail = lazy(() => import('@/pages/CourseDetail'))
const Material = lazy(() => import('@/pages/Material'))
const Assignments = lazy(() => import('@/pages/Assignments'))
const TeacherAssignmentEditor = lazy(() => import('@/pages/TeacherAssignmentEditor'))
const TeacherSubmissionReview = lazy(() => import('@/pages/TeacherSubmissionReview'))
const StudentAssignmentDetail = lazy(() => import('@/pages/StudentAssignmentDetail'))
const Quiz = lazy(() => import('@/pages/Quiz'))
const TeacherQuizEditor = lazy(() => import('@/pages/TeacherQuizEditor'))
const TeacherQuizAttemptReview = lazy(() => import('@/pages/TeacherQuizAttemptReview'))
const StudentQuizDetail = lazy(() => import('@/pages/StudentQuizDetail'))
const StudentQuizAttempt = lazy(() => import('@/pages/StudentQuizAttempt'))
const StudentQuizResult = lazy(() => import('@/pages/StudentQuizResult'))
const Calendar = lazy(() => import('@/pages/Calendar'))
const Grades = lazy(() => import('@/pages/Grades'))
const TeacherGradebookDetail = lazy(() => import('@/pages/TeacherGradebookDetail'))
const StudentGradeDetail = lazy(() => import('@/pages/StudentGradeDetail'))
const Profile = lazy(() => import('@/pages/Profile'))
const Communications = lazy(() => import('@/pages/Communications'))
const Attendance = lazy(() => import('@/pages/Attendance'))

function PageFallback() {
  return <div className="grid min-h-screen place-items-center bg-[#F6F8FB] px-6">
    <div className="text-center">
      <div className="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-blue-600" />
      <p className="mt-3 text-xs font-medium text-slate-500">Membuka halaman...</p>
    </div>
  </div>
}

export default function App(){return <Suspense fallback={<PageFallback/>}><Routes>
 <Route path="/" element={<RootRedirect/>}/><Route path="/login" element={<PublicOnly><Login/></PublicOnly>}/>
 <Route element={<RequireAuth/>}>
  <Route path="/student" element={<RequireRole role="student"><StudentDashboard/></RequireRole>}/>
  <Route path="/teacher" element={<RequireRole role="teacher"><TeacherDashboard/></RequireRole>}/>
  <Route path="/teacher/classrooms" element={<RequireRole role="teacher"><TeacherClassrooms/></RequireRole>}/>
  <Route path="/teacher/classrooms/:id" element={<RequireRole role="teacher"><TeacherClassroomEditor/></RequireRole>}/>
  <Route path="/admin" element={<RequireRole role="admin"><AdminDashboard/></RequireRole>}/>
  <Route path="/admin/academic" element={<RequireRole role="admin"><AdminAcademic/></RequireRole>}/>
  <Route path="/courses" element={<Courses/>}/>
  <Route path="/courses/:id" element={<RequireRole role="student"><CourseDetail/></RequireRole>}/>
  <Route path="/courses/:courseId/materials/:materialId" element={<RequireRole role="student"><Material/></RequireRole>}/>
  <Route path="/assignments" element={<Assignments/>}/><Route path="/assignments/:id" element={<RequireRole role="student"><StudentAssignmentDetail/></RequireRole>}/><Route path="/teacher/assignments/:id" element={<RequireRole role="teacher"><TeacherAssignmentEditor/></RequireRole>}/><Route path="/teacher/submissions/:id" element={<RequireRole role="teacher"><TeacherSubmissionReview/></RequireRole>}/><Route path="/quiz" element={<Quiz/>}/><Route path="/quiz/:id" element={<RequireRole role="student"><StudentQuizDetail/></RequireRole>}/><Route path="/quiz/attempts/:attemptId" element={<RequireRole role="student"><StudentQuizAttempt/></RequireRole>}/><Route path="/quiz/results/:attemptId" element={<RequireRole role="student"><StudentQuizResult/></RequireRole>}/><Route path="/teacher/quizzes/:id" element={<RequireRole role="teacher"><TeacherQuizEditor/></RequireRole>}/><Route path="/teacher/quiz-attempts/:attemptId" element={<RequireRole role="teacher"><TeacherQuizAttemptReview/></RequireRole>}/><Route path="/communications" element={<Communications/>}/><Route path="/attendance" element={<Attendance/>}/><Route path="/calendar" element={<Calendar/>}/><Route path="/grades" element={<Grades/>}/><Route path="/grades/:classroomId" element={<RequireRole role="student"><StudentGradeDetail/></RequireRole>}/><Route path="/teacher/gradebook/:classroomId" element={<RequireRole role="teacher"><TeacherGradebookDetail/></RequireRole>}/><Route path="/profile" element={<Profile/>}/>
 </Route><Route path="*" element={<RootRedirect/>}/>
 </Routes></Suspense>}
