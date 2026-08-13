import { useAuth } from '@/features/auth/AuthContext'
import StudentQuizzes from '@/pages/StudentQuizzes'
import TeacherQuizzes from '@/pages/TeacherQuizzes'
import AppShell from '@/components/layout/AppShell'
import { Card } from '@/components/ui'
export default function Quiz(){const{user}=useAuth();if(user?.role==='teacher')return <TeacherQuizzes/>;if(user?.role==='student')return <StudentQuizzes/>;return <AppShell title="Quiz & Ujian"><Card className="p-8"><h2 className="text-lg font-semibold">Assessment Monitor</h2><p className="mt-2 text-sm text-slate-500">Monitoring ujian lintas kelas akan tersedia di modul laporan admin.</p></Card></AppShell>}
