import type { HTMLAttributes } from 'react'
import { cn } from '@/lib/utils'

type Tone = 'blue' | 'green' | 'amber' | 'red' | 'slate'
const tones: Record<Tone, string> = {
  blue: 'border-blue-100 bg-blue-50 text-blue-700',
  green: 'border-emerald-100 bg-emerald-50 text-emerald-700',
  amber: 'border-amber-100 bg-amber-50 text-amber-700',
  red: 'border-red-100 bg-red-50 text-red-700',
  slate: 'border-slate-200 bg-slate-50 text-slate-600'
}
export function Badge({ tone='slate', className, ...props }: HTMLAttributes<HTMLSpanElement> & { tone?: Tone }) {
  return <span className={cn('inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-[10px] font-bold uppercase tracking-[.08em]', tones[tone], className)} {...props}/>
}
