import type { HTMLAttributes } from 'react'
import { cn } from '@/lib/utils'

export function Card({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('surface-shadow rounded-[18px] border border-slate-200/90 bg-white', className)} {...props}/>
}
