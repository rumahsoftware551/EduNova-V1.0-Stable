import { useEffect, useMemo, useState, type ReactNode } from 'react'
import { BarChart3, Bell, BookOpen, CalendarDays, ChevronDown, ClipboardList, CircleHelp, GraduationCap, Home, LogOut, Menu, MessageSquareText, ScanLine, Search, Settings, UserRound, X } from 'lucide-react'
import { NavLink, useNavigate } from 'react-router'
import { AnimatePresence, motion } from 'motion/react'
import { Brand } from '@/components/shared/Brand'
import { NotificationDrawer } from '@/components/shared/NotificationDrawer'
import { CommandPalette } from '@/components/shared/CommandPalette'
import { PWAInstallButton } from '@/components/shared/PWAInstallButton'
import { OfflineStatus } from '@/components/shared/OfflineStatus'
import { Button } from '@/components/ui'
import { useAuth } from '@/features/auth/AuthContext'
import { ROLE_HOME, ROLE_LABEL } from '@/features/auth/config'

const studentNav = [
  ['/student','Ringkasan',Home], ['/courses','Kelas Saya',BookOpen], ['/assignments','Tugas',ClipboardList], ['/quiz','Quiz & Ujian',CircleHelp], ['/communications','Komunikasi',MessageSquareText], ['/attendance','Absensi',ScanLine], ['/calendar','Kalender',CalendarDays], ['/grades','Nilai',BarChart3], ['/profile','Profil',UserRound]
] as const
const teacherNav = [
  ['/teacher','Ringkasan',Home], ['/teacher/classrooms','Kelas Digital',BookOpen], ['/assignments','Penugasan',ClipboardList], ['/quiz','Quiz & Ujian',CircleHelp], ['/communications','Komunikasi',MessageSquareText], ['/attendance','Absensi',ScanLine], ['/calendar','Kalender',CalendarDays], ['/grades','Penilaian',BarChart3], ['/profile','Profil',UserRound]
] as const
const adminNav = [
  ['/admin','Ringkasan',Home], ['/admin/academic','Master Akademik',GraduationCap], ['/attendance','Kehadiran',ScanLine], ['/courses','Kurikulum',BookOpen], ['/assignments','Aktivitas',ClipboardList], ['/grades','Laporan',BarChart3], ['/profile','Pengaturan',Settings]
] as const

