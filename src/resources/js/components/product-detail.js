import { productApi } from '../api/product-api.js'

const emptyVariantForm = () => ({
    sku: '', unit: 'UN', minimum_stock: 0, color: '', size: '',
})

export function productDetail(productId) {
    return {
        product:     null,
        loading:     true,
        showForm:    false,
        submitting:  false,
        toast:       null,
        form:        emptyVariantForm(),

        async init() {
            await this.load()
        },

        async load() {
            this.loading = true
            try {
                this.product = await productApi.get(productId)
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.loading = false
            }
        },

        async addVariant() {
            this.submitting = true
            try {
                await productApi.addVariant(productId, this.form)
                this.showToast('Variante adicionada!', 'success')
                this.showForm = false
                this.form = emptyVariantForm()
                await this.load()
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.submitting = false
            }
        },

        async removeVariant(variantId, sku) {
            if (!confirm(`Remover variante "${sku}"?`)) return
            try {
                await productApi.removeVariant(productId, variantId)
                this.showToast('Variante removida.', 'success')
                await this.load()
            } catch (e) {
                this.showToast(e.message, 'error')
            }
        },

        copyId(id) {
            navigator.clipboard.writeText(id)
            this.showToast('ID copiado!', 'success')
        },

        showToast(message, type = 'success') {
            this.toast = { message, type }
            setTimeout(() => { this.toast = null }, 4000)
        },
    }
}
