const KEY = 'auth_user'

export const userStore = {
    get:    () => { const s = localStorage.getItem(KEY); return s ? JSON.parse(s) : null },
    set:    (user) => localStorage.setItem(KEY, JSON.stringify(user)),
    remove: ()     => localStorage.removeItem(KEY),
}
