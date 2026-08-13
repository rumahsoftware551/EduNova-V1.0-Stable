import { useEffect, useState } from 'react'
import { CloudOff, Wifi } from 'lucide-react'

export function OfflineStatus() {
  const [online, setOnline] = useState(() => navigator.onLine)
  const [justBack, setJustBack] = useState(false)

  useEffect(() => {
    const onOnline = () => {
      setOnline(true)
      setJustBack(true)
      window.setTimeout(() => setJustBack(false), 2500)
    }
    const onOffline = () => setOnline(false)

    window.addEventListener('online', onOnline)
    window.addEventListener('offline', onOffline)
    return () => {
      window.removeEventListener('online', onOnline)
      window.removeEventListener('offline', onOffline)
    }
  }, [])

  if (!online) {
    return <div className="fixed bottom-24 left-1/2 z-[90] -translate-x-1/2 rounded-full bg-slate-950 px-4 py-2 text-xs font-semibold text-white shadow-xl lg:bottom-5">
      <CloudOff size={14} className="mr-2 inline"/> Offline — data baru belum dapat dimuat
    </div>
  }

  if (justBack) {
    return <div className="fixed bottom-24 left-1/2 z-[90] -translate-x-1/2 rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-xl lg:bottom-5">
      <Wifi size={14} className="mr-2 inline"/> Koneksi kembali aktif
    </div>
  }

  return null
}
