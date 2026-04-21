'use client'
import { useState } from 'react'
import { useRouter } from 'next/navigation'
import { registerApi } from '@/lib/api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Shield } from 'lucide-react'

export default function RegisterPage() {
  const router = useRouter()
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const [loading, setLoading] = useState(false)

  const handleRegister = async () => {
    setLoading(true)
    setError('')
    try {
      const form = new FormData()
      form.append('name', name)
      form.append('email', email)
      form.append('password', password)
      const res = await registerApi(form)
      if (res.message === 'Usuario registrado correctamente') {
        setSuccess('Registro exitoso. Redirigiendo...')
        setTimeout(() => router.push('/login'), 1500)
      } else {
        setError(res.message || 'Error al registrar')
      }
    } catch {
      setError('Error al conectar con el servidor')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-slate-950 flex items-center justify-center p-4">
      <Card className="w-full max-w-md bg-slate-900 border-slate-800">
        <CardHeader className="text-center space-y-4">
          <div className="flex justify-center">
            <div className="bg-blue-600 p-3 rounded-full">
              <Shield className="h-8 w-8 text-white" />
            </div>
          </div>
          <CardTitle className="text-2xl text-white">Crear cuenta</CardTitle>
          <CardDescription className="text-slate-400">
            Sistema de Gestión Penitenciaria
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {error && (
            <div className="bg-red-900/50 border border-red-700 text-red-300 px-4 py-2 rounded text-sm">
              {error}
            </div>
          )}
          {success && (
            <div className="bg-green-900/50 border border-green-700 text-green-300 px-4 py-2 rounded text-sm">
              {success}
            </div>
          )}
          <div className="space-y-2">
            <Label className="text-slate-300">Nombre</Label>
            <Input
              placeholder="Tu nombre"
              value={name}
              onChange={e => setName(e.target.value)}
              className="bg-slate-800 border-slate-700 text-white placeholder:text-slate-500"
            />
          </div>
          <div className="space-y-2">
            <Label className="text-slate-300">Email</Label>
            <Input
              type="email"
              placeholder="correo@ejemplo.com"
              value={email}
              onChange={e => setEmail(e.target.value)}
              className="bg-slate-800 border-slate-700 text-white placeholder:text-slate-500"
            />
          </div>
          <div className="space-y-2">
            <Label className="text-slate-300">Contraseña</Label>
            <Input
              type="password"
              placeholder="Mínimo 6 caracteres"
              value={password}
              onChange={e => setPassword(e.target.value)}
              className="bg-slate-800 border-slate-700 text-white placeholder:text-slate-500"
            />
          </div>
          <Button
            onClick={handleRegister}
            disabled={loading}
            className="w-full bg-blue-600 hover:bg-blue-700"
          >
            {loading ? 'Registrando...' : 'Registrarse'}
          </Button>
          <p className="text-center text-slate-400 text-sm">
            ¿Ya tienes cuenta?{' '}
            <a href="/login" className="text-blue-400 hover:underline">
              Inicia sesión
            </a>
          </p>
        </CardContent>
      </Card>
    </div>
  )
}