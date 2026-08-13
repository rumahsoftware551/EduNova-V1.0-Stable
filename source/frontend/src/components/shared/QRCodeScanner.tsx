import { useEffect, useRef, useState } from 'react'
import { Camera, CameraOff, ScanLine, X } from 'lucide-react'
import { Button } from '@/components/ui'

type Props = {
  open: boolean
  onClose: () => void
  onScan: (value: string) => void
}

type BarcodeResult = { rawValue?: string }

export function QRCodeScanner({ open, onClose, onScan }: Props) {
  const videoRef = useRef<HTMLVideoElement>(null)
  const streamRef = useRef<MediaStream | null>(null)
  const timerRef = useRef<number | null>(null)
  const [error, setError] = useState('')
  const [ready, setReady] = useState(false)

  useEffect(() => {
    if (!open) return

    let cancelled = false

    async function start() {
      setError('')
      setReady(false)

      const BarcodeDetectorCtor = (window as unknown as {
        BarcodeDetector?: new (options: { formats: string[] }) => {
          detect: (source: HTMLVideoElement) => Promise<BarcodeResult[]>
        }
      }).BarcodeDetector

      if (!BarcodeDetectorCtor) {
        setError('Browser ini belum mendukung pemindaian QR otomatis. Gunakan Chrome/Edge terbaru atau masukkan kode secara manual.')
        return
      }

      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1280 },
            height: { ideal: 720 },
          },
          audio: false,
        })

        if (cancelled) {
          stream.getTracks().forEach(track => track.stop())
          return
        }

        streamRef.current = stream
        const video = videoRef.current
        if (!video) return

        video.srcObject = stream
        await video.play()
        setReady(true)

        const detector = new BarcodeDetectorCtor({ formats: ['qr_code'] })

        const scan = async () => {
          if (cancelled || !videoRef.current) return
          try {
            const results = await detector.detect(videoRef.current)
            const value = results[0]?.rawValue?.trim()
            if (value) {
              onScan(value.toUpperCase())
              onClose()
              return
            }
          } catch {
            // Continue scanning. Individual frames can fail harmlessly.
          }
          timerRef.current = window.setTimeout(scan, 220)
        }

        timerRef.current = window.setTimeout(scan, 350)
      } catch {
        setError('Kamera tidak dapat dibuka. Pastikan izin kamera diberikan dan tidak sedang digunakan aplikasi lain.')
      }
    }

    start()

    return () => {
      cancelled = true
      if (timerRef.current) window.clearTimeout(timerRef.current)
      streamRef.current?.getTracks().forEach(track => track.stop())
      streamRef.current = null
    }
  }, [open, onClose, onScan])

  if (!open) return null

  return <div className="fixed inset-0 z-[110] grid place-items-center bg-slate-950/80 p-4 backdrop-blur-sm">
    <div className="w-full max-w-lg overflow-hidden rounded-[24px] bg-white shadow-2xl">
      <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
          <p className="text-xs font-bold uppercase tracking-[.14em] text-blue-600">QR Scanner</p>
          <h3 className="font-semibold">Arahkan kamera ke QR guru</h3>
        </div>
        <Button variant="ghost" size="icon" onClick={onClose}><X size={18}/></Button>
      </div>

      <div className="relative aspect-square bg-slate-950">
        <video ref={videoRef} playsInline muted className="h-full w-full object-cover"/>
        {ready && <>
          <div className="pointer-events-none absolute inset-[14%] rounded-[24px] border-2 border-white/90 shadow-[0_0_0_999px_rgba(15,23,42,.28)]"/>
          <ScanLine className="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white" size={46}/>
        </>}
        {!ready && !error && <div className="absolute inset-0 grid place-items-center text-center text-white">
          <div><Camera className="mx-auto mb-3" size={40}/><p className="text-sm">Membuka kamera...</p></div>
        </div>}
      </div>

      <div className="p-5">
        {error ? <div className="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
          <CameraOff size={17} className="mr-2 inline"/>{error}
        </div> : <p className="text-sm leading-6 text-slate-500">
          QR akan terbaca otomatis. Tidak perlu mengambil foto.
        </p>}
      </div>
    </div>
  </div>
}
