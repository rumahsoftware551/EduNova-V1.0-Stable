import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router'
import { ArrowRight, Award, BookOpenCheck, Clock3, LockKeyhole } from 'lucide-react'
import AppShell from '@/components/layout/AppShell'
import { Badge, Button, Card } from '@/components/ui'
import { gradeApi, type StudentGradeSummary } from '@/features/grades/api'

export default function StudentGrades(){
 const [items,setItems]=useState<StudentGradeSummary[]>([]);const [loading,setLoading]=useState(true);const navigate=useNavigate()
 useEffect(()=>{gradeApi.student.list().then(r=>setItems(r.data)).finally(()=>setLoading(false))},[])
 return <AppShell title="Nilai"><div className="mb-7"><p className="text-xs font-semibold uppercase tracking-[.16em] text-blue-600">Learning record</p><h2 className="mt-2 text-2xl font-semibold tracking-tight">Nilai & capaian belajar</h2><p className="mt-1 text-sm text-slate-500">Pantau hasil tugas, quiz, progres materi, dan nilai akhir yang sudah dipublikasikan guru.</p></div>
 {loading?<Card className="h-52 animate-pulse"/>:<div className="grid gap-4 lg:grid-cols-2">{items.map(item=><Card key={item.id} className="p-5 sm:p-6"><div className="flex items-start justify-between gap-4"><div><div className="flex items-center gap-2"><Badge tone={item.published?'green':'slate'}>{item.published?'Nilai final':'Dalam proses'}</Badge><span className="text-xs text-slate-400">{item.class_group?.name}</span></div><h3 className="mt-3 text-lg font-semibold">{item.subject?.name||item.title}</h3><p className="mt-1 text-xs text-slate-500">{item.title}</p></div><div className={`grid h-12 w-12 place-items-center rounded-2xl ${item.published?'bg-blue-600 text-white':'bg-slate-100 text-slate-500'}`}>{item.published?<Award size={21}/>:<Clock3 size={20}/>}</div></div>
 {item.final_grade?<div className="mt-5 grid grid-cols-3 gap-2"><Score label="Nilai akhir" value={item.final_grade.final_score.toFixed(1)}/><Score label="Predikat" value={item.final_grade.letter_grade}/><Score label="Status" value={item.final_grade.result_status==='passed'?'Lulus':'Belum'}/></div>:<div className="mt-5 flex items-center gap-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-500"><LockKeyhole size={18}/>Nilai akhir belum dipublikasikan. Progres komponen tetap dapat dilihat.</div>}
 <Button variant="secondary" className="mt-5 w-full justify-between" onClick={()=>navigate(`/grades/${item.id}`)}>Lihat rincian <ArrowRight size={16}/></Button></Card>)}
 {!items.length&&<Card className="p-8 text-center text-sm text-slate-500"><BookOpenCheck className="mx-auto mb-3 text-slate-300"/>Belum ada kelas yang memiliki data penilaian.</Card>}</div>}
 </AppShell>
}
function Score({label,value}:{label:string;value:string}){return <div className="rounded-xl bg-slate-50 p-3"><p className="text-[10px] text-slate-400">{label}</p><p className="mt-1 text-lg font-semibold">{value}</p></div>}
