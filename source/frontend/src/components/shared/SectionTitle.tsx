import type { ReactNode } from 'react'
export function SectionTitle({ eyebrow, title, action }: { eyebrow?: string; title: string; action?: ReactNode }) {
  return <div className="mb-4 flex items-end justify-between gap-4"><div>{eyebrow&&<p className="text-[10px] font-bold uppercase tracking-[.14em] text-slate-400">{eyebrow}</p>}<h3 className="mt-1 text-base font-semibold tracking-[-.015em] text-slate-950">{title}</h3></div>{action}</div>
}
