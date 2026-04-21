@extends('layouts.app')
@section('title', 'Produtos')

@section('content')
<div x-data="productList()" x-init="init()">

    {{-- Toast --}}
    <template x-if="toast">
        <div class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium transition"
             :class="toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
             x-text="toast.message"></div>
    </template>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-slate-500 text-sm" x-text="products.length + ' produto(s) encontrado(s)'"></p>
        <button x-show="activeTab === 'active'" @click="showForm = !showForm"
                class="bg-slate-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-slate-700 transition">
            + Novo Produto
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-5 border-b border-slate-200">
        <button @click="switchTab('active')"
                :class="activeTab === 'active'
                    ? 'border-b-2 border-slate-900 text-slate-900 font-medium'
                    : 'text-slate-400 hover:text-slate-600'"
                class="px-4 py-2 text-sm transition">
            Ativos
        </button>
        <button @click="switchTab('inactive')"
                :class="activeTab === 'inactive'
                    ? 'border-b-2 border-slate-900 text-slate-900 font-medium'
                    : 'text-slate-400 hover:text-slate-600'"
                class="px-4 py-2 text-sm transition">
            Inativos
        </button>
    </div>

    {{-- Create Form --}}
    <div x-show="showForm" x-transition class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h2 class="font-semibold text-slate-800 mb-4">Novo Produto</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="label">Nome *</label>
                <input x-model="form.name" type="text" class="input" placeholder="Ex: Camiseta Básica">
            </div>
            <div>
                <label class="label">Tipo *</label>
                <select x-model="form.type" class="input">
                    <option value="PRODUTO_FINAL">Produto Final</option>
                    <option value="MATERIA_PRIMA">Matéria-Prima</option>
                    <option value="INSUMO">Insumo</option>
                </select>
            </div>
            <div>
                <label class="label">Descrição</label>
                <input x-model="form.description" type="text" class="input" placeholder="Opcional">
            </div>
        </div>

        {{-- Variants --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label class="label mb-0">Variantes *</label>
                <button @click="addVariantRow()" class="text-xs text-slate-600 underline">+ Adicionar variante</button>
            </div>
            <template x-for="(v, i) in form.variants" :key="i">
                <div class="grid grid-cols-6 gap-2 mb-2 items-end">
                    <div class="col-span-1">
                        <label class="label text-xs">SKU *</label>
                        <input x-model="v.sku" type="text" class="input text-sm" placeholder="SKU-001">
                    </div>
                    <div>
                        <label class="label text-xs">Unidade *</label>
                        <input x-model="v.unit" type="text" class="input text-sm" placeholder="UN">
                    </div>
                    <div>
                        <label class="label text-xs">Estoque Mín.</label>
                        <input x-model.number="v.minimum_stock" type="number" class="input text-sm" min="0">
                    </div>
                    <div>
                        <label class="label text-xs">Cor</label>
                        <input x-model="v.color" type="text" class="input text-sm" placeholder="Azul">
                    </div>
                    <div>
                        <label class="label text-xs">Tamanho</label>
                        <input x-model="v.size" type="text" class="input text-sm" placeholder="M">
                    </div>
                    <div>
                        <button @click="removeVariantRow(i)"
                                class="w-full py-2 text-red-500 hover:text-red-700 text-sm"
                                :disabled="form.variants.length === 1">✕</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex gap-3">
            <button @click="submit()" :disabled="submitting"
                    class="bg-slate-900 text-white text-sm px-5 py-2 rounded-lg hover:bg-slate-700 disabled:opacity-50 transition">
                <span x-text="submitting ? 'Salvando...' : 'Salvar'"></span>
            </button>
            <button @click="showForm = false; form = $data.form"
                    class="text-sm text-slate-500 hover:text-slate-700">Cancelar</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div x-show="loading" class="p-8 text-center text-slate-400 text-sm">Carregando...</div>
        <table x-show="!loading && products.length" class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="text-left px-5 py-3">Nome</th>
                    <th class="text-left px-5 py-3">Tipo</th>
                    <th class="text-center px-5 py-3">Variantes</th>
                    <th class="text-center px-5 py-3">Status</th>
                    <th class="text-right px-5 py-3">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="p in products" :key="p.id">
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800" x-text="p.name"></td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                  :class="TYPES[p.type]?.class"
                                  x-text="TYPES[p.type]?.label ?? p.type"></span>
                        </td>
                        <td class="px-5 py-3 text-center text-slate-500" x-text="p.variants?.length ?? 0"></td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                  :class="p.active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                                  x-text="p.active ? 'Ativo' : 'Inativo'"></span>
                        </td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <button @click="navigate(p.id)"
                                    class="text-slate-600 hover:text-slate-900 underline text-xs">Detalhes</button>
                            <button x-show="p.active" @click="deactivate(p.id, p.name)"
                                    class="text-red-500 hover:text-red-700 underline text-xs">Desativar</button>
                            <button x-show="!p.active" @click="reactivate(p.id, p.name)"
                                    class="text-emerald-600 hover:text-emerald-800 underline text-xs">Reativar</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div x-show="!loading && !products.length" class="p-10 text-center text-slate-400 text-sm">
            <template x-if="activeTab === 'active'">
                <span>Nenhum produto ativo. Clique em <strong>+ Novo Produto</strong> para começar.</span>
            </template>
            <template x-if="activeTab === 'inactive'">
                <span>Nenhum produto inativo.</span>
            </template>
        </div>
    </div>
</div>
@endsection
