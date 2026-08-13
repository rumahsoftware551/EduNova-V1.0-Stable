import { ArrowUpRight, BookOpen, Camera, PenTool, Sigma, Type } from 'lucide-react'
import { motion } from 'motion/react'
import { Link } from 'react-router'
import { Card, Progress } from '@/components/ui'
import type { Course } from '@/data/mock'

const iconMap = {
  camera: Camera,
  design: PenTool,
  type: Type,
  book: BookOpen,
  math: Sigma
}

export function CourseCard({ course }: { course: Course }) {
  const Icon = iconMap[course.icon]
  return <motion.div whileHover={{ y: -3 }} transition={{ duration: .18 }}>
    <Card className="group overflow-hidden p-0 transition hover:border-slate-300 hover:shadow-[0_18px_45px_rgba(15,23,42,.07)]">
      <div className="relative h-32 overflow-hidden bg-[#0D1B36] p-5 text-white dashboard-grid">
        <div className="absolute -right-10 -top-12 h-40 w-40 rounded-full border-[24px] border-white/[.045]"/>
        <div className="relative flex h-full flex-col justify-between">
          <div className="flex items-start justify-between">
            <div className="grid h-10 w-10 place-items-center rounded-xl border border-white/10 bg-white/[.07]"><Icon size={19}/></div>
            <Link to={`/courses/${course.id}`} aria-label={`Buka ${course.title}`} className="grid h-9 w-9 place-items-center rounded-xl border border-white/10 bg-white/[.06] text-white/80 transition group-hover:bg-white group-hover:text-slate-950"><ArrowUpRight size={16}/></Link>
          </div>
          <div><p className="text-[10px] font-bold uppercase tracking-[.15em] text-blue-200/80">{course.code}</p><h3 className="mt-1 text-base font-semibold tracking-[-.02em]">{course.title}</h3></div>
        </div>
      </div>
      <div className="p-5">
        <div className="flex items-start justify-between gap-4"><div><p className="text-[11px] text-slate-400">Pengajar</p><p className="mt-1 text-sm font-semibold text-slate-800">{course.teacher}</p></div><span className="text-sm font-bold text-blue-700">{course.progress}%</span></div>
        <Progress value={course.progress} className="mt-4"/>
        <p className="mt-3 truncate text-xs text-slate-500">Berikutnya: {course.next}</p>
      </div>
    </Card>
  </motion.div>
}
