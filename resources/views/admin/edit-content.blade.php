@extends('layouts.admin')

@section('title', 'Editar: ' . $content->title)

@section('content')
<div class="tw-max-w-7xl tw-mx-auto">
    
    <!-- Hero -->
    <div class="tw-mb-8 tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-center">
        <div>
            <h1 class="tw-text-3xl tw-font-bold tw-text-slate-900">
                Editar Conteúdo
            </h1>
            <p class="tw-text-slate-600 tw-mt-2">
                <a href="{{ route('content.show', $content->slug) }}" class="tw-text-indigo-600 hover:tw-underline">← Voltar para visualização pública</a>
            </p>
        </div>
        <div class="tw-mt-4 sm:tw-mt-0">
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-slate-100 tw-text-slate-800">
                Slug: {{ $content->slug }}
            </span>
        </div>
    </div>
    <!-- END Hero -->

    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-200 tw-flex tw-justify-between tw-items-center tw-bg-slate-50">
            <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800">✏️ Editando: {{ $content->title }}</h3>
        </div>
        
        <div class="tw-p-6">
            
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="tw-bg-emerald-100 tw-border tw-border-emerald-400 tw-text-emerald-700 tw-px-4 tw-py-3 tw-rounded tw-relative tw-mb-6" role="alert">
                <span class="tw-block sm:tw-inline">
                    <strong>✅ Sucesso!</strong> {{ session('success') }}
                </span>
                <span class="tw-absolute tw-top-0 tw-bottom-0 tw-right-0 tw-px-4 tw-py-3" @click="show = false">
                    <svg class="tw-fill-current tw-h-6 tw-w-6 tw-text-emerald-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
            @endif

            @if($errors->any())
            <div class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-6">
                <strong>❌ Erro:</strong>
                <ul class="tw-list-disc tw-pl-5 tw-mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('content.update', $content->slug) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="tw-mb-6">
                    <label for="title" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-2">Título <span class="tw-text-red-500">*</span></label>
                    <input type="text" class="tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-indigo-500 focus:tw-ring-indigo-500 sm:tw-text-lg" id="title" name="title" 
                           value="{{ old('title', $content->title) }}" required>
                    <p class="tw-mt-1 tw-text-sm tw-text-slate-500">Título principal da página (usado no SEO)</p>
                </div>

                <div class="tw-mb-6">
                    <label for="meta_description" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-2">Meta Description (SEO)</label>
                    <textarea class="tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-indigo-500 focus:tw-ring-indigo-500" id="meta_description" name="meta_description" 
                              rows="2" maxlength="160">{{ old('meta_description', $content->meta_description) }}</textarea>
                    <p class="tw-mt-1 tw-text-sm tw-text-slate-500">
                        <span id="meta-char-count">{{ strlen($content->meta_description ?? '') }}</span>/160 caracteres - Descrição que aparece no Google
                    </p>
                </div>

                <div class="tw-mb-6">
                    <label for="content" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-2">Conteúdo <span class="tw-text-red-500">*</span></label>
                    <textarea class="tw-w-full" id="content" name="content" rows="20">{{ old('content', $content->content) }}</textarea>
                    <p class="tw-mt-1 tw-text-sm tw-text-slate-500">
                        Editor visual ativo. Use os botões para formatar ou clique em "Code View" para editar HTML diretamente.
                    </p>
                </div>

                <div class="tw-mb-6">
                    <div class="tw-flex tw-items-center">
                        <input type="checkbox" class="tw-h-4 tw-w-4 tw-text-indigo-600 tw-focus:ring-indigo-500 tw-border-gray-300 tw-rounded" id="published" name="published" 
                               value="1" {{ $content->published ? 'checked' : '' }}>
                        <label class="tw-ml-2 tw-block tw-text-sm tw-text-slate-900" for="published">
                            Publicado (visível para visitantes)
                        </label>
                    </div>
                </div>

                <div class="tw-flex tw-justify-end tw-space-x-3 tw-pt-4 tw-border-t tw-border-slate-200">
                    <a href="{{ route('content.show', $content->slug) }}" class="tw-bg-white tw-py-2 tw-px-4 tw-border tw-border-slate-300 tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500">
                        <i class="fa fa-times tw-mr-1"></i> Cancelar
                    </a>
                    <button type="submit" class="tw-inline-flex tw-justify-center tw-py-2 tw-px-4 tw-border tw-border-transparent tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-indigo-600 hover:tw-bg-indigo-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500">
                        <i class="fa fa-save tw-mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>

            <hr class="tw-my-8 tw-border-slate-200">
            
            <div class="tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-4">
                <div class="tw-flex">
                    <div class="tw-flex-shrink-0">
                        <i class="fa fa-info-circle tw-text-blue-400"></i>
                    </div>
                    <div class="tw-ml-3">
                        <p class="tw-text-sm tw-text-blue-700">
                            <strong>💡 Como usar o editor:</strong>
                        </p>
                        <ul class="tw-mt-2 tw-list-disc tw-list-inside tw-text-sm tw-text-blue-700">
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
    </div>
</div>
@endsection

@push('styles')
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    /* Fix for Summernote conflicts with Tailwind */
    .note-editor .note-toolbar .note-dropdown-menu, 
    .note-popover .popover-content .note-dropdown-menu {
        min-width: 180px;
    }
    .note-modal-footer {
        height: auto;
        padding-bottom: 20px;
    }
</style>
@endpush

@push('scripts')
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<!-- Summernote Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Contador de caracteres para meta description
        $('#meta_description').on('input', function() {
            $('#meta-char-count').text(this.value.length);
        });

        // Initialize Summernote
        $('#content').summernote({
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
            styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote'],
            callbacks: {
                onInit: function() {
                    console.log('Summernote inicializado!');
                },
                onChange: function(contents, $editable) {
                    $('#content').val(contents);
                }
            }
        });

        // Aviso antes de sair
        var formChanged = false;
        
        $('.note-editable').on('input', function() {
            formChanged = true;
        });
        
        $('input, select, textarea').on('change', function() {
            formChanged = true;
        });
        
        $(window).on('beforeunload', function(e) {
            if (formChanged) {
                return 'Você tem alterações não salvas. Deseja realmente sair?';
            }
        });
        
        $('form').on('submit', function() {
            var content = $('#content').summernote('code');
            $('#content').val(content);
            formChanged = false;
            return true;
        });
    });
</script>
@endpush
