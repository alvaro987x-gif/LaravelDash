const BASE = 'http://localhost:8000/api'

export const getPresos = () =>
  fetch(`${BASE}/presos`).then(r => r.json())

export const getPresosAltoRiesgo = () =>
  fetch(`${BASE}/presosAltoRiesgo`).then(r => r.json())

export const getPresosProfugos = () =>
  fetch(`${BASE}/presosProfugos`).then(r => r.json())

export const getPresosDashboard = () =>
  fetch(`${BASE}/presosDashboard`).then(r => r.json())

export const getUsuarios = () =>
  fetch(`${BASE}/users`).then(r => r.json())

export const loginApi = (email: string, password: string) =>
  fetch(`${BASE}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  }).then(r => r.json())

export const registerApi = (data: FormData) =>
  fetch(`${BASE}/register`, { method: 'POST', body: data }).then(r => r.json())