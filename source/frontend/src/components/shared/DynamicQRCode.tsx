import { useEffect, useState } from 'react'
import QRCode from 'qrcode'

export function DynamicQRCode({ value, size=190 }: { value?: string | null; size?: number }) {
  const [src, setSrc] = useState('')

  useEffect(() => {
    let active = true
    if (!value) {
      setSrc('')
      return
    }

    QRCode.toDataURL(value, {
      width: size,
      margin: 1,
      errorCorrectionLevel: 'M',
      color: { dark: '#0F172A', light: '#FFFFFF' },
    }).then((url) => {
      if (active) setSrc(url)
    }).catch(() => {
      if (active) setSrc('')
    })

    return () => { active = false }
  }, [value, size])

  if (!src) {
    return <div className="grid h-[190px] w-[190px] place-items-center rounded-2xl bg-white/10 text-xs text-slate-300">
      Menyiapkan QR...
    </div>
  }

  return <img
    src={src}
    width={size}
    height={size}
    className="rounded-2xl bg-white p-2 shadow-xl"
    alt="Dynamic QR EduNova"
  />
}
