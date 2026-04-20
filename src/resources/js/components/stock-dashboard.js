import { stockApi } from '../api/stock-api.js'

export function stockDashboard() {
    return {
        tab:        'entry',
        toast:      null,
        submitting: false,

        entryForm:  { variant_id: '', quantity: 1, reason: '' },
        exitForm:   { variant_id: '', quantity: 1, reason: '' },
        cancelForm: { movement_id: '', reason: '' },

        lookupVariantId: '',
        balance:         null,
        movements:       [],
        loadingLookup:   false,

        MOVEMENT_TYPES: {
            ENTRY:    { label: 'Entrada',  class: 'bg-green-100 text-green-700' },
            EXIT:     { label: 'Saída',    class: 'bg-red-100 text-red-700' },
            REVERSAL: { label: 'Estorno',  class: 'bg-amber-100 text-amber-700' },
        },

        async recordEntry() {
            this.submitting = true
            try {
                await stockApi.recordEntry(this.entryForm)
                this.showToast('Entrada registrada!', 'success')
                this.entryForm = { variant_id: '', quantity: 1, reason: '' }
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.submitting = false
            }
        },

        async recordExit() {
            this.submitting = true
            try {
                await stockApi.recordExit(this.exitForm)
                this.showToast('Saída registrada!', 'success')
                this.exitForm = { variant_id: '', quantity: 1, reason: '' }
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.submitting = false
            }
        },

        async cancelMovement() {
            this.submitting = true
            try {
                await stockApi.cancelMovement(this.cancelForm.movement_id, this.cancelForm.reason)
                this.showToast('Estorno registrado!', 'success')
                this.cancelForm = { movement_id: '', reason: '' }
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.submitting = false
            }
        },

        async lookup() {
            if (!this.lookupVariantId.trim()) return
            this.loadingLookup = true
            this.balance = null
            this.movements = []
            try {
                const [bal, movs] = await Promise.all([
                    stockApi.getBalance(this.lookupVariantId),
                    stockApi.getMovements(this.lookupVariantId),
                ])
                this.balance   = bal
                this.movements = movs
            } catch (e) {
                this.showToast(e.message, 'error')
            } finally {
                this.loadingLookup = false
            }
        },

        showToast(message, type = 'success') {
            this.toast = { message, type }
            setTimeout(() => { this.toast = null }, 4000)
        },
    }
}
