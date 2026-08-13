import { GraduationCap } from 'lucide-react'
export function Brand({ compact=false }: { compact?: boolean }) {
  return <div className="flex items-center gap-3"><div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-200"><GraduationCap size={22}/></div>{!compact&&<div><div className="text-[19px] font-bold tracking-[-.04em] text-slate-950">EduNova</div><div className="mt-0.5 text-[9px] font-semibold uppercase tracking-[.2em] text-slate-400">Learning Platform</div></div>}</div>
}
