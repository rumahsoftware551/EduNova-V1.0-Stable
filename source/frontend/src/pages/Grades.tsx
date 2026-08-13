import { useAuth } from '@/features/auth/AuthContext'
import TeacherGradebooks from '@/pages/TeacherGradebooks'
import StudentGrades from '@/pages/StudentGrades'
import AdminLearningAnalytics from '@/pages/AdminLearningAnalytics'

export default function Grades(){
  const {user}=useAuth()
  if(user?.role==='teacher') return <TeacherGradebooks/>
  if(user?.role==='admin') return <AdminLearningAnalytics/>
  return <StudentGrades/>
}
