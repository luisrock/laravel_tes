@extends('front.base')

@section('page-title', $label)

@section('content')

    <!-- Schema.org Structured Data para SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
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

    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">
                    <a href="{{ url('/') }}">
                        Teses & Súmulas
                    </a>
                    <span class="text-muted"> | </span> {{ $label }}
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="badge badge-primary">Extensão para o Chrome</a>
                </span>
            </div>
            <p>
                Faça uma <a href="{{ route('searchpage') }}">pesquisa</a> ou veja as <a
                    href="{{ route('alltemaspage') }}">pesquisas prontas</a>.
                @if ($admin)
                    <br><a href="{{ route('admin') }}">Admin</a>
                @endif
            </p>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="content content-full pt-2 pb-0">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <div class="content" id="content-results">

        <!-- Results -->

        <div class="block-content tab-content overflow-hidden">
            <div class="block-content tab-content overflow-hidden">


                <div class="tab-pane fade fade-up active show" role="tabpanel">

                    <div
                        class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">
                        {{ $tribunal_nome_completo }} - {{ $tribunal }}
                    </div>

                    <table class="table table-striped table-vcenter table-results table-sumula"
                        style="border: 2px solid #5c80d1;">

                        <tbody>

                            <tr>
                                <td>
                                    <h4 class="h5 mt-3 mb-2" style="color:#6d8cd5;">
                                        @if (!empty($tese->link))
                                            <a href="{{ $tese->link }}" target="_blank">
                                                {{ $tese->titulo }}
                                            </a>
                                        @else
                                            {{ $tese->titulo }}
                                        @endif
                                    </h4>
                                    @if (!empty($tese->questao))
                                        <p class="d-sm-block" style="">
                                            {{ $tese->questao }}
                                        </p>
                                    @endif
                                    @if (!empty($tese->texto))
                                        <p class="d-sm-block" style="font-weight: bold;" id="tese-texto">
                                            {{ $tese->texto }}
                                        </p>
                                    @endif
                                    @if (!empty($tese->text_muted))
                                        <span class="text-muted"
                                            style="display: flex;justify-content: flex-end;font-size: 0.8em;">
                                            {{ $tese->text_muted }}
                                        </span>
                                    @endif
                                    
                                    <!-- Botões de Ação -->
                                    <div class="mt-3 mb-3">
                                        @if (!empty($tese->to_be_copied))
                                        <button class="btn btn-primary btn-sm mr-2" onclick="copiarTese(event)" title="Copiar texto completo da tese">
                                            <i class="fa fa-copy"></i> Copiar Tese
                                        </button>
                                        @endif
                                        
                                        <button class="btn btn-outline-primary btn-sm mr-2" onclick="compartilharTese()" title="Compartilhar esta tese">
                                            <i class="fa fa-share-alt"></i> Compartilhar
                                        </button>
                                        
                                        @if (!empty($tese->link))
                                        <a href="{{ $tese->link }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Ver no site oficial do {{ $tribunal }}">
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
                        <h4 class="h5 mt-3 mb-2">
                            Ementa
                        </h4>
                        <p class="d-sm-block" style="">
                            {{ $tese->ementa_texto }}
                            <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
                                {{ $tese->relator }}, {{ $tese->acordao }}.
                            </span>
                        </p>
                    @endif

                    @if (!empty($tese->indexacao))
                        {{-- STF --}}
                        <h4 class="h5 mt-3 mb-2">
                            Indexação
                        </h4>
                        <p class="d-sm-block" style="">
                            {{ $tese->indexacao }}
                        </p>
                    @endif

                    @if (!empty($tese->ramos))
                        {{-- STJ --}}
                        <h4 class="h5 mt-3 mb-2">
                            Assuntos
                        </h4>
                        <p class="d-sm-block" style="">
                            {{ $tese->ramos }}
                        </p>
                    @endif

                    @if (!empty($tese->link))
                        <p class="d-sm-block" style="">
                            Consulte a fonte
                            <a href="{{ $tese->link }}" target="_blank">
                                aqui</a>
                        </p>
                    @endif

                </div>

            </div>

            <!-- END Results -->
            
            <!-- Temas Relacionados -->
            @if(isset($related_themes) && $related_themes->count() > 0)
            <div class="block-content mt-4 mb-4">
                <h4 class="h5 mb-3">
                    <i class="fa fa-link text-primary"></i> Temas Relacionados
                </h4>
                <div class="row">
                    @foreach($related_themes as $theme)
                    <div class="col-md-4 col-sm-6 mb-3">
                        <a href="/tema/{{ $theme->slug }}" class="block block-rounded block-link-shadow text-center h-100" style="text-decoration: none;">
                            <div class="block-content block-content-full">
                                <div class="font-size-sm font-w600 text-primary">
                                    {{ $theme->label ?? $theme->keyword }}
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="block-content block-content-full block-content-sm bg-body-light">
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <a href="{{ route($alltesesroute) }}">
                            <i class="fa fa-arrow-left mr-1"></i> Teses Vinculantes do {{ $tribunal_nome_completo }}
                        </a>
                    </div>
                </div>

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
