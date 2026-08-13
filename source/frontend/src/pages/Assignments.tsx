import { useAuth } from '@/features/auth/AuthContext'
import StudentAssignments from '@/pages/StudentAssignments'
import TeacherAssignments from '@/pages/TeacherAssignments'
import AppShell from '@/components/layout/AppShell'
import { Card } from '@/components/ui'
export default function Assignments(){const{user}=useAuth();if(user?.role==='teacher')return <TeacherAssignments/>;if(user?.role==='student')return <StudentAssignments/>;return <AppShell title="Aktivitas"><Card className="p-8"><h2 className="text-lg font-semibold">Assignment Center</h2><p className="mt-2 text-sm text-slate-500">Monitoring tugas lintas kelas akan tersedia pada modul laporan admin.</p></Card></AppShell>}
