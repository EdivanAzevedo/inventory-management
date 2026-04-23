const KEY = 'auth_token'

export const tokenStore = {
    get:    ()      => localStorage.getItem(KEY),
    set:    (token) => localStorage.setItem(KEY, token),
    remove: ()      => localStorage.removeItem(KEY),
    has:    ()      => !!localStorage.getItem(KEY),
}
