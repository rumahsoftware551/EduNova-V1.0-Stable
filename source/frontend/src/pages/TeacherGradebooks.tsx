import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router'
import { AlertTriangle, ArrowRight, BarChart3, BookOpenCheck, CheckCircle2, Users } from 'lucide-react'
import AppShell from '@/components/layout/AppShell'
import { Badge, Button, Card } from '@/components/ui'
import { gradeApi, type GradebookSummary } from '@/features/grades/api'

const fmt=(n:number|null|undefined)=>n==null?'—':n.toFixed(1)

export default function TeacherGradebooks(){
 const [items,setItems]=useState<GradebookSummary[]>([])
 const [loading,setLoading]=useState(true); const [error,setError]=useState('')
 const navigate=useNavigate()
 useEffect(()=>{gradeApi.teacher.list().then(r=>setItems(r.data)).catch(e=>setError(e instanceof Error?e.message:'Gagal memuat gradebook')).finally(()=>setLoading(false))},[])
 return <AppShell title="Penilaian">
  <div className="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p className="text-xs font-semibold uppercase tracking-[.16em] text-blue-600">Phase 08 • Gradebook</p><h2 className="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Gradebook & learning analytics</h2><p className="mt-1 max-w-2xl text-sm leading-6 text-slate-500">Satukan nilai tugas dan quiz, atur bobot, pantau progres belajar, lalu publikasikan nilai akhir ke siswa.</p></div></div>
  {error&&<Card className="mb-5 border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</Card>}
  {loading?<div className="grid gap-4 md:grid-cols-2"><Card className="h-44 animate-pulse bg-white"/><Card className="h-44 animate-pulse bg-white"/></div>:
  <div className="grid gap-4 xl:grid-cols-2">{items.map(item=><Card key={item.id} className="overflow-hidden p-0"><div className="p-5 sm:p-6"><div className="flex items-start justify-between gap-4"><div><div className="flex items-center gap-2"><Badge tone={item.settings.status==='published'?'green':'slate'}>{item.settings.status==='published'?'Dipublikasikan':'Draft'}</Badge><span className="text-xs text-slate-400">{item.class_group?.name}</span></div><h3 className="mt-3 text-lg font-semibold text-slate-950">{item.subject?.name||item.title}</h3><p className="mt-1 text-sm text-slate-500">{item.title}</p></div><div className="grid h-11 w-11 place-items-center rounded-2xl bg-blue-50 text-blue-700"><BarChart3 size={20}/></div></div>
   <div className="mt-5 grid grid-cols-3 gap-2"><Mini icon={<Users size={15}/>} label="Siswa" value={String(item.summary.students)}/><Mini icon={<BookOpenCheck size={15}/>} label="Rata-rata" value={fmt(item.summary.class_average)}/><Mini icon={<CheckCircle2 size={15}/>} label="Lulus" value={item.summary.pass_rate==null?'—':`${fmt(item.summary.pass_rate)}%`}/></div>
   <div className="mt-4 flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5"><span className="flex items-center gap-2 text-xs text-slate-500"><AlertTriangle size={14} className={item.summary.at_risk?'text-amber-500':'text-slate-400'}/>{item.summary.at_risk} siswa perlu perhatian</span><span className="text-xs font-medium text-slate-600">Tugas {item.settings.assignment_weight}% • Quiz {item.settings.quiz_weight}%</span></div>
   <Button onClick={()=>navigate(`/teacher/gradebook/${item.id}`)} className="mt-5 w-full justify-between">Buka gradebook <ArrowRight size={16}/></Button></div></Card>)}
   {!items.length&&<Card className="p-8 text-center text-sm text-slate-500">Belum ada kelas digital untuk gradebook.</Card>}
  </div>}
 </AppShell>
}
function Mini({icon,label,value}:{icon:React.ReactNode;label:string;value:string}){return <div className="rounded-xl border border-slate-100 bg-white p-3"><div className="flex items-center gap-1.5 text-slate-400">{icon}<span className="text-[11px]">{label}</span></div><p className="mt-2 text-lg font-semibold text-slate-950">{value}</p></div>}
