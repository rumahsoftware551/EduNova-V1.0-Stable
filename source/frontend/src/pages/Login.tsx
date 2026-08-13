import { useEffect, useState } from 'react'
import { AlertCircle, ArrowRight, CheckCircle2, Eye, EyeOff, LockKeyhole, Mail, Sparkles } from 'lucide-react'
import { useLocation, useNavigate } from 'react-router'
import { motion } from 'motion/react'
import { Brand } from '@/components/shared/Brand'
import { Button, Card, Input } from '@/components/ui'
import { useAuth } from '@/features/auth/AuthContext'
import { ensureCsrfCookie } from '@/lib/api/client'
import { demoIdentifierForRole, ROLE_HOME } from '@/features/auth/config'
import type { UserRole } from '@/features/auth/types'

export default function Login() {
  const navigate = useNavigate()
  const location = useLocation()
  const { login } = useAuth()
  const [role, setRole] = useState<UserRole>('student')
  const [identifier, setIdentifier] = useState(demoIdentifierForRole('student'))
  const [password, setPassword] = useState('edunova123')
  const [show, setShow] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  useEffect(() => {
    // Warm Sanctum CSRF + the selected dashboard while the user is typing.
    void ensureCsrfCookie().catch(() => undefined)
    const preload = role === 'student'
      ? import('@/pages/StudentDashboard')
      : role === 'teacher'
        ? import('@/pages/TeacherDashboard')
        : import('@/pages/AdminDashboard')
    void preload.catch(() => undefined)
  }, [role])

  function changeRole(nextRole: UserRole) {
    setRole(nextRole)
    setIdentifier(demoIdentifierForRole(nextRole))
    setPassword('edunova123')
    setError('')
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      const user = await login({ identifier, password, role })
      const requested = (location.state as { from?: string } | null)?.from
      navigate(requested && requested !== '/login' ? requested : ROLE_HOME[user.role], { replace: true })
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : 'Login gagal. Silakan coba kembali.')
    } finally {
      setLoading(false)
    }
  }

  return <div className="min-h-screen bg-[#F6F8FB] p-3 sm:p-5">
    <div className="mx-auto grid min-h-[calc(100vh-24px)] max-w-[1500px] overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,.08)] lg:grid-cols-[1.15fr_.85fr]">
      <section className="dashboard-grid relative hidden overflow-hidden bg-[#0D1B36] p-10 text-white lg:flex lg:flex-col">
        <div className="absolute -right-20 -top-24 h-96 w-96 rounded-full border-[54px] border-blue-500/[.08]" />
        <div className="relative"><div className="inline-flex items-center gap-3 rounded-2xl bg-white p-3 pr-4 text-slate-950"><Brand /></div></div>
        <div className="relative my-auto max-w-[650px]"><div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[.06] px-3 py-1.5 text-xs font-semibold text-blue-200"><Sparkles size={14} />Learning workspace generasi baru</div><h1 className="mt-6 max-w-[620px] text-5xl font-semibold leading-[1.08] tracking-[-.05em]">Belajar lebih terarah. Berkembang tanpa kehilangan fokus.</h1><p className="mt-5 max-w-xl text-base leading-7 text-slate-300">EduNova menyatukan kelas, materi, tugas, nilai, dan kalender belajar dalam satu pengalaman yang tenang dan terstruktur.</p><div className="mt-8 grid max-w-xl grid-cols-3 gap-3">{[['6','Kelas aktif'],['88.4','Rata-rata nilai'],['92%','Aktivitas selesai']].map(([n,l])=><div key={l} className="rounded-2xl border border-white/10 bg-white/[.05] p-4"><b className="text-2xl tracking-tight">{n}</b><span className="mt-1 block text-xs text-slate-400">{l}</span></div>)}</div></div>
        <div className="relative flex items-center gap-3 text-xs text-slate-400"><CheckCircle2 size={15} className="text-emerald-400" />Protected routes • role based • PWA ready</div>
      </section>

      <section className="flex items-center justify-center p-5 sm:p-8 lg:p-12">
        <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} className="w-full max-w-[430px]">
          <div className="mb-8 lg:hidden"><Brand /></div>
          <p className="text-[11px] font-bold uppercase tracking-[.15em] text-blue-700">Welcome back</p><h2 className="mt-2 text-3xl font-bold tracking-[-.04em] text-slate-950">Masuk ke EduNova</h2><p className="mt-2 text-sm leading-6 text-slate-500">Gunakan akun sekolah untuk melanjutkan aktivitas belajar.</p>

          <div className="mt-6 grid grid-cols-3 gap-2 rounded-2xl bg-slate-100 p-1.5">{([['student','Siswa'],['teacher','Guru'],['admin','Admin']] as const).map(([v,l])=><button type="button" key={v} onClick={()=>changeRole(v)} className={`rounded-xl px-3 py-2.5 text-xs font-semibold transition ${role===v?'bg-white text-slate-950 shadow-sm':'text-slate-500 hover:text-slate-800'}`}>{l}</button>)}</div>

          <form onSubmit={submit} className="mt-6 space-y-4">
            <label className="block"><span className="mb-2 block text-xs font-semibold text-slate-700">Email atau username</span><div className="relative"><Mail size={16} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><Input className="pl-10" value={identifier} onChange={(e)=>setIdentifier(e.target.value)} autoComplete="username" /></div></label>
            <label className="block"><div className="mb-2 flex items-center justify-between"><span className="text-xs font-semibold text-slate-700">Password</span><button type="button" className="text-xs font-semibold text-blue-700">Lupa password?</button></div><div className="relative"><LockKeyhole size={16} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><Input className="pl-10 pr-10" type={show?'text':'password'} value={password} onChange={(e)=>setPassword(e.target.value)} autoComplete="current-password" /><button type="button" onClick={()=>setShow(v=>!v)} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">{show?<EyeOff size={16}/>:<Eye size={16}/>}</button></div></label>
            {error && <div className="flex gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2.5 text-xs leading-5 text-red-700"><AlertCircle size={16} className="mt-0.5 shrink-0" />{error}</div>}
            <Button type="submit" size="lg" className="w-full" disabled={loading}>{loading?'Memverifikasi akun...':<>Masuk ke workspace<ArrowRight size={16}/></>}</Button>
          </form>

          <Card className="mt-6 bg-slate-50 p-4 shadow-none"><p className="text-xs leading-5 text-slate-500"><b className="text-slate-700">Akun demo database:</b> Siswa <code>andi.saputra</code>, Guru <code>susanto</code>, Admin <code>admin</code>. Password semua role: <code>edunova123</code>.</p></Card>
        </motion.div>
      </section>
    </div>
  </div>
}
