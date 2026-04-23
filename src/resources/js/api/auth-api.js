import { http } from './http-client.js'

export const authApi = {
    login:  (email, password) => http.post('/auth/login',  { email, password }),
    logout: ()                => http.post('/auth/logout'),
}
