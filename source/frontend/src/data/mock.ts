export type Course = {
  id: string
  title: string
  teacher: string
  progress: number
  code: string
  category: string
  next: string
  icon: 'camera' | 'design' | 'type' | 'book' | 'math'
}

export const courses: Course[] = [
  { id:'fotografi', title:'Fotografi Digital', teacher:'Susanto, S.Kom', progress:72, code:'DKV • 04', category:'Konsentrasi Keahlian', next:'Komposisi & Framing', icon:'camera' },
  { id:'dkv', title:'Desain Komunikasi Visual', teacher:'Rina Pratiwi, S.Ds', progress:54, code:'DKV • 02', category:'Konsentrasi Keahlian', next:'Visual Hierarchy', icon:'design' },
  { id:'tipografi', title:'Tipografi Modern', teacher:'Aldi Saputra, S.Ds', progress:86, code:'DKV • 06', category:'Konsentrasi Keahlian', next:'Editorial Grid System', icon:'type' },
  { id:'bahasa', title:'Bahasa Indonesia', teacher:'Dewi Lestari, S.Pd', progress:64, code:'UMUM • 03', category:'Mata Pelajaran Umum', next:'Teks Editorial', icon:'book' },
  { id:'matematika', title:'Matematika', teacher:'Arif Rahman, S.Pd', progress:48, code:'UMUM • 05', category:'Mata Pelajaran Umum', next:'Transformasi Geometri', icon:'math' }
]

export const tasks = [
  { id:'poster', title:'Poster Kampanye Lingkungan', course:'Desain Komunikasi Visual', due:'12 Agu, 23.59', status:'Prioritas', progress:40 },
  { id:'exposure', title:'Foto Produk Manual Exposure', course:'Fotografi Digital', due:'14 Agu, 20.00', status:'Berjalan', progress:70 },
  { id:'grid', title:'Eksplorasi Grid Typography', course:'Tipografi Modern', due:'18 Agu, 23.59', status:'Baru', progress:10 }
]

export const notifications = [
  { id:1, type:'task', title:'Deadline besok', body:'Poster Kampanye Lingkungan harus dikumpulkan sebelum 23.59.', time:'12 menit lalu', unread:true },
  { id:2, type:'course', title:'Materi baru tersedia', body:'Chapter 05 — Komposisi & Framing sudah dibuka oleh guru.', time:'1 jam lalu', unread:true },
  { id:3, type:'grade', title:'Nilai diperbarui', body:'Nilai praktik Fotografi Digital sudah tersedia. Skor: 92.', time:'3 jam lalu', unread:false }
]

export const schedule = [
  { time:'07:00', title:'Fotografi Digital', meta:'Lab DKV', accent:'blue' },
  { time:'10:30', title:'Desain Komunikasi Visual', meta:'Ruang DKV 2', accent:'slate' },
  { time:'13:30', title:'Belajar mandiri', meta:'EduNova', accent:'slate' }
]
