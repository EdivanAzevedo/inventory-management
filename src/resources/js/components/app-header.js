import { authApi }   from '../api/auth-api.js'
import { tokenStore } from '../auth/token-store.js'
import { userStore }  from '../auth/user-store.js'

const ROLE_LABELS = {
    admin:    'Admin',
    operator: 'Operador',
    viewer:   'Visualizador',
}

export function appHeader() {
    return {
        user: null,

        init() {
            if (!tokenStore.has()) {
                window.location.href = '/login'
                return
            }
            this.user = userStore.get()
        },

        roleLabel() {
            return ROLE_LABELS[this.user?.role] ?? this.user?.role
        },

        async logout() {
            try {
                await authApi.logout()
            } finally {
                tokenStore.remove()
                userStore.remove()
                window.location.href = '/login'
            }
        },
    }
}
