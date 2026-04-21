'use client'
import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { getPresos, getPresosDashboard, getUsuarios } from '@/lib/api'
import { Preso, Usuario } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Shield, Users, AlertTriangle, UserX, LogOut, Eye, Pencil, Plus, User } from 'lucide-react'
import { PieChart, Pie, Cell, BarChart, Bar, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer } from 'recharts'

export default function DashboardPage() {
  const router = useRouter()
  const [presos, setPresos] = useState<Preso[]>([])
  const [usuarios, setUsuarios] = useState<Usuario[]>([])
  const [stats, setStats] = useState<any>(null)
  const [tab, setTab] = useState<'presos' | 'usuarios'>('presos')
  const [loading, setLoading] = useState(true)

  const [presoSeleccionado, setPresoSeleccionado] = useState<Preso | null>(null)
  const [modalVer, setModalVer] = useState(false)
  const [modalEditar, setModalEditar] = useState(false)
  const [modalAnadir, setModalAnadir] = useState(false)
  const [modalPerfil, setModalPerfil] = useState(false)

  const [editForm, setEditForm] = useState<any>({})
  const [editImagen, setEditImagen] = useState<File | null>(null)
  const [addForm, setAddForm] = useState<any>({
    nombre: '', apellido: '', dni: '', delito: '', condena: '',
    estado: 'Encarcelado', nivel_peligrosidad: 'Bajo',
    fecha_ingreso: '', fecha_salida: '', observaciones: ''
  })
  const [addImagen, setAddImagen] = useState<File | null>(null)
  const [formMsg, setFormMsg] = useState('')

  const [perfilForm, setPerfilForm] = useState({ name: '', current_password: '', new_password: '', new_password_confirmation: '' })
  const [perfilAvatar, setPerfilAvatar] = useState<File | null>(null)
  const [perfilMsg, setPerfilMsg] = useState('')
  const [showCambiarPass, setShowCambiarPass] = useState(false)

  const currentUser = typeof window !== 'undefined'
    ? JSON.parse(localStorage.getItem('user') || '{}')
    : {}

  useEffect(() => {
    const user = localStorage.getItem('user')
    if (!user) localStorage.setItem('user', JSON.stringify({ email: 'google' }))
    cargarDatos()
  }, [])

  const cargarDatos = () => {
    Promise.all([getPresos(), getPresosDashboard(), getUsuarios()])
      .then(([p, s, u]) => { setPresos(p); setStats(s); setUsuarios(u) })
      .finally(() => setLoading(false))
  }

  const handleLogout = () => {
    localStorage.removeItem('user')
    router.push('/login')
  }

  const handleVerPreso = (p: Preso) => {
    setPresoSeleccionado(p)
    setModalVer(true)
  }

  const handleEditarPreso = (p: Preso) => {
    setPresoSeleccionado(p)
    setEditForm({ ...p })
    setEditImagen(null)
    setModalEditar(true)
  }

  const handleGuardarEdicion = async () => {
    const form = new FormData()
    Object.keys(editForm).forEach(k => form.append(k, editForm[k]))
    if (editImagen) form.append('imagen', editImagen)
    const res = await fetch('http://localhost:8000/api/editarPreso', { method: 'POST', body: form })
    const data = await res.json()
    if (res.ok) {
      setModalEditar(false)
      setFormMsg(data.message)
      cargarDatos()
      setTimeout(() => setFormMsg(''), 3000)
    } else {
      setFormMsg('❌ ' + data.message)
      setTimeout(() => setFormMsg(''), 4000)
    }
  }

  const handleAnadirPreso = async () => {
    const form = new FormData()
    Object.keys(addForm).forEach(k => form.append(k, addForm[k]))
    if (addImagen) form.append('imagen', addImagen)
    const res = await fetch('http://localhost:8000/api/añadirPreso', { method: 'POST', body: form })
    const data = await res.json()
    if (res.ok) {
      setModalAnadir(false)
      setAddForm({
        nombre: '', apellido: '', dni: '', delito: '', condena: '',
        estado: 'Encarcelado', nivel_peligrosidad: 'Bajo',
        fecha_ingreso: '', fecha_salida: '', observaciones: ''
      })
      setAddImagen(null)
      setFormMsg(data.message)
      cargarDatos()
      setTimeout(() => setFormMsg(''), 3000)
    } else {
      setFormMsg('❌ ' + data.message)
      setTimeout(() => setFormMsg(''), 4000)
    }
  }

  const handleEditarPerfil = async () => {
    const form = new FormData()
    form.append('name', perfilForm.name)
    if (perfilAvatar) form.append('avatar', perfilAvatar)
    const res = await fetch('http://localhost:8000/api/updateProfile', { method: 'POST', body: form })
    const data = await res.json()
    setPerfilMsg(data.message || 'Perfil actualizado.')
    setTimeout(() => setPerfilMsg(''), 3000)
  }

  const handleCambiarPassword = async () => {
    const form = new FormData()
    form.append('current_password', perfilForm.current_password)
    form.append('new_password', perfilForm.new_password)
    form.append('new_password_confirmation', perfilForm.new_password_confirmation)
    const res = await fetch('http://localhost:8000/api/updatePassword', { method: 'POST', body: form })
    const data = await res.json()
    setPerfilMsg(data.message || 'Contraseña actualizada.')
    setTimeout(() => setPerfilMsg(''), 3000)
  }

  const peligrosidadColor: Record<string, any> = {
    Alto: 'destructive', Medio: 'secondary', Bajo: 'outline',
  }
  const estadoColor: Record<string, string> = {
    Encarcelado: 'bg-blue-900/50 text-blue-300 border-blue-700',
    Profugo: 'bg-red-900/50 text-red-300 border-red-700',
  }

  const pieData = [
    { name: 'Encarcelados', value: stats ? stats.totalPresos - stats.totalPresosProfugos : 0 },
    { name: 'Prófugos', value: stats ? stats.totalPresosProfugos : 0 },
  ]
  const PIE_COLORS = ['#4CAF50', '#f44336']
  const barData = [
    { nivel: 'Bajo', cantidad: stats?.totalPresosBajo ?? 0, fill: '#4CAF50' },
    { nivel: 'Medio', cantidad: stats?.totalPresosMedio ?? 0, fill: '#FF9800' },
    { nivel: 'Alto', cantidad: stats?.totalPresosAltoRiesgo ?? 0, fill: '#f44336' },
  ]

  if (loading) return (
    <div className="min-h-screen bg-slate-950 flex items-center justify-center">
      <p className="text-slate-400">Cargando...</p>
    </div>
  )

  return (
    <div className="min-h-screen bg-slate-950 text-white flex">

      {/* Sidebar */}
      <aside className="w-52 bg-slate-900 border-r border-slate-800 flex flex-col p-4 gap-2">
        <div className="flex items-center gap-2 mb-6">
          <div className="bg-blue-600 p-2 rounded-full">
            <Shield className="h-5 w-5 text-white" />
          </div>
          <span className="text-lg font-bold">FLAP</span>
        </div>
        <Button variant="ghost" className="justify-start text-slate-300 hover:text-white" onClick={() => setTab('usuarios')}>
          <Users className="h-4 w-4 mr-2" /> Usuarios
        </Button>
        <Button variant="ghost" className="justify-start text-slate-300 hover:text-white" onClick={() => setTab('presos')}>
          <Shield className="h-4 w-4 mr-2" /> Presos
        </Button>
        <Button variant="ghost" className="justify-start text-slate-300 hover:text-white"
          onClick={() => { setPerfilForm({ ...perfilForm, name: currentUser.name || '' }); setModalPerfil(true) }}>
          <User className="h-4 w-4 mr-2" /> Perfil
        </Button>
        <Button variant="ghost" className="justify-start text-slate-300 hover:text-white" onClick={() => setModalAnadir(true)}>
          <Plus className="h-4 w-4 mr-2" /> Añadir preso
        </Button>
        <div className="mt-auto">
          <Button variant="ghost" onClick={handleLogout} className="justify-start text-slate-400 hover:text-white w-full">
            <LogOut className="h-4 w-4 mr-2" /> Cerrar sesión
          </Button>
        </div>
      </aside>

      {/* Main */}
      <main className="flex-1 p-6 space-y-6 overflow-auto">
        <h2 className="text-2xl font-bold">Bienvenido al Dashboard</h2>

        {formMsg && (
          <div className={`border px-4 py-2 rounded text-sm ${formMsg.startsWith('❌') ? 'bg-red-900/50 border-red-700 text-red-300' : 'bg-green-900/50 border-green-700 text-green-300'}`}>
            {formMsg}
          </div>
        )}

        {/* Stats */}
        {stats && (
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Card className="bg-slate-900 border-slate-800">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm text-slate-400 flex items-center gap-2">
                  <Shield className="h-4 w-4" /> Total Presos
                </CardTitle>
              </CardHeader>
              <CardContent><p className="text-3xl font-bold text-white">{stats.totalPresos}</p></CardContent>
            </Card>
            <Card className="bg-slate-900 border-slate-800">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm text-slate-400 flex items-center gap-2">
                  <AlertTriangle className="h-4 w-4 text-red-400" /> Alto Riesgo
                </CardTitle>
              </CardHeader>
              <CardContent><p className="text-3xl font-bold text-red-400">{stats.totalPresosAltoRiesgo}</p></CardContent>
            </Card>
            <Card className="bg-slate-900 border-slate-800">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm text-slate-400 flex items-center gap-2">
                  <UserX className="h-4 w-4 text-orange-400" /> Prófugos
                </CardTitle>
              </CardHeader>
              <CardContent><p className="text-3xl font-bold text-orange-400">{stats.totalPresosProfugos}</p></CardContent>
            </Card>
            <Card className="bg-slate-900 border-slate-800">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm text-slate-400 flex items-center gap-2">
                  <Users className="h-4 w-4 text-blue-400" /> Usuarios
                </CardTitle>
              </CardHeader>
              <CardContent><p className="text-3xl font-bold text-blue-400">{usuarios.length}</p></CardContent>
            </Card>
          </div>
        )}

        {/* Gráficos */}
        {stats && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Card className="bg-slate-900 border-slate-800">
              <CardHeader><CardTitle className="text-white">Estado de Presos</CardTitle></CardHeader>
              <CardContent className="flex justify-center">
                <ResponsiveContainer width="100%" height={280}>
                  <PieChart>
                    <Pie data={pieData} cx="50%" cy="50%" outerRadius={100} dataKey="value"
                      label={({ name, value }) => `${name}: ${value}`}>
                      {pieData.map((_, i) => <Cell key={i} fill={PIE_COLORS[i]} />)}
                    </Pie>
                    <Legend /><Tooltip />
                  </PieChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
            <Card className="bg-slate-900 border-slate-800">
              <CardHeader><CardTitle className="text-white">Nivel de Peligrosidad</CardTitle></CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={280}>
                  <BarChart data={barData}>
                    <XAxis dataKey="nivel" stroke="#94a3b8" />
                    <YAxis stroke="#94a3b8" allowDecimals={false} />
                    <Tooltip contentStyle={{ backgroundColor: '#1e293b', border: '1px solid #334155', color: '#fff' }} />
                    <Bar dataKey="cantidad" name="Cantidad de presos" radius={[4, 4, 0, 0]}>
                      {barData.map((e, i) => <Cell key={i} fill={e.fill} />)}
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Tabla Presos */}
        {tab === 'presos' && (
          <Card className="bg-slate-900 border-slate-800">
            <CardHeader><CardTitle className="text-white">Lista de Presos</CardTitle></CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow className="border-slate-700">
                    <TableHead className="text-slate-400">Nombre</TableHead>
                    <TableHead className="text-slate-400">DNI</TableHead>
                    <TableHead className="text-slate-400">Peligrosidad</TableHead>
                    <TableHead className="text-slate-400">Estado</TableHead>
                    <TableHead className="text-slate-400">Acciones</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {presos.map(p => (
                    <TableRow key={p.id} className="border-slate-700 hover:bg-slate-800">
                      <TableCell className="text-white font-medium">{p.nombre} {p.apellido}</TableCell>
                      <TableCell className="text-slate-300">{p.dni}</TableCell>
                      <TableCell>
                        <Badge variant={peligrosidadColor[p.nivel_peligrosidad]}>{p.nivel_peligrosidad}</Badge>
                      </TableCell>
                      <TableCell>
                        <span className={`text-xs px-2 py-1 rounded border ${estadoColor[p.estado]}`}>{p.estado}</span>
                      </TableCell>
                      <TableCell className="flex gap-2">
                        <Button size="sm" variant="ghost" onClick={() => handleVerPreso(p)} className="text-blue-400 hover:text-blue-300">
                          <Eye className="h-4 w-4 mr-1" /> Ver
                        </Button>
                        <Button size="sm" variant="ghost" onClick={() => handleEditarPreso(p)} className="text-yellow-400 hover:text-yellow-300">
                          <Pencil className="h-4 w-4 mr-1" /> Editar
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        )}

        {/* Tabla Usuarios */}
        {tab === 'usuarios' && (
          <Card className="bg-slate-900 border-slate-800">
            <CardHeader><CardTitle className="text-white">Lista de Usuarios</CardTitle></CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow className="border-slate-700">
                    <TableHead className="text-slate-400">Nombre</TableHead>
                    <TableHead className="text-slate-400">Email</TableHead>
                    <TableHead className="text-slate-400">Rol</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {usuarios.map(u => (
                    <TableRow key={u.id} className="border-slate-700 hover:bg-slate-800">
                      <TableCell className="text-white">{u.name}</TableCell>
                      <TableCell className="text-slate-300">{u.email}</TableCell>
                      <TableCell>
                        <Badge variant={u.rol === 'sargento' ? 'default' : 'secondary'}>{u.rol}</Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        )}
      </main>

      {/* Modal Ver Preso */}
      <Dialog open={modalVer} onOpenChange={setModalVer}>
        <DialogContent className="bg-slate-900 border-slate-700 text-white">
          <DialogHeader><DialogTitle>Detalle del Preso</DialogTitle></DialogHeader>
          {presoSeleccionado && (
            <div className="space-y-2 text-sm">
              {presoSeleccionado.imagen && (
                <img src={`http://localhost:8000/storage/${presoSeleccionado.imagen}`}
                  className="w-24 h-24 rounded-full object-cover mb-4" alt="foto" />
              )}
              <p><span className="text-slate-400">Nombre:</span> {presoSeleccionado.nombre} {presoSeleccionado.apellido}</p>
              <p><span className="text-slate-400">DNI:</span> {presoSeleccionado.dni}</p>
              <p><span className="text-slate-400">Delito:</span> {presoSeleccionado.delito}</p>
              <p><span className="text-slate-400">Condena:</span> {presoSeleccionado.condena} años</p>
              <p><span className="text-slate-400">Estado:</span> {presoSeleccionado.estado}</p>
              <p><span className="text-slate-400">Peligrosidad:</span> {presoSeleccionado.nivel_peligrosidad}</p>
              <p><span className="text-slate-400">Fecha ingreso:</span> {presoSeleccionado.fecha_ingreso}</p>
              <p><span className="text-slate-400">Fecha salida:</span> {presoSeleccionado.fecha_salida}</p>
              <p><span className="text-slate-400">Observaciones:</span> {presoSeleccionado.observaciones}</p>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Modal Editar Preso */}
      <Dialog open={modalEditar} onOpenChange={setModalEditar}>
        <DialogContent className="bg-slate-900 border-slate-700 text-white max-h-[80vh] overflow-y-auto">
          <DialogHeader><DialogTitle>Editar Preso</DialogTitle></DialogHeader>
          <div className="space-y-3 text-sm">
            {['nombre','apellido','dni','delito','condena','fecha_ingreso','fecha_salida','observaciones'].map(field => (
              <div key={field}>
                <Label className="text-slate-400 capitalize">{field.replace('_', ' ')}</Label>
                <Input value={editForm[field] || ''} onChange={e => setEditForm({...editForm, [field]: e.target.value})}
                  className="bg-slate-800 border-slate-700 text-white mt-1" />
              </div>
            ))}
            <div>
              <Label className="text-slate-400">Imagen</Label>
              <input type="file" accept="image/*" onChange={e => setEditImagen(e.target.files?.[0] || null)}
                className="w-full mt-1 text-slate-300 bg-slate-800 border border-slate-700 rounded px-3 py-2" />
            </div>
            <div>
              <Label className="text-slate-400">Estado</Label>
              <select value={editForm.estado || 'Encarcelado'} onChange={e => setEditForm({...editForm, estado: e.target.value})}
                className="w-full mt-1 bg-slate-800 border border-slate-700 text-white rounded px-3 py-2">
                <option value="Encarcelado">Encarcelado</option>
                <option value="Profugo">Profugo</option>
              </select>
            </div>
            <div>
              <Label className="text-slate-400">Nivel de peligrosidad</Label>
              <select value={editForm.nivel_peligrosidad || 'Bajo'} onChange={e => setEditForm({...editForm, nivel_peligrosidad: e.target.value})}
                className="w-full mt-1 bg-slate-800 border border-slate-700 text-white rounded px-3 py-2">
                <option value="Bajo">Bajo</option>
                <option value="Medio">Medio</option>
                <option value="Alto">Alto</option>
              </select>
            </div>
            <Button onClick={handleGuardarEdicion} className="w-full bg-blue-600 hover:bg-blue-700">Guardar cambios</Button>
          </div>
        </DialogContent>
      </Dialog>

      {/* Modal Añadir Preso */}
      <Dialog open={modalAnadir} onOpenChange={setModalAnadir}>
        <DialogContent className="bg-slate-900 border-slate-700 text-white max-h-[80vh] overflow-y-auto">
          <DialogHeader><DialogTitle>Añadir Preso</DialogTitle></DialogHeader>
          <div className="space-y-3 text-sm">
            {['nombre','apellido','dni','delito','condena','fecha_ingreso','fecha_salida','observaciones'].map(field => (
              <div key={field}>
                <Label className="text-slate-400 capitalize">{field.replace('_', ' ')}</Label>
                <Input value={addForm[field] || ''} onChange={e => setAddForm({...addForm, [field]: e.target.value})}
                  className="bg-slate-800 border-slate-700 text-white mt-1" />
              </div>
            ))}
            <div>
              <Label className="text-slate-400">Imagen</Label>
              <input type="file" accept="image/*" onChange={e => setAddImagen(e.target.files?.[0] || null)}
                className="w-full mt-1 text-slate-300 bg-slate-800 border border-slate-700 rounded px-3 py-2" />
            </div>
            <div>
              <Label className="text-slate-400">Estado</Label>
              <select value={addForm.estado} onChange={e => setAddForm({...addForm, estado: e.target.value})}
                className="w-full mt-1 bg-slate-800 border border-slate-700 text-white rounded px-3 py-2">
                <option value="Encarcelado">Encarcelado</option>
                <option value="Profugo">Profugo</option>
              </select>
            </div>
            <div>
              <Label className="text-slate-400">Nivel de peligrosidad</Label>
              <select value={addForm.nivel_peligrosidad} onChange={e => setAddForm({...addForm, nivel_peligrosidad: e.target.value})}
                className="w-full mt-1 bg-slate-800 border border-slate-700 text-white rounded px-3 py-2">
                <option value="Bajo">Bajo</option>
                <option value="Medio">Medio</option>
                <option value="Alto">Alto</option>
              </select>
            </div>
            <Button onClick={handleAnadirPreso} className="w-full bg-blue-600 hover:bg-blue-700">Guardar preso</Button>
          </div>
        </DialogContent>
      </Dialog>

      {/* Modal Perfil */}
      <Dialog open={modalPerfil} onOpenChange={setModalPerfil}>
        <DialogContent className="bg-slate-900 border-slate-700 text-white max-h-[80vh] overflow-y-auto">
          <DialogHeader><DialogTitle>Mi Perfil</DialogTitle></DialogHeader>
          <div className="space-y-4 text-sm">
            {perfilMsg && (
              <div className="bg-green-900/50 border border-green-700 text-green-300 px-3 py-2 rounded">{perfilMsg}</div>
            )}
            {currentUser.avatar && (
              <img src={currentUser.avatar.startsWith('http') ? currentUser.avatar : `http://localhost:8000/storage/${currentUser.avatar}`}
                className="w-20 h-20 rounded-full object-cover" alt="avatar" />
            )}
            <p><span className="text-slate-400">Email:</span> {currentUser.email}</p>
            <p><span className="text-slate-400">Rol:</span> {currentUser.rol || '—'}</p>

            <div className="border-t border-slate-700 pt-4 space-y-3">
              <h4 className="text-white font-medium">Editar perfil</h4>
              <div>
                <Label className="text-slate-400">Nombre</Label>
                <Input value={perfilForm.name} onChange={e => setPerfilForm({...perfilForm, name: e.target.value})}
                  className="bg-slate-800 border-slate-700 text-white mt-1" />
              </div>
              <div>
                <Label className="text-slate-400">Avatar</Label>
                <input type="file" accept="image/*" onChange={e => setPerfilAvatar(e.target.files?.[0] || null)}
                  className="w-full mt-1 text-slate-300 bg-slate-800 border border-slate-700 rounded px-3 py-2" />
              </div>
              <Button onClick={handleEditarPerfil} className="w-full bg-blue-600 hover:bg-blue-700">Guardar perfil</Button>
            </div>

            <div className="border-t border-slate-700 pt-4 space-y-3">
              <button onClick={() => setShowCambiarPass(!showCambiarPass)}
                className="text-blue-400 hover:underline text-sm">
                {showCambiarPass ? 'Cancelar' : 'Cambiar contraseña'}
              </button>
              {showCambiarPass && (
                <div className="space-y-3">
                  <div>
                    <Label className="text-slate-400">Contraseña actual</Label>
                    <Input type="password" value={perfilForm.current_password}
                      onChange={e => setPerfilForm({...perfilForm, current_password: e.target.value})}
                      className="bg-slate-800 border-slate-700 text-white mt-1" />
                  </div>
                  <div>
                    <Label className="text-slate-400">Nueva contraseña</Label>
                    <Input type="password" value={perfilForm.new_password}
                      onChange={e => setPerfilForm({...perfilForm, new_password: e.target.value})}
                      className="bg-slate-800 border-slate-700 text-white mt-1" />
                  </div>
                  <div>
                    <Label className="text-slate-400">Confirmar contraseña</Label>
                    <Input type="password" value={perfilForm.new_password_confirmation}
                      onChange={e => setPerfilForm({...perfilForm, new_password_confirmation: e.target.value})}
                      className="bg-slate-800 border-slate-700 text-white mt-1" />
                  </div>
                  <Button onClick={handleCambiarPassword} className="w-full bg-blue-600 hover:bg-blue-700">
                    Guardar contraseña
                  </Button>
                </div>
              )}
            </div>
          </div>
        </DialogContent>
      </Dialog>

    </div>
  )
}