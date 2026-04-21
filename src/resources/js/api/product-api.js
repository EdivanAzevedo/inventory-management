import { http } from './http-client.js'

export const productApi = {
    list:          ()                     => http.get('/products').then(r => r.data),
    listInactive:  ()                     => http.get('/products/inactive').then(r => r.data),
    get:           (id)                   => http.get(`/products/${id}`).then(r => r.data),
    create:        (data)                 => http.post('/products', data).then(r => r.data),
    update:        (id, data)             => http.put(`/products/${id}`, data).then(r => r.data),
    deactivate:    (id)                   => http.delete(`/products/${id}`),
    reactivate:    (id)                   => http.post(`/products/${id}/reactivate`),
    addVariant:    (productId, data)      => http.post(`/products/${productId}/variants`, data).then(r => r.data),
    removeVariant: (productId, variantId) => http.delete(`/products/${productId}/variants/${variantId}`),
}
