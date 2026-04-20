@extends('layouts.app')
@section('title', 'Detalhe do Produto')

@section('content')
<div x-data="productDetail('{{ $id }}')" x-init="init()">

    {{-- Toast --}}
    <template x-if="toast">
        <div class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
             :class="toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
             x-text="toast.message"></div>
    </template>

    <a href="/products" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-800 mb-5">
        ← Voltar
    </a>

    <div x-show="loading" class="text-slate-400 text-sm">Carregando...</div>

    <template x-if="product">
        <div>
            {{-- Product Card --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800" x-text="product.name"></h2>
                        <p class="text-slate-500 text-sm mt-0.5" x-text="product.description || '—'"></p>
                    </div>
                    <div class="flex gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
                              x-text="product.type"></span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                              :class="product.active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                              x-text="product.active ? 'Ativo' : 'Inativo'"></span>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400 font-mono flex items-center gap-2">
                    ID: <span x-text="product.id"></span>
                    <button @click="copyId(product.id)" class="text-slate-400 hover:text-slate-700" title="Copiar">⎘</button>
                </p>
            </div>

            {{-- Variants --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800 text-sm">Variantes</h3>
                    <button @click="showForm = !showForm"
                            class="text-sm bg-slate-900 text-white px-3 py-1.5 rounded-lg hover:bg-slate-700 transition">
                        + Adicionar
                    </button>
                </div>

                {{-- Add Variant Form --}}
                <div x-show="showForm" x-transition class="px-5 py-4 bg-slate-50 border-b border-slate-100">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-3">
                        <div>
                            <label class="label text-xs">SKU *</label>
                            <input x-model="form.sku" type="text" class="input text-sm" placeholder="SKU-001">
                        </div>
                        <div>
                            <label class="label text-xs">Unidade *</label>
                            <input x-model="form.unit" type="text" class="input text-sm" placeholder="UN">
                        </div>
                        <div>
                            <label class="label text-xs">Estoque Mín. *</label>
                            <input x-model.number="form.minimum_stock" type="number" class="input text-sm" min="0">
                        </div>
                        <div>
                            <label class="label text-xs">Cor</label>
                            <input x-model="form.color" type="text" class="input text-sm" placeholder="Azul">
                        </div>
                        <div>
                            <label class="label text-xs">Tamanho</label>
                            <input x-model="form.size" type="text" class="input text-sm" placeholder="M">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="addVariant()" :disabled="submitting"
                                class="bg-slate-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-slate-700 disabled:opacity-50 transition">
                            <span x-text="submitting ? 'Salvando...' : 'Salvar'"></span>
                        </button>
                        <button @click="showForm = false" class="text-sm text-slate-500 hover:text-slate-700">Cancelar</button>
                    </div>
                </div>

                {{-- Variants Table --}}
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="text-left px-5 py-3">SKU</th>
                            <th class="text-left px-5 py-3">Unidade</th>
                            <th class="text-center px-5 py-3">Est. Mín.</th>
                            <th class="text-left px-5 py-3">Cor</th>
                            <th class="text-left px-5 py-3">Tamanho</th>
                            <th class="text-center px-5 py-3">Status</th>
                            <th class="text-right px-5 py-3">ID</th>
                            <th class="text-right px-5 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="v in product.variants" :key="v.id">
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-mono text-xs font-semibold text-slate-700" x-text="v.sku"></td>
                                <td class="px-5 py-3 text-slate-600" x-text="v.unit"></td>
                                <td class="px-5 py-3 text-center text-slate-600" x-text="v.minimum_stock"></td>
                                <td class="px-5 py-3 text-slate-500" x-text="v.color || '—'"></td>
                                <td class="px-5 py-3 text-slate-500" x-text="v.size || '—'"></td>
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                          :class="v.active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                                          x-text="v.active ? 'Ativa' : 'Inativa'"></span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button @click="copyId(v.id)"
                                            class="font-mono text-xs text-slate-400 hover:text-slate-700 underline"
                                            x-text="v.id.slice(0, 8) + '…'" title="Copiar ID completo"></button>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button x-show="v.active" @click="removeVariant(v.id, v.sku)"
                                            class="text-red-500 hover:text-red-700 underline text-xs">Remover</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="!product.variants?.length"
                     class="p-8 text-center text-slate-400 text-sm">Nenhuma variante.</div>
            </div>
        </div>
    </template>
</div>
@endsection
