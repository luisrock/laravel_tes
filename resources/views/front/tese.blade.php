@extends('front.base')

@section('page-title', $label)

@section('content')

    <!-- Schema.org Structured Data para SEO -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "LegalDocument",
      "name": "{{ $label }}",
      "description": "{{ $description }}",
      "author": {
        "@type": "Organization",
        "name": "{{ $tribunal_nome_completo }}"
      },
      "publisher": {
        "@type": "Organization",
        "name": "{{ $tribunal_nome_completo }}"
      },
      "inLanguage": "pt-BR",
      "datePublished": "{{ $tese->aprovadaEm ?? $tese->atualizadaEm ?? date('Y-m-d') }}",
      "url": "{{ url()->current() }}",
      "mainEntity": {
        "@type": "Thing",
        "name": "Tema {{ $tese->numero }}",
        "description": "{{ $tese->questao ?? $tese->tema_texto }}"
      }
    }
    </script>

    <!-- Page Content -->

    <div class="home-pilot-shell tw-pt-4">
        <section class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-2">
            <h1 class="home-pilot-title tw-m-0">
                {{ $label }}
            </h1>
            <p class="home-pilot-subtitle tw-m-0">
                Faça uma <a href="{{ route('searchpage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">pesquisa</a> ou veja as
                <a href="{{ route('alltemaspage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">pesquisas prontas</a>.
                @if ($admin)
                    <br><a href="{{ route('admin') }}" class="tw-text-brand-700 hover:tw-text-brand-800">Admin</a>
                @endif
            </p>
        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="home-pilot-shell tw-pt-2 tw-pb-0">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <div class="home-pilot-shell tw-pt-2" id="content-results">

        <!-- Results -->

        <div class="home-pilot-card tw-p-5 md:tw-p-6">
            <div>


                <div role="tabpanel">

                    <div class="home-results-count trib-texto-quantidade">
                        {{ $tribunal_nome_completo }} - {{ $tribunal }}
                    </div>

                    <table class="home-results-table table-results table-sumula">

                        <tbody>

                            <tr>
                                <td>
                                    <h4 class="tw-text-lg tw-font-semibold tw-mt-0 tw-mb-2 tw-text-brand-700">
                                        @if (!empty($tese->link))
                                            <a href="{{ $tese->link }}" target="_blank">
                                                {{ $tese->titulo }}
                                            </a>
                                        @else
                                            {{ $tese->titulo }}
                                        @endif
                                    </h4>
                                    @if (!empty($tese->questao))
                                        <p>
                                            {{ $tese->questao }}
                                        </p>
                                    @endif
                                    @if (!empty($tese->texto))
                                        <p class="tw-font-semibold" id="tese-texto">
                                            {{ $tese->texto }}
                                        </p>
                                    @endif
                                    @if (!empty($tese->text_muted))
                                        <span class="tw-text-slate-500 tw-text-sm tw-flex tw-justify-end">
                                            {{ $tese->text_muted }}
                                        </span>
                                    @endif
                                    
                                    <!-- Botões de Ação -->
                                    <div class="tw-mt-3 tw-mb-3 tw-flex tw-flex-wrap tw-gap-2">
                                        @if (!empty($tese->to_be_copied))
                                        <button class="home-pilot-btn tw-py-2 tw-px-3 tw-text-sm" onclick="copiarTese(event)" title="Copiar texto completo da tese">
                                            <i class="fa fa-copy"></i> Copiar Tese
                                        </button>
                                        @endif
                                        
                                        <button class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-lg tw-border tw-border-brand-300 tw-text-brand-700 hover:tw-bg-brand-50 tw-py-2 tw-px-3 tw-text-sm tw-font-medium" onclick="compartilharTese()" title="Compartilhar esta tese">
                                            <i class="fa fa-share-alt"></i> Compartilhar
                                        </button>
                                        
                                        @if (!empty($tese->link))
                                        <a href="{{ $tese->link }}" target="_blank" class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-lg tw-border tw-border-slate-300 tw-text-slate-700 hover:tw-bg-slate-50 tw-py-2 tw-px-3 tw-text-sm tw-font-medium" title="Ver no site oficial do {{ $tribunal }}">
                                            <i class="fa fa-external-link"></i> Ver Original
                                        </a>
                                        @endif
                                    </div>
                                    
                                    @if (!empty($tese->to_be_copied))
                                        <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text" style="display: none;">
                                            <span>
                                                <i class="fa fa-copy"></i>
                                            </span>
                                        </button>
                                        <span class="tes-clear tes-text-to-be-copied" style="display: none"
                                            data-spec="trim">{{ $tese->to_be_copied }}
                                        </span>
                                    @endif

                                </td>
                            </tr>

                        </tbody>

                    </table>

                    @if (!empty($tese->ementa_texto))
                        {{-- STF --}}
                        <h4 class="tw-text-base tw-font-semibold tw-mt-4 tw-mb-2">
                            Ementa
                        </h4>
                        <p>
                            {{ $tese->ementa_texto }}
                            <span class="tw-text-slate-500 tw-text-sm tw-flex tw-justify-end">
                                {{ $tese->relator }}, {{ $tese->acordao }}.
                            </span>
                        </p>
                    @endif

                    @if (!empty($tese->indexacao))
                        {{-- STF --}}
                        <h4 class="tw-text-base tw-font-semibold tw-mt-4 tw-mb-2">
                            Indexação
                        </h4>
                        <p>
                            {{ $tese->indexacao }}
                        </p>
                    @endif

                    @if (!empty($tese->ramos))
                        {{-- STJ --}}
                        <h4 class="tw-text-base tw-font-semibold tw-mt-4 tw-mb-2">
                            Assuntos
                        </h4>
                        <p>
                            {{ $tese->ramos }}
                        </p>
                    @endif

                    @if (!empty($tese->link))
                        <p>
                            Consulte a fonte
                            <a href="{{ $tese->link }}" target="_blank" class="tw-text-brand-700 hover:tw-text-brand-800">
                                aqui</a>
                        </p>
                    @endif

                </div>

            </div>

            <!-- END Results -->
            
            <!-- Temas Relacionados -->
            @if(isset($related_themes) && $related_themes->count() > 0)
            <div class="tw-mt-6 tw-mb-2">
                <h4 class="tw-text-base tw-font-semibold tw-mb-3 tw-text-slate-800">
                    <i class="fa fa-link text-primary"></i> Temas Relacionados
                </h4>
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-3">
                    @foreach($related_themes as $theme)
                    <div>
                        <a href="/tema/{{ $theme->slug }}" class="tw-block tw-border tw-border-slate-200 tw-rounded-lg hover:tw-border-brand-300 hover:tw-bg-brand-50 tw-transition tw-p-4" style="text-decoration: none;">
                            <div>
                                <div class="tw-text-sm tw-font-semibold tw-text-brand-800">
                                    {{ $theme->label ?? $theme->keyword }}
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="tw-mt-5 tw-pt-5 tw-border-t tw-border-slate-200 tw-text-center">
                <a href="{{ route($alltesesroute) }}" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-brand-700 hover:tw-text-brand-800 tw-font-medium">
                    <i class="fa fa-arrow-left"></i> Teses Vinculantes do {{ $tribunal_nome_completo }}
                </a>
            </div>

        </div>
    </div>
        @endsection
        
        @section('scripts')
        <script>
        // Função para copiar texto da tese
        function copiarTese(event) {
            const texto = @json($tese->to_be_copied ?? '');
            
            if (!texto) {
                alert('Texto não disponível para cópia');
                return;
            }
            
            const btn = event.target.closest('button');
            
            // Usar Clipboard API moderna
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(texto).then(function() {
                    // Feedback visual de sucesso
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fa fa-check"></i> Copiada!';
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                    
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-primary');
                    }, 3000);
                }).catch(function(err) {
                    console.error('Erro ao copiar:', err);
                    copiarTeseOldWay(texto, btn);
                });
            } else {
                // Fallback para navegadores antigos
                copiarTeseOldWay(texto, btn);
            }
        }
        
        // Método antigo para navegadores sem Clipboard API
        function copiarTeseOldWay(texto, btn) {
            const textArea = document.createElement('textarea');
            textArea.value = texto;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                // Feedback visual de sucesso
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check"></i> Copiada!';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-primary');
                }, 3000);
            } catch (err) {
                alert('Erro ao copiar. Por favor, selecione e copie manualmente.');
            }
            
            document.body.removeChild(textArea);
        }
        
        // Função para compartilhar
        function compartilharTese() {
            const titulo = @json($label ?? '');
            const url = window.location.href;
            const texto = @json($tese->questao ?? $tese->tema_texto ?? '');
            
            // Usar Web Share API se disponível (mobile principalmente)
            if (navigator.share) {
                navigator.share({
                    title: titulo,
                    text: texto,
                    url: url
                }).then(() => {
                    console.log('Compartilhado com sucesso');
                }).catch((error) => {
                    console.log('Erro ao compartilhar:', error);
                    abrirOpcoesCompartilhamento(titulo, url, texto);
                });
            } else {
                // Fallback: abrir opções tradicionais
                abrirOpcoesCompartilhamento(titulo, url, texto);
            }
        }
        
        // Opções tradicionais de compartilhamento
        function abrirOpcoesCompartilhamento(titulo, url, texto) {
            const encodedUrl = encodeURIComponent(url);
            const encodedTitulo = encodeURIComponent(titulo);
            const encodedTexto = encodeURIComponent(texto);
            
            const opcoes = `
                <div style="text-align: left;">
                    <p><strong>Compartilhar via:</strong></p>
                    <a href="https://wa.me/?text=${encodedTitulo}%20${encodedUrl}" target="_blank" class="btn btn-success btn-sm mb-2" style="width: 100%;">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a><br>
                    <a href="https://t.me/share/url?url=${encodedUrl}&text=${encodedTitulo}" target="_blank" class="btn btn-info btn-sm mb-2" style="width: 100%;">
                        <i class="fab fa-telegram"></i> Telegram
                    </a><br>
                    <a href="mailto:?subject=${encodedTitulo}&body=${encodedTexto}%0A%0A${encodedUrl}" class="btn btn-secondary btn-sm mb-2" style="width: 100%;">
                        <i class="fa fa-envelope"></i> Email
                    </a><br>
                    <button onclick="copiarLink('${url}')" class="btn btn-outline-primary btn-sm" style="width: 100%;">
                        <i class="fa fa-link"></i> Copiar Link
                    </button>
                </div>
            `;
            
            // Usar SweetAlert ou alert simples
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Compartilhar',
                    html: opcoes,
                    showConfirmButton: false,
                    showCloseButton: true
                });
            } else {
                // Criar modal simples
                const modal = document.createElement('div');
                modal.innerHTML = opcoes;
                modal.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999;';
                
                const overlay = document.createElement('div');
                overlay.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9998;';
                overlay.onclick = function() {
                    document.body.removeChild(modal);
                    document.body.removeChild(overlay);
                };
                
                document.body.appendChild(overlay);
                document.body.appendChild(modal);
            }
        }
        
        // Copiar link da página
        function copiarLink(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Link copiado para a área de transferência!');
            }).catch(function() {
                prompt('Copie o link abaixo:', url);
            });
        }
        </script>
        @endsection
