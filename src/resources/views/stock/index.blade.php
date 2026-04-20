@extends('layouts.app')
@section('title', 'Estoque')

@section('content')
<div x-data="stockDashboard()">

    {{-- Toast --}}
    <template x-if="toast">
        <div class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
             :class="toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
             x-text="toast.message"></div>
    </template>

    {{-- Operations --}}
    <div class="bg-white rounded-xl border border-slate-200 mb-6">
        {{-- Tabs --}}
        <div class="flex border-b border-slate-200">
            <button @click="tab = 'entry'"
                    :class="tab === 'entry' ? 'border-b-2 border-slate-900 text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-3 text-sm transition">
                📥 Entrada
            </button>
            <button @click="tab = 'exit'"
                    :class="tab === 'exit' ? 'border-b-2 border-slate-900 text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-3 text-sm transition">
                📤 Saída
            </button>
            <button @click="tab = 'cancel'"
                    :class="tab === 'cancel' ? 'border-b-2 border-slate-900 text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-3 text-sm transition">
                ↩ Estorno
            </button>
        </div>

        <div class="p-6">
            {{-- Entry Form --}}
            <div x-show="tab === 'entry'">
                <p class="text-sm text-slate-500 mb-4">Registra entrada de estoque para uma variante.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="md:col-span-1">
                        <label class="label">ID da Variante *</label>
                        <input x-model="entryForm.variant_id" type="text" class="input font-mono text-sm"
                               placeholder="uuid da variante">
                    </div>
                    <div>
                        <label class="label">Quantidade *</label>
                        <input x-model.number="entryForm.quantity" type="number" class="input" min="1">
                    </div>
                    <div>
                        <label class="label">Motivo</label>
                        <input x-model="entryForm.reason" type="text" class="input" placeholder="Compra, devolução…">
                    </div>
                </div>
                <button @click="recordEntry()" :disabled="submitting"
                        class="bg-green-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                    <span x-text="submitting ? 'Registrando...' : 'Registrar Entrada'"></span>
                </button>
            </div>

            {{-- Exit Form --}}
            <div x-show="tab === 'exit'">
                <p class="text-sm text-slate-500 mb-4">Registra saída. O saldo deve ser suficiente.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="md:col-span-1">
                        <label class="label">ID da Variante *</label>
                        <input x-model="exitForm.variant_id" type="text" class="input font-mono text-sm"
                               placeholder="uuid da variante">
                    </div>
                    <div>
                        <label class="label">Quantidade *</label>
                        <input x-model.number="exitForm.quantity" type="number" class="input" min="1">
                    </div>
                    <div>
                        <label class="label">Motivo</label>
                        <input x-model="exitForm.reason" type="text" class="input" placeholder="Venda, consumo…">
                    </div>
                </div>
                <button @click="recordExit()" :disabled="submitting"
                        class="bg-red-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-red-700 disabled:opacity-50 transition">
                    <span x-text="submitting ? 'Registrando...' : 'Registrar Saída'"></span>
                </button>
            </div>

            {{-- Cancel Form --}}
            <div x-show="tab === 'cancel'">
                <p class="text-sm text-slate-500 mb-4">Cria um estorno compensatório. A movimentação original não é alterada.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">ID da Movimentação *</label>
                        <input x-model="cancelForm.movement_id" type="text" class="input font-mono text-sm"
                               placeholder="uuid da movimentação">
                    </div>
                    <div>
                        <label class="label">Motivo</label>
                        <input x-model="cancelForm.reason" type="text" class="input" placeholder="Erro de lançamento…">
                    </div>
                </div>
                <button @click="cancelMovement()" :disabled="submitting"
                        class="bg-amber-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-amber-700 disabled:opacity-50 transition">
                    <span x-text="submitting ? 'Estornando...' : 'Registrar Estorno'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Lookup --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="font-semibold text-slate-800 text-sm mb-4">🔍 Consultar Variante</h2>
        <div class="flex gap-3 mb-6">
            <input x-model="lookupVariantId" type="text" class="input flex-1 font-mono text-sm"
                   placeholder="ID da variante (uuid)"
                   @keydown.enter="lookup()">
            <button @click="lookup()" :disabled="loadingLookup"
                    class="bg-slate-900 text-white text-sm px-5 py-2 rounded-lg hover:bg-slate-700 disabled:opacity-50 transition">
                <span x-text="loadingLookup ? 'Buscando...' : 'Consultar'"></span>
            </button>
        </div>

        {{-- Balance --}}
        <template x-if="balance">
            <div class="flex items-center gap-3 mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                <div class="text-3xl font-bold text-slate-800" x-text="balance.quantity"></div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-medium tracking-wide">Saldo atual</p>
                    <p class="text-xs text-slate-400 font-mono" x-text="balance.variant_id"></p>
                </div>
            </div>
        </template>

        {{-- Movements --}}
        <template x-if="movements.length">
            <div>
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Histórico de Movimentações</h3>
                <div class="overflow-hidden rounded-lg border border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                            <tr>
                                <th class="text-left px-4 py-2">Tipo</th>
                                <th class="text-center px-4 py-2">Qtd</th>
                                <th class="text-left px-4 py-2">Motivo</th>
                                <th class="text-left px-4 py-2">Ref.</th>
                                <th class="text-right px-4 py-2">ID</th>
                                <th class="text-right px-4 py-2">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="m in movements" :key="m.id">
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="MOVEMENT_TYPES[m.type]?.class"
                                              x-text="MOVEMENT_TYPES[m.type]?.label ?? m.type"></span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-semibold" x-text="m.quantity"></td>
                                    <td class="px-4 py-2.5 text-slate-500 text-xs" x-text="m.reason || '—'"></td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-slate-400"
                                        x-text="m.referenced_movement_id ? m.referenced_movement_id.slice(0,8) + '…' : '—'"></td>
                                    <td class="px-4 py-2.5 text-right font-mono text-xs text-slate-400"
                                        x-text="m.id.slice(0,8) + '…'"></td>
                                    <td class="px-4 py-2.5 text-right text-xs text-slate-500" x-text="m.created_at"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div x-show="!balance && !loadingLookup" class="text-slate-400 text-sm text-center py-4">
            Informe o ID de uma variante para consultar o saldo e histórico.
        </div>
    </div>
</div>
@endsection
