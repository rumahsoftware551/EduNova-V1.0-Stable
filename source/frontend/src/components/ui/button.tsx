import type { ButtonHTMLAttributes, ReactNode } from 'react'
import { cn } from '@/lib/utils'

type Variant = 'primary' | 'secondary' | 'ghost' | 'dark' | 'danger'
type Size = 'sm' | 'md' | 'lg' | 'icon'

const variants: Record<Variant, string> = {
  primary: 'border-blue-600 bg-blue-600 text-white hover:border-blue-700 hover:bg-blue-700',
  secondary: 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50',
  ghost: 'border-transparent bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-950',
  dark: 'border-slate-950 bg-slate-950 text-white hover:bg-slate-800',
  danger: 'border-red-600 bg-red-600 text-white hover:bg-red-700'
}
const sizes: Record<Size, string> = {
  sm: 'min-h-9 rounded-[10px] px-3 py-2 text-xs',
  md: 'min-h-10 rounded-xl px-4 py-2.5 text-sm',
  lg: 'min-h-12 rounded-xl px-5 py-3 text-sm',
  icon: 'h-10 w-10 rounded-xl p-0'
}

export function Button({ variant='primary', size='md', className, children, ...props }: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: Variant; size?: Size; children?: ReactNode }) {
  return <button className={cn('focus-ring inline-flex items-center justify-center gap-2 border font-semibold shadow-sm transition disabled:pointer-events-none disabled:opacity-50', variants[variant], sizes[size], className)} {...props}>{children}</button>
}
