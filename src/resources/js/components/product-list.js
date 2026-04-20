import { productApi } from '../api/product-api.js'

const TYPES = {
    PRODUTO_FINAL: { label: 'Produto Final', class: 'bg-blue-100 text-blue-700' },
    MATERIA_PRIMA: { label: 'Matéria-Prima', class: 'bg-amber-100 text-amber-700' },
    INSUMO:        { label: 'Insumo',         class: 'bg-green-100 text-green-700' },
}

const emptyForm = () => ({
    name: '',
    type: 'PRODUTO_FINAL',
    description: '',
    variants: [{ sku: '', unit: 'UN', minimum_stock: 0, color: '', size: '' }],
})

export function productList() {
    return {
        products:    [],
        loading:     false,
        showForm:    false,
        submitting:  false,
        toast:       null,
        form:        emptyForm(),
        TYPES,

        async init() {
            await this.load()
        },

        async load() {
            this.loading = true
            try {
                this.products = await productApi.list()
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.loading = false
            }
        },

        addVariantRow() {
            this.form.variants.push({ sku: '', unit: 'UN', minimum_stock: 0, color: '', size: '' })
        },

        removeVariantRow(index) {
            if (this.form.variants.length > 1) this.form.variants.splice(index, 1)
        },

        async submit() {
            this.submitting = true
            try {
                await productApi.create(this.form)
                this.showToast('Produto criado com sucesso!', 'success')
                this.showForm = false
                this.form = emptyForm()
                await this.load()
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.submitting = false
            }
        },

        async deactivate(id, name) {
            if (!confirm(`Desativar "${name}"?`)) return
            try {
                await productApi.deactivate(id)
                this.showToast('Produto desativado.', 'success')
                await this.load()
            } catch (e) {
                this.showToast(e.message, 'error')
            }
        },

        navigate(id) {
            window.location.href = `/products/${id}`
        },

        showToast(message, type = 'success') {
            this.toast = { message, type }
            setTimeout(() => { this.toast = null }, 4000)
        },
    }
}
