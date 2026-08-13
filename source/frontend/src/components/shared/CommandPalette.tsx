import { BookOpen, CalendarDays, ChartNoAxesCombined, ClipboardList, Search, X } from 'lucide-react'
import { AnimatePresence, motion } from 'motion/react'
import { useMemo, useState } from 'react'
import { useNavigate } from 'react-router'

const commands = [
  { label:'Kelas Saya', keywords:'kelas course', to:'/courses', icon:BookOpen, key:'K' },
  { label:'Tugas & Deadline', keywords:'tugas assignment deadline', to:'/assignments', icon:ClipboardList, key:'T' },
  { label:'Kalender Belajar', keywords:'kalender jadwal schedule', to:'/calendar', icon:CalendarDays, key:'C' },
  { label:'Nilai & Perkembangan', keywords:'nilai grade score', to:'/grades', icon:ChartNoAxesCombined, key:'G' },
  { label:'Exposure Triangle — Fotografi Digital', keywords:'exposure triangle fotografi materi', to:'/material', icon:BookOpen, key:'↵' }
]

export function CommandPalette({ open, onClose }: { open: boolean; onClose: () => void }) {
  const [query,setQuery]=useState('')
  const navigate=useNavigate()
  const filtered=useMemo(()=>commands.filter(c=>(c.label+' '+c.keywords).toLowerCase().includes(query.toLowerCase())),[query])
  function go(to:string){ navigate(to); setQuery(''); onClose() }
  return <AnimatePresence>
    {open&&<>
      <motion.button aria-label="Tutup pencarian" initial={{opacity:0}} animate={{opacity:1}} exit={{opacity:0}} onClick={onClose} className="fixed inset-0 z-[70] cursor-default bg-slate-950/30 backdrop-blur-[2px]"/>
      <motion.div initial={{opacity:0,y:-10,scale:.99}} animate={{opacity:1,y:0,scale:1}} exit={{opacity:0,y:-8,scale:.99}} className="fixed left-1/2 top-[13vh] z-[90] w-[min(620px,92vw)] -translate-x-1/2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_28px_80px_rgba(15,23,42,.22)]">
        <div className="flex h-14 items-center gap-3 border-b border-slate-100 px-4"><Search size={18} className="text-slate-400"/><input autoFocus value={query} onChange={e=>setQuery(e.target.value)} onKeyDown={e=>{ if(e.key==='Escape')onClose(); if(e.key==='Enter'&&filtered[0])go(filtered[0].to) }} className="h-full w-full bg-transparent text-sm outline-none placeholder:text-slate-400" placeholder="Cari apa saja di EduNova..."/><button onClick={onClose} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100"><X size={16}/></button></div>
        <div className="max-h-[430px] overflow-y-auto p-2"><p className="px-3 py-2 text-[9px] font-bold uppercase tracking-[.14em] text-slate-400">Hasil pencarian</p>{filtered.map(c=><button key={c.label} onClick={()=>go(c.to)} className="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"><c.icon size={17} className="text-slate-400"/><span>{c.label}</span><span className="ml-auto rounded-md border border-slate-200 bg-white px-1.5 py-0.5 text-[9px] text-slate-400">{c.key}</span></button>)}{filtered.length===0&&<div className="px-3 py-10 text-center text-sm text-slate-400">Tidak ada hasil.</div>}</div>
      </motion.div>
    </>}
  </AnimatePresence>
}
