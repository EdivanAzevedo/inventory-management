@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div x-data="authLogin" class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">

        {{-- Logo / título --}}
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-slate-900 text-xl mb-4">
                📦
            </div>
            <h1 class="text-xl font-bold text-slate-800">Gestão de Estoque</h1>
            <p class="text-sm text-slate-500 mt-1">Faça login para continuar</p>
        </div>

        {{-- Formulário --}}
        <form @submit.prevent="submit" class="space-y-4" novalidate>

            <div>
                <label class="label" for="email">E-mail</label>
                <input
                    id="email"
                    type="email"
                    x-model="form.email"
                    class="input"
                    placeholder="seu@email.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div>
                <label class="label" for="password">Senha</label>
                <input
                    id="password"
                    type="password"
                    x-model="form.password"
                    class="input"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>

            {{-- Mensagem de erro --}}
            <template x-if="error">
                <p class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"
                   x-text="error"
                   role="alert">
                </p>
            </template>

            <button
                type="submit"
                class="w-full bg-slate-900 text-white rounded-lg px-4 py-2.5 text-sm font-medium
                       hover:bg-slate-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed mt-2"
                :disabled="submitting"
            >
                <span x-text="submitting ? 'Entrando…' : 'Entrar'"></span>
            </button>

        </form>

    </div>
</div>
@endsection
