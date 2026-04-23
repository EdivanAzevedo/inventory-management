import { tokenStore } from '../auth/token-store.js'

const BASE = '/api'

async function request(method, path, body = null) {
    const token = tokenStore.get()

    const opts = {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept:         'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
    }

    if (body !== null) opts.body = JSON.stringify(body)

    const res = await fetch(`${BASE}${path}`, opts)

    if (res.status === 401) {
        tokenStore.remove()
        window.location.href = '/login'
        return
    }

    if (res.status === 204) return null

    const json = await res.json().catch(() => null)

    if (!res.ok) {
        const message = json?.message ?? `Erro HTTP ${res.status}`
        const error = new Error(message)
        error.errors = json?.errors ?? {}
        throw error
    }

    return json
}

export const http = {
    get:    (path)        => request('GET',    path),
    post:   (path, body)  => request('POST',   path, body),
    put:    (path, body)  => request('PUT',    path, body),
    delete: (path)        => request('DELETE', path),
}
