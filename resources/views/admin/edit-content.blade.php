<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar: {{ $content->title }} - Teses & Súmulas</title>
    
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" id="css-main" href="{{ url('/assets/css/tescustom.min.css') }}" type="text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
</head>
<body>
    <div id="page-container" class="main-content-boxed">
        <main id="main-container">
            
            <!-- Hero -->
            <div class="bg-body-light">
                <div class="content content-full">
                    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                        <h1 class="flex-sm-fill h3 my-2">
                            <a href="{{ url('/') }}">Teses & Súmulas</a>
                            <span class="text-muted">| Admin</span>
                        </h1>
                    </div>
                    <p>
                        <a href="{{ route('content.show', $content->slug) }}">← Voltar para visualização pública</a> | 
                        <a href="{{ route('admin') }}">Painel Admin</a>
                    </p>
                </div>
            </div>
            <!-- END Hero -->

            <div class="content">
                <div class="block">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">✏️ Editando: {{ $content->title }}</h3>
                        <div class="block-options">
                            <span class="badge badge-info">Slug: {{ $content->slug }}</span>
                        </div>
                    </div>
                    <div class="block-content block-content-full">
                        
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>✅ Sucesso!</strong> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>❌ Erro:</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('content.update', $content->slug) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="title">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                       value="{{ old('title', $content->title) }}" required>
                                <small class="form-text text-muted">Título principal da página (usado no SEO)</small>
                            </div>

                            <div class="form-group">
                                <label for="meta_description">Meta Description (SEO)</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" 
                                          rows="2" maxlength="160">{{ old('meta_description', $content->meta_description) }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="meta-char-count">{{ strlen($content->meta_description ?? '') }}</span>/160 caracteres
                                    - Descrição que aparece no Google
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="content">Conteúdo <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="20">{{ old('content', $content->content) }}</textarea>
                                <small class="form-text text-muted">
                                    Editor visual ativo. Use os botões para formatar ou clique em "Code View" para editar HTML diretamente.
                                </small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="published" name="published" 
                                           value="1" {{ $content->published ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="published">
                                        Publicado (visível para visitantes)
                                    </label>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <a href="{{ route('content.show', $content->slug) }}" class="btn btn-secondary">
                                    <i class="fa fa-times mr-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-save mr-1"></i> Salvar Alterações
                                </button>
                            </div>
                        </form>

                        <hr>
                        
                        <div class="alert alert-info">
                            <strong>💡 Como usar o editor:</strong>
                            <ul class="mb-0">
                                <li><strong>Modo Visual:</strong> Use os botões da barra de ferramentas (negrito, listas, links, etc.)</li>
                                <li><strong>Modo Código:</strong> Clique em "Code View" para editar HTML diretamente</li>
                                <li><strong>Headings:</strong> Use "Heading 2" para seções principais, "Heading 3" para subseções</li>
                                <li><strong>Links:</strong> Selecione texto → clique no ícone de link → insira URL (ex: /sumulas/stf)</li>
                                <li><strong>Fullscreen:</strong> Clique no ícone de tela cheia para edição em tela inteira</li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Template Scripts (já incluem jQuery) -->
    <script src="{{ url('assets/js/tescustom.core.min.js') }}"></script>
    <script src="{{ url('assets/js/tescustom.app.min.js') }}"></script>
    
    <!-- Summernote JS (carrega DEPOIS do template) -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

    <!-- Summernote Initialization -->
    <script>
        // Usar setTimeout para garantir que tudo carregou
        setTimeout(function() {
            console.log('Inicializando Summernote...');
            console.log('jQuery disponível:', typeof jQuery);
            console.log('Summernote disponível:', typeof jQuery.fn.summernote);
            
            // Contador de caracteres para meta description
            jQuery('#meta_description').on('input', function() {
                jQuery('#meta-char-count').text(this.value.length);
            });

            // Summernote Editor
            jQuery('#content').summernote({
                height: 600,
                placeholder: 'Digite o conteúdo aqui...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                styleTags: ['p', 'h2', 'h3', 'h4'],
                callbacks: {
                    onInit: function() {
                        console.log('Summernote inicializado com sucesso!');
                    },
                    onChange: function(contents, $editable) {
                        jQuery('#content').val(contents);
                    }
                }
            });

            console.log('Editor criado!');

            // Aviso antes de sair da página
            var formChanged = false;
            
            jQuery(document).on('input', '.note-editable', function() {
                formChanged = true;
            });
            
            jQuery('input, select').on('change', function() {
                formChanged = true;
            });
            
            jQuery(window).on('beforeunload', function(e) {
                if (formChanged) {
                    return 'Você tem alterações não salvas. Deseja realmente sair?';
                }
            });
            
            // Ao submeter, sincronizar editor com textarea
            jQuery('form').on('submit', function() {
                var content = jQuery('#content').summernote('code');
                jQuery('#content').val(content);
                console.log('Salvando conteúdo...');
                formChanged = false;
                return true;
            });
            
        }, 500); // Aguardar 500ms para garantir que tudo carregou
    </script>
</body>
</html>

