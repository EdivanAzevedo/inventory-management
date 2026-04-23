import { authApi }   from '../api/auth-api.js'
import { tokenStore } from '../auth/token-store.js'
import { userStore }  from '../auth/user-store.js'

export function authLogin() {
    return {
        form:       { email: '', password: '' },
        error:      null,
        submitting: false,

        init() {
            if (tokenStore.has()) {
                window.location.href = '/products'
            }
        },

        async submit() {
            this.error      = null
            this.submitting = true

            try {
                const result = await authApi.login(this.form.email, this.form.password)
                tokenStore.set(result.token)
                userStore.set(result.data)
                window.location.href = '/products'
            } catch (e) {
                this.error = e.message
            } finally {
                this.submitting = false
            }
        },
    }
}
