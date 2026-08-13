import { useAuth } from '@/features/auth/AuthContext'
import TeacherAttendance from './TeacherAttendance'
import StudentAttendance from './StudentAttendance'
import AdminAttendanceAnalytics from './AdminAttendanceAnalytics'
export default function Attendance(){const {user}=useAuth();return user?.role==='teacher'?<TeacherAttendance/>:user?.role==='admin'?<AdminAttendanceAnalytics/>:<StudentAttendance/>}
