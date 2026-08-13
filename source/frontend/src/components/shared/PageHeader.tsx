import type { ReactNode } from 'react'
export function PageHeader({ eyebrow, title, description, action }: { eyebrow?: string; title: string; description?: string; action?: ReactNode }) {
  return <section className="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p className="text-[11px] font-semibold uppercase tracking-[.14em] text-slate-400">{eyebrow}</p><h2 className="mt-1 text-2xl font-bold tracking-[-.035em] text-slate-950 sm:text-[30px]">{title}</h2>{description&&<p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{description}</p>}</div>{action&&<div className="shrink-0">{action}</div>}</section>
}
