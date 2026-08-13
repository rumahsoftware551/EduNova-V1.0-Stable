import { useEffect, useState } from 'react'
import { AlertTriangle, BarChart3, CheckCircle2, GraduationCap, UsersRound } from 'lucide-react'
import AppShell from '@/components/layout/AppShell'
import { Badge, Card, Progress } from '@/components/ui'
import { gradeApi, type AdminAnalytics } from '@/features/grades/api'
const fmt=(n:number|null|undefined)=>n==null?'—':n.toFixed(1)
export default function AdminLearningAnalytics(){
 const [data,setData]=useState<AdminAnalytics|null>(null)
 useEffect(()=>{gradeApi.admin.overview().then(r=>setData(r.data))},[])
 if(!data)return <AppShell title="Laporan"><Card className="h-64 animate-pulse"/></AppShell>
 return <AppShell title="Learning Analytics">
  <div className="mb-7"><p className="text-xs font-semibold uppercase tracking-[.16em] text-blue-600">School intelligence</p><h2 className="mt-2 text-2xl font-semibold tracking-tight">Learning analytics sekolah</h2><p className="mt-1 text-sm text-slate-500">Ringkasan gradebook, tingkat ketuntasan, performa kelas, dan siswa yang perlu perhatian.</p></div>
  <div className="grid grid-cols-2 gap-3 lg:grid-cols-5"><Metric icon={<GraduationCap/>} label="Kelas digital" value={String(data.summary.classrooms)}/><Metric icon={<BarChart3/>} label="Gradebook publish" value={String(data.summary.published_gradebooks)}/><Metric icon={<UsersRound/>} label="Nilai dipublish" value={String(data.summary.published_students)}/><Metric icon={<CheckCircle2/>} label="Tingkat lulus" value={data.summary.pass_rate==null?'—':`${fmt(data.summary.pass_rate)}%`}/><Metric icon={<AlertTriangle/>} label="Perlu perhatian" value={String(data.summary.at_risk)}/></div>
  <Card className="mt-5 overflow-hidden p-0"><div className="border-b border-slate-100 px-5 py-4"><h3 className="font-semibold">Performa per kelas</h3><p className="mt-1 text-xs text-slate-500">Data dihitung dari tugas, quiz, dan progress materi pada setiap Digital Classroom.</p></div><div className="divide-y divide-slate-100">{data.classes.map(item=><div key={item.id} className="grid gap-4 px-5 py-5 md:grid-cols-[1.4fr_.8fr_.8fr_.8fr] md:items-center"><div><div className="flex items-center gap-2"><Badge tone={item.grade_status==='published'?'green':'slate'}>{item.grade_status}</Badge><span className="text-xs text-slate-400">{item.class_group?.name}</span></div><p className="mt-2 font-semibold">{item.subject?.name||item.title}</p><p className="mt-1 text-xs text-slate-500">{item.teacher?.name}</p></div><Cell label="Rata-rata" value={fmt(item.summary.class_average)}/><Cell label="Tingkat lulus" value={item.summary.pass_rate==null?'—':`${fmt(item.summary.pass_rate)}%`}/><div><div className="mb-2 flex justify-between text-xs"><span className="text-slate-500">Risiko</span><span className={item.summary.at_risk?'text-amber-700':'text-emerald-600'}>{item.summary.at_risk} siswa</span></div><Progress value={item.summary.students?Math.min(100,(item.summary.at_risk/item.summary.students)*100):0}/></div></div>)}</div></Card>
 </AppShell>
}
function Metric({icon,label,value}:{icon:React.ReactNode;label:string;value:string}){return <Card className="p-4"><div className="text-blue-600 [&>svg]:h-4 [&>svg]:w-4">{icon}</div><p className="mt-3 text-[11px] text-slate-500">{label}</p><p className="mt-1 text-xl font-semibold">{value}</p></Card>}
function Cell({label,value}:{label:string;value:string}){return <div><p className="text-[11px] text-slate-400">{label}</p><p className="mt-1 text-lg font-semibold">{value}</p></div>}