export default function AppShell({ children, title='EduNova' }: { children: ReactNode; title?: string }) {
  const [mobileOpen,setMobileOpen]=useState(false)
  const [notificationsOpen,setNotificationsOpen]=useState(false)
  const [commandOpen,setCommandOpen]=useState(false)
  const [accountOpen,setAccountOpen]=useState(false)
  const navigate=useNavigate()
  const { user, logout }=useAuth()

  const nav=useMemo(()=>user?.role==='teacher'?teacherNav:user?.role==='admin'?adminNav:studentNav,[user?.role])
  const mobileNav=useMemo(()=>nav.filter(([to])=>to!=='/calendar').slice(0,5),[nav])

  useEffect(()=>{
    function onKey(e:KeyboardEvent){
      if((e.metaKey||e.ctrlKey)&&e.key.toLowerCase()==='k'){ e.preventDefault(); setCommandOpen(true) }
      if(e.key==='Escape'){ setCommandOpen(false); setNotificationsOpen(false); setMobileOpen(false); setAccountOpen(false) }
    }
    window.addEventListener('keydown',onKey)
    return ()=>window.removeEventListener('keydown',onKey)
  },[])

  async function signOut(){
    await logout()
    navigate('/login',{replace:true})
  }

  if(!user) return null

  const roleLabel=ROLE_LABEL[user.role]

  return <div className="min-h-screen bg-[#F6F8FB] text-slate-900">
    <aside className="fixed inset-y-0 left-0 z-40 hidden w-[248px] border-r border-slate-200 bg-white lg:flex lg:flex-col">
      <div className="flex h-[78px] items-center border-b border-slate-100 px-6"><Brand/></div>
      <div className="px-4 pt-5"><p className="px-3 text-[10px] font-bold uppercase tracking-[.18em] text-slate-400">Workspace</p><nav className="mt-3 space-y-1">{nav.map(([to,label,Icon])=><NavLink key={to} to={to} className={({isActive})=>`group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ${isActive?'bg-blue-50 text-blue-700':'text-slate-600 hover:bg-slate-50 hover:text-slate-950'}`}><Icon size={18} strokeWidth={2}/><span>{label}</span>{label==='Tugas'&&user.role==='student'&&<span className="ml-auto rounded-md bg-blue-100 px-1.5 py-0.5 text-[9px] font-bold text-blue-700">3</span>}</NavLink>)}</nav></div>
      <div className="mt-auto border-t border-slate-100 p-4"><div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4"><div className="flex items-center gap-3"><div className="grid h-9 w-9 place-items-center rounded-xl bg-slate-950 text-xs font-bold text-white">{user.initials}</div><div className="min-w-0"><p className="truncate text-sm font-semibold">{user.name}</p><p className="truncate text-xs text-slate-500">{user.subtitle}</p></div></div><div className="mt-3 flex items-center justify-between text-[11px] text-slate-500"><span>{roleLabel}</span><button onClick={signOut} className="inline-flex items-center gap-1 font-semibold text-slate-500 hover:text-red-600"><LogOut size={13}/>Keluar</button></div></div></div>
    </aside>

    <AnimatePresence>{mobileOpen&&<><motion.button aria-label="Tutup menu" className="fixed inset-0 z-50 bg-slate-950/30 backdrop-blur-[2px] lg:hidden" initial={{opacity:0}} animate={{opacity:1}} exit={{opacity:0}} onClick={()=>setMobileOpen(false)}/><motion.aside initial={{x:-300}} animate={{x:0}} exit={{x:-300}} transition={{duration:.22}} className="fixed inset-y-0 left-0 z-[60] w-[min(300px,86vw)] bg-white shadow-[20px_0_55px_rgba(15,23,42,.16)] lg:hidden"><div className="flex h-[72px] items-center justify-between border-b border-slate-100 px-5"><Brand/><Button variant="ghost" size="icon" onClick={()=>setMobileOpen(false)}><X size={18}/></Button></div><div className="border-b border-slate-100 p-4"><div className="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><div className="grid h-10 w-10 place-items-center rounded-xl bg-slate-950 text-xs font-bold text-white">{user.initials}</div><div><p className="text-sm font-semibold">{user.name}</p><p className="text-xs text-slate-500">{user.subtitle} • {roleLabel}</p></div></div></div><nav className="space-y-1 p-4">{nav.map(([to,label,Icon])=><NavLink onClick={()=>setMobileOpen(false)} key={to} to={to} className={({isActive})=>`flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium ${isActive?'bg-blue-50 text-blue-700':'text-slate-700 hover:bg-slate-50'}`}><Icon size={18}/>{label}</NavLink>)}<button onClick={signOut} className="mt-3 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-red-600 hover:bg-red-50"><LogOut size={18}/>Keluar</button></nav></motion.aside></>}</AnimatePresence>

    <div className="lg:pl-[248px]">
      <header className="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur"><div className="mx-auto flex h-[78px] max-w-[1480px] items-center gap-4 px-4 sm:px-6 lg:px-8"><Button variant="secondary" size="icon" className="lg:hidden" onClick={()=>setMobileOpen(true)}><Menu size={18}/></Button><div className="min-w-0"><p className="text-[10px] font-semibold uppercase tracking-[.14em] text-slate-400">SMK Negeri 6 • {roleLabel}</p><h1 className="truncate text-lg font-semibold tracking-tight text-slate-950">{title}</h1></div><button onClick={()=>setCommandOpen(true)} className="ml-auto hidden w-full max-w-[360px] items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-left md:flex"><Search size={17} className="text-slate-400"/><span className="flex-1 text-sm text-slate-400">Cari kelas, materi, atau tugas</span><span className="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] text-slate-400">⌘K</span></button><PWAInstallButton compact/><Button variant="secondary" size="icon" className="relative" onClick={()=>setNotificationsOpen(true)}><Bell size={18}/><span className="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-blue-600 ring-2 ring-white"/></Button><div className="relative hidden sm:block"><button onClick={()=>setAccountOpen(v=>!v)} className="flex items-center gap-2 rounded-xl p-1.5 pl-2 hover:bg-slate-50"><div className="grid h-9 w-9 place-items-center rounded-xl bg-slate-950 text-xs font-bold text-white">{user.initials}</div><div className="hidden text-left xl:block"><p className="max-w-36 truncate text-xs font-semibold">{user.name}</p><p className="text-[11px] text-slate-400">{user.subtitle}</p></div><ChevronDown size={15} className="text-slate-400"/></button>{accountOpen&&<div className="absolute right-0 top-[calc(100%+8px)] w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_18px_50px_rgba(15,23,42,.14)]"><button onClick={()=>{setAccountOpen(false);navigate('/profile')}} className="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50"><UserRound size={16}/>Profil akun</button><button onClick={signOut} className="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm text-red-600 hover:bg-red-50"><LogOut size={16}/>Keluar</button></div>}</div></div></header>
      <main className="mx-auto max-w-[1480px] px-4 pb-28 pt-7 sm:px-6 lg:px-8 lg:pb-10">{children}</main>
    </div>

    <nav className="fixed bottom-3 left-3 right-3 z-40 grid grid-cols-5 rounded-[20px] border border-slate-200 bg-white/95 p-1.5 shadow-xl shadow-slate-900/10 backdrop-blur lg:hidden">{mobileNav.map(([to,label,Icon])=><NavLink key={to} to={to} className={({isActive})=>`flex min-w-0 flex-col items-center gap-1 rounded-2xl py-2 text-[10px] font-medium ${isActive?'bg-blue-50 text-blue-700':'text-slate-500'}`}><Icon size={19}/><span className="truncate">{label==='Ringkasan'?'Home':label}</span></NavLink>)}</nav>

    <NotificationDrawer open={notificationsOpen} onClose={()=>setNotificationsOpen(false)}/>
    <CommandPalette open={commandOpen} onClose={()=>setCommandOpen(false)}/>
    <OfflineStatus/>
  </div>
}
