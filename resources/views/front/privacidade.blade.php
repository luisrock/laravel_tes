@extends('layouts.app')

@section('content')
<main id="main-container">
    <div class="tw-bg-gray-50 tw-py-12 tw-min-h-screen">
        <div class="tw-max-w-4xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-overflow-hidden">
                <div class="tw-p-6 sm:tw-p-10">
                    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-8">Política de Privacidade</h1>

                    <div class="tw-prose tw-prose-blue tw-max-w-none">
                        
                        <h2>1. Informações que Coletamos</h2>
                        <p>O Teses e Súmulas coleta informações estritamente necessárias para o funcionamento da plataforma e para proporcionar uma melhor experiência jurídica. Isso inclui dados de cadastro (nome e e-mail), histórico de buscas anonimizadas (para alimentar trending topics) e informações de pagamento processadas de forma segura via Stripe.</p>

                        <h2>2. Como Usamos suas Informações</h2>
                        <p>Usamos as informações coletadas para manter sua conta, processar assinaturas e melhorar ou personalizar nossos serviços. Não vendemos suas informações pessoais para terceiros.</p>
                        
                        <h2>3. Segurança dos Dados e Pagamentos</h2>
                        <p>Implementamos medidas de segurança rígidas para proteger suas informações. Todos os processamentos de cartão de crédito e faturas são conduzidos de maneira direta e criptografada pela Stripe Inc. Nós não armazenamos os dados do seu cartão de crédito em nossos servidores.</p>
                        
                        <h2>4. Cookies e Rastreamento Analytics</h2>
                        <p>Utilizamos cookies para manter o controle de sua sessão de autenticação, personalização da interface e para coletar dados analíticos de uso (ex.: Matomo) com o fim de aprimorar a usabilidade das funcionalidades e da listagem de teses.</p>

                        <p class="tw-text-sm tw-text-gray-500 tw-mt-10">Última atualização: Fevereiro de 2026</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
