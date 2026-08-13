import { Bell, CalendarDays, LogOut, MonitorSmartphone, Moon, ShieldCheck } from 'lucide-react'
import { useNavigate } from 'react-router'
import AppShell from '@/components/layout/AppShell'
import { PageHeader } from '@/components/shared/PageHeader'
import { Badge, Button, Card } from '@/components/ui'
import { useAuth } from '@/features/auth/AuthContext'
import { ROLE_LABEL } from '@/features/auth/config'

export default function Profile(){
  const { user, logout }=useAuth()
  const navigate=useNavigate()
  if(!user) return null

  const roleLabel=ROLE_LABEL[user.role]
  const student=user.role==='student'

  async function signOut(){ await logout(); navigate('/login',{replace:true}) }

  return <AppShell title="Profil Saya"><PageHeader eyebrow="Account & preferences" title="Profil saya" description="Kelola informasi akun, preferensi, perangkat, dan keamanan sesi EduNova." action={<Button>Edit profil</Button>}/><div className="grid gap-5 xl:grid-cols-[330px_minmax(0,1fr)]"><Card className="p-6 text-center"><div className="mx-auto grid h-20 w-20 place-items-center rounded-[22px] bg-slate-950 text-xl font-bold text-white">{user.initials}</div><h2 className="mt-4 text-xl font-semibold">{user.name}</h2><p className="mt-1 text-xs text-slate-500">{user.subtitle}</p><p className="mt-1 text-[11px] text-slate-400">{user.email}</p><div className="mt-4 flex flex-wrap justify-center gap-2"><Badge tone="green">Akun aktif</Badge><Badge>{roleLabel}</Badge><Badge>Semester 1</Badge></div>{student?<div className="mt-6 grid grid-cols-3 gap-2 border-t border-slate-100 pt-5">{[['92%','Progress'],['88.4','Nilai'],['4','Streak']].map(([v,l])=><div key={l}><b className="text-lg text-slate-900">{v}</b><span className="mt-1 block text-[10px] text-slate-400">{l}</span></div>)}</div>:<div className="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4 text-left"><p className="text-[10px] font-bold uppercase tracking-[.12em] text-slate-400">Workspace</p><p className="mt-2 text-sm font-semibold text-slate-800">{user.school}</p><p className="mt-1 text-xs text-slate-500">Role aktif: {roleLabel}</p></div>}<Button variant="secondary" className="mt-5 w-full text-red-600 hover:bg-red-50" onClick={signOut}><LogOut size={16}/>Keluar dari EduNova</Button></Card><Card className="p-5 sm:p-6"><p className="text-[10px] font-bold uppercase tracking-[.14em] text-slate-400">Preferences</p><h3 className="mt-1 text-base font-semibold">Preferensi akun</h3><div className="mt-4 divide-y divide-slate-100"><Setting icon={<Bell size={17}/>} title="Push notification" desc="Deadline, pengumuman, dan materi baru" active/><Setting icon={<CalendarDays size={17}/>} title="Pengingat aktivitas" desc="Reminder agenda penting di EduNova" active/><Setting icon={<Moon size={17}/>} title="Mode fokus" desc="Sembunyikan notifikasi saat bekerja atau belajar"/><Setting icon={<CalendarDays size={17}/>} title="Sinkronisasi kalender" desc="Hubungkan agenda EduNova ke kalender perangkat" action="Hubungkan"/><Setting icon={<MonitorSmartphone size={17}/>} title="Perangkat aktif" desc="Windows • Chrome • sesi ini aktif sekarang" action="Kelola"/><Setting icon={<ShieldCheck size={17}/>} title="Keamanan akun" desc="Password, sesi login, dan verifikasi perangkat" action="Buka"/></div></Card></div></AppShell>
}

function Setting({icon,title,desc,active,action}:{icon:React.ReactNode,title:string,desc:string,active?:boolean,action?:string}){return <div className="flex items-center gap-4 py-4"><div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">{icon}</div><div className="min-w-0 flex-1"><p className="text-sm font-semibold text-slate-800">{title}</p><p className="mt-1 text-xs leading-5 text-slate-400">{desc}</p></div>{action?<Button variant="secondary" size="sm">{action}</Button>:<button aria-label={`Toggle ${title}`} className={`relative h-6 w-11 rounded-full transition ${active?'bg-blue-600':'bg-slate-200'}`}><span className={`absolute top-1 h-4 w-4 rounded-full bg-white shadow-sm transition ${active?'left-6':'left-1'}`}/></button>}</div>}
