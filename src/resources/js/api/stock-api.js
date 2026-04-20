import { http } from './http-client.js'

export const stockApi = {
    recordEntry:     (data)          => http.post('/stock/entries', data).then(r => r.data),
    recordExit:      (data)          => http.post('/stock/exits', data).then(r => r.data),
    cancelMovement:  (id, reason)    => http.post(`/stock/movements/${id}/cancel`, { reason }).then(r => r.data),
    getBalance:      (variantId)     => http.get(`/stock/balance/${variantId}`).then(r => r.data),
    getMovements:    (variantId)     => http.get(`/stock/movements/${variantId}`).then(r => r.data),
}
