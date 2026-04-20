const BASE = '/api'

async function request(method, path, body = null) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    }

    if (body !== null) opts.body = JSON.stringify(body)

    const res = await fetch(`${BASE}${path}`, opts)

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
