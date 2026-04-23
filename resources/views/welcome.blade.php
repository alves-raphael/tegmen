<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Tegmen') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .feature-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        @media (min-width: 768px) {
            .feature-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .btn-row {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
        }
        @media (min-width: 480px) {
            .btn-row {
                flex-direction: row;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-white text-zinc-900 antialiased">

    {{-- Header --}}
    <header class="border-b border-zinc-100 bg-white">
        <div class="mx-auto max-w-5xl px-6 py-4 flex items-center justify-between">
            <span class="text-lg font-bold tracking-tight text-zinc-900">
                {{ config('app.name', 'Tegmen') }}
            </span>
            <nav class="flex items-center gap-3">
                <a href="{{ route('login') }}"
                   class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors px-3 py-1.5">
                    Entrar
                </a>
                <a href="{{ route('register') }}"
                   class="text-sm font-medium bg-zinc-900 text-white px-4 py-2 rounded-lg hover:bg-zinc-700 transition-colors">
                    Criar conta
                </a>
            </nav>
        </div>
    </header>

    <main>

        {{-- Hero --}}
        <section class="mx-auto max-w-5xl px-6 py-24 text-center">
            <span class="inline-block bg-zinc-100 text-zinc-600 text-xs font-semibold uppercase tracking-widest px-4 py-1.5 rounded-full mb-8">
                Para corretores de seguros independentes
            </span>
            <h1 class="text-4xl font-bold tracking-tight text-zinc-900 leading-tight mb-5" style="font-size: clamp(2rem, 5vw, 3.5rem);">
                Gerencie suas apólices<br>com eficiência
            </h1>
            <p class="text-base text-zinc-500 max-w-xl mx-auto mb-10" style="font-size: 1.0625rem; line-height: 1.75;">
                Plataforma completa para corretores de seguros automotivos.
                Controle clientes, veículos e apólices em um só lugar.
            </p>
            <div class="btn-row">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center bg-zinc-900 text-white font-semibold px-7 py-3 rounded-lg hover:bg-zinc-700 transition-colors w-full"
                   style="max-width: 240px;">
                    Começar gratuitamente
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center border border-zinc-200 text-zinc-700 font-medium px-7 py-3 rounded-lg hover:bg-zinc-50 transition-colors w-full"
                   style="max-width: 240px;">
                    Já tenho conta
                </a>
            </div>
        </section>

        {{-- Features --}}
        <section style="background-color: #f9fafb; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0;">
            <div class="mx-auto max-w-5xl px-6 py-16">
                <div class="feature-grid">

                    <div>
                        <div class="inline-flex items-center justify-center w-11 h-11 bg-zinc-200 rounded-xl mb-5">
                            <svg class="w-5 h-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-zinc-900 mb-2">Gestão de Clientes</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed">
                            Cadastre e gerencie seus clientes com endereços e documentos organizados.
                        </p>
                    </div>

                    <div>
                        <div class="inline-flex items-center justify-center w-11 h-11 bg-zinc-200 rounded-xl mb-5">
                            <svg class="w-5 h-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-zinc-900 mb-2">Controle de Apólices</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed">
                            Acompanhe vencimentos, renovações e comissões de todas as suas apólices.
                        </p>
                    </div>

                    <div>
                        <div class="inline-flex items-center justify-center w-11 h-11 bg-zinc-200 rounded-xl mb-5">
                            <svg class="w-5 h-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 0m8 0H9m4 0h2m4 0h.01M7 16H4m0 0l2-10h7l1 3.5"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-zinc-900 mb-2">Frota de Veículos</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed">
                            Vincule veículos aos clientes e mantenha o histórico de seguros atualizado.
                        </p>
                    </div>

                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="mx-auto max-w-5xl px-6 py-24 text-center">
            <h2 class="font-bold text-zinc-900 mb-4" style="font-size: clamp(1.5rem, 3vw, 2rem);">
                Pronto para organizar sua corretora?
            </h2>
            <p class="text-zinc-500 mb-8 text-sm">
                Crie sua conta e comece a gerenciar suas apólices hoje mesmo.
            </p>
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center bg-zinc-900 text-white font-semibold px-8 py-3 rounded-lg hover:bg-zinc-700 transition-colors">
                Criar conta grátis
            </a>
        </section>

    </main>

    <footer style="border-top: 1px solid #f0f0f0;">
        <div class="mx-auto max-w-5xl px-6 py-6 text-center text-xs text-zinc-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'Tegmen') }}. Todos os direitos reservados.
        </div>
    </footer>

</body>
</html>
