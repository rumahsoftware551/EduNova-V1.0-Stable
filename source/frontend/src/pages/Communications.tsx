import { useEffect, useMemo, useState } from 'react'
import { Megaphone, MessageCircle, Pin, Send, Trash2 } from 'lucide-react'
import AppShell from '@/components/layout/AppShell'
import { Button, Card } from '@/components/ui'
import { apiRequest, ApiError } from '@/lib/api/client'
import { useAuth } from '@/features/auth/AuthContext'

type Classroom = { id:number; title:string; teaching_assignment?:{ subject?:{name:string}; class_group?:{name:string} } }
type Announcement = { id:number; title:string; body:string; status:string; is_pinned:boolean; published_at?:string|null; author?:{name:string} }
type Post = { id:number; body:string; created_at:string; user?:{name:string;role:string}; replies?:Post[] }
type Data<T> = { data:T }

export default function Communications(){
  const {user}=useAuth(); const teacher=user?.role==='teacher'
  const [classes,setClasses]=useState<Classroom[]>([]),[classroomId,setClassroomId]=useState<number|undefined>()
  const [ann,setAnn]=useState<Announcement[]>([]),[posts,setPosts]=useState<Post[]>([]),[tab,setTab]=useState<'announcements'|'discussion'>('announcements')
  const [title,setTitle]=useState(''),[body,setBody]=useState(''),[postBody,setPostBody]=useState(''),[busy,setBusy]=useState(false),[error,setError]=useState('')
  const prefix=teacher?'/teacher/communications':'/student/communications'
  const selected=useMemo(()=>classes.find(c=>c.id===classroomId),[classes,classroomId])

  useEffect(()=>{apiRequest<Data<Classroom[]>>(`${prefix}/classrooms`).then(r=>{setClasses(r.data);if(r.data[0])setClassroomId(r.data[0].id)}).catch(e=>setError(e.message))},[prefix])
  useEffect(()=>{if(!classroomId)return; Promise.all([
    apiRequest<Data<Announcement[]>>(`${prefix}/classrooms/${classroomId}/announcements`),
    apiRequest<Data<Post[]>>(`${prefix}/classrooms/${classroomId}/discussion`),
  ]).then(([a,p])=>{setAnn(a.data);setPosts(p.data)}).catch(e=>setError(e.message))},[classroomId,prefix])

  async function refresh(){if(!classroomId)return; const [a,p]=await Promise.all([apiRequest<Data<Announcement[]>>(`${prefix}/classrooms/${classroomId}/announcements`),apiRequest<Data<Post[]>>(`${prefix}/classrooms/${classroomId}/discussion`)]);setAnn(a.data);setPosts(p.data)}
  async function createAnnouncement(publish:boolean){if(!teacher||!classroomId||!title.trim()||!body.trim())return;setBusy(true);setError('');try{await apiRequest(`${prefix}/classrooms/${classroomId}/announcements`,{method:'POST',body:JSON.stringify({title,body,is_pinned:false,publish})});setTitle('');setBody('');await refresh()}catch(e){setError(e instanceof ApiError?e.message:'Gagal menyimpan pengumuman')}finally{setBusy(false)}}
  async function publish(id:number,published:boolean){await apiRequest(`${prefix}/announcements/${id}/publish`,{method:'POST',body:JSON.stringify({published})});await refresh()}
  async function remove(id:number){if(!confirm('Hapus pengumuman ini?'))return;await apiRequest(`${prefix}/announcements/${id}`,{method:'DELETE'});await refresh()}
  async function sendPost(){if(!classroomId||!postBody.trim())return;setBusy(true);try{await apiRequest(`${prefix}/classrooms/${classroomId}/discussion`,{method:'POST',body:JSON.stringify({body:postBody})});setPostBody('');await refresh()}finally{setBusy(false)}}

  return <AppShell title="Komunikasi">
    <div className="grid gap-6 xl:grid-cols-[270px_1fr]">
      <Card className="h-fit p-3"><p className="px-2 pb-2 text-xs font-bold uppercase tracking-[.14em] text-slate-400">Kelas</p>{classes.map(c=><button key={c.id} onClick={()=>setClassroomId(c.id)} className={`mb-1 w-full rounded-xl px-3 py-3 text-left ${classroomId===c.id?'bg-blue-50 text-blue-800':'hover:bg-slate-50'}`}><b className="block text-sm">{c.title}</b><span className="text-xs text-slate-500">{c.teaching_assignment?.class_group?.name}</span></button>)}</Card>
      <div>
        <div className="mb-5 flex flex-wrap items-end justify-between gap-3"><div><p className="text-xs font-semibold uppercase tracking-[.14em] text-blue-600">Class communication</p><h2 className="mt-1 text-2xl font-semibold tracking-tight">{selected?.title??'Pilih kelas'}</h2></div><div className="flex rounded-xl border border-slate-200 bg-white p-1"><button onClick={()=>setTab('announcements')} className={`rounded-lg px-3 py-2 text-sm font-semibold ${tab==='announcements'?'bg-slate-950 text-white':'text-slate-500'}`}>Pengumuman</button><button onClick={()=>setTab('discussion')} className={`rounded-lg px-3 py-2 text-sm font-semibold ${tab==='discussion'?'bg-slate-950 text-white':'text-slate-500'}`}>Diskusi</button></div></div>
        {error&&<div className="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{error}</div>}
        {tab==='announcements'?<div className="space-y-4">
          {teacher&&<Card className="p-5"><div className="mb-4 flex items-center gap-2"><Megaphone size={18} className="text-blue-600"/><b>Buat pengumuman</b></div><input value={title} onChange={e=>setTitle(e.target.value)} placeholder="Judul pengumuman" className="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm outline-none focus:border-blue-400"/><textarea value={body} onChange={e=>setBody(e.target.value)} placeholder="Tulis informasi untuk siswa..." rows={4} className="mt-3 w-full resize-none rounded-xl border border-slate-200 px-3 py-3 text-sm outline-none focus:border-blue-400"/><div className="mt-3 flex justify-end gap-2"><Button variant="secondary" disabled={busy} onClick={()=>createAnnouncement(false)}>Simpan Draft</Button><Button disabled={busy} onClick={()=>createAnnouncement(true)}>Publikasikan</Button></div></Card>}
          {ann.map(a=><Card key={a.id} className="p-5"><div className="flex items-start gap-3"><div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><Megaphone size={18}/></div><div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><h3 className="font-semibold text-slate-950">{a.title}</h3>{a.is_pinned&&<Pin size={13} className="text-amber-500"/>}<span className={`rounded-full px-2 py-0.5 text-[10px] font-bold ${a.status==='published'?'bg-emerald-50 text-emerald-700':'bg-slate-100 text-slate-500'}`}>{a.status==='published'?'PUBLISHED':'DRAFT'}</span></div><p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-600">{a.body}</p><p className="mt-3 text-xs text-slate-400">{a.author?.name}</p>{teacher&&<div className="mt-4 flex gap-2"><Button size="sm" variant="secondary" onClick={()=>publish(a.id,a.status!=='published')}>{a.status==='published'?'Tarik Publikasi':'Publish'}</Button><Button size="sm" variant="ghost" className="text-red-600" onClick={()=>remove(a.id)}><Trash2 size={14}/> Hapus</Button></div>}</div></div></Card>)}
          {!ann.length&&<Card className="p-8 text-center text-sm text-slate-500">Belum ada pengumuman.</Card>}
        </div>:<div className="space-y-4">
          <Card className="p-4"><div className="flex gap-3"><div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100"><MessageCircle size={18}/></div><textarea value={postBody} onChange={e=>setPostBody(e.target.value)} rows={3} placeholder="Tulis pertanyaan atau diskusi..." className="min-h-24 flex-1 resize-none rounded-xl border border-slate-200 px-3 py-3 text-sm outline-none focus:border-blue-400"/></div><div className="mt-3 flex justify-end"><Button disabled={busy||!postBody.trim()} onClick={sendPost}><Send size={15}/> Kirim</Button></div></Card>
          {posts.map(p=><Card key={p.id} className="p-5"><div className="flex gap-3"><div className="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-950 text-[10px] font-bold text-white">{p.user?.name?.slice(0,2).toUpperCase()}</div><div><div className="flex items-center gap-2"><b className="text-sm">{p.user?.name}</b><span className="text-[10px] uppercase text-slate-400">{p.user?.role}</span></div><p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-600">{p.body}</p>{p.replies?.map(r=><div key={r.id} className="mt-3 rounded-xl bg-slate-50 p-3"><b className="text-xs">{r.user?.name}</b><p className="mt-1 text-sm text-slate-600">{r.body}</p></div>)}</div></div></Card>)}
        </div>}
      </div>
    </div>
  </AppShell>
}
