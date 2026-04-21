export interface Preso {
  id: number
  nombre: string
  apellido: string
  dni: string
  delito: string
  condena: number
  estado: 'Encarcelado' | 'Profugo'
  imagen: string | null
  fecha_ingreso: string
  fecha_salida: string | null
  observaciones: string | null
  nivel_peligrosidad: 'Bajo' | 'Medio' | 'Alto'
  created_at: string
  updated_at: string
}

export interface Usuario {
  id: number
  name: string
  email: string
  avatar: string | null
  rol: 'sargento' | 'policia'
  created_at: string
  updated_at: string
}