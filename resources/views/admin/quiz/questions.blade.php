@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl"
     x-data="questionsManager({{ $quiz->id }}, {{ \Illuminate\Support\Js::from($quiz->questions) }})">
    
    <!-- Header -->
    <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-8 tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Perguntas do Quiz</h1>
            <p class="tw-text-slate-500">{{ $quiz->title }}</p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fas fa-pencil tw-mr-2"></i> Editar Quiz
            </a>
            <a href="{{ route('admin.quizzes.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fa fa-arrow-left tw-mr-2"></i> Voltar
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="tw-bg-emerald-50 tw-text-emerald-700 tw-p-4 tw-rounded-lg tw-mb-6 tw-border tw-border-emerald-200 tw-flex tw-justify-between tw-items-center">
            <div><i class="fas fa-check-circle tw-mr-2"></i> {{ session('success') }}</div>
            <button onclick="this.parentElement.remove()" class="tw-text-emerald-500 hover:tw-text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-12 tw-gap-8">
        <!-- Questions List -->
        <div class="md:tw-col-span-8">
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-flex tw-justify-between tw-items-center">
                    <span class="tw-font-medium tw-text-slate-700">
                        <strong class="tw-text-slate-900">{{ $quiz->questions->count() }}</strong> pergunta(s) neste quiz
                    </span>
                    <button @click="openAddModal()" class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-bg-brand-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                        <i class="fas fa-plus tw-mr-2"></i> Adicionar Pergunta
                    </button>
                </div>
                
                <div class="tw-p-6">
                    @if($quiz->questions->count() > 0)
                        <div id="questionsList" class="tw-space-y-3">
                            @foreach($quiz->questions as $index => $question)
                                <div class="question-item tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 hover:tw-shadow-md tw-transition-all" data-id="{{ $question->id }}">
                                    <div class="tw-flex tw-items-start tw-gap-4">
                                        <div class="tw-text-slate-400 tw-cursor-move tw-py-1">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        
                                        <div class="tw-flex-shrink-0 tw-w-8 tw-h-8 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white tw-font-bold tw-text-sm"
                                             style="background-color: {{ $quiz->color ?? '#912F56' }};">
                                            <span class="question-number">{{ $index + 1 }}</span>
                                        </div>
                                        
                                        <div class="tw-flex-1">
                                            <div class="tw-text-slate-800 tw-mb-2">
                                                {{ Str::limit($question->text, 150) }}
                                            </div>
                                            
                                            <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-2">
                                                @if($question->category)
                                                    <span class="tw-px-2 tw-py-0.5 tw-bg-cyan-50 tw-text-cyan-700 tw-rounded tw-text-xs tw-font-semibold">{{ $question->category->name }}</span>
                                                @endif
                                                <span class="tw-px-2 tw-py-0.5 tw-bg-slate-100 tw-text-slate-600 tw-rounded tw-text-xs tw-font-semibold">{{ $question->options->count() }} opções</span>
                                            </div>
                                        </div>
                                        
                                        <div class="tw-flex tw-items-center tw-gap-1">
                                            <button @click="viewQuestion({{ $question->id }})" class="tw-p-2 tw-text-blue-600 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors" title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('admin.questions.edit', $question) }}" class="tw-p-2 tw-text-slate-600 hover:tw-bg-slate-100 tw-rounded-lg tw-transition-colors" title="Editar">
                                                <i class="fas fa-pencil"></i>
                                            </a>
                                            <button @click="removeQuestion({{ $question->id }})" class="tw-p-2 tw-text-rose-600 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors" title="Remover do quiz">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="tw-text-slate-500 tw-text-sm tw-mt-4 tw-flex tw-items-center">
                            <i class="fas fa-info-circle tw-mr-2"></i> Arraste as perguntas para reordenar.
                        </p>
                    @else
                        <div class="tw-text-center tw-py-12">
                            <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-full tw-bg-slate-100 tw-mb-4">
                                <i class="fas fa-list-ol tw-text-2xl tw-text-slate-400"></i>
                            </div>
                            <p class="tw-text-slate-500 tw-mb-4">Nenhuma pergunta neste quiz ainda.</p>
                            <button @click="openAddModal()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                                <i class="fas fa-plus tw-mr-2"></i> Adicionar Primeira Pergunta
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="md:tw-col-span-4 tw-space-y-6">
            <!-- Quiz Info -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 hover:tw-bg-slate-50 tw-rounded-t-xl">
                    <h3 class="tw-font-bold tw-text-slate-800">Informações do Quiz</h3>
                </div>
                <div class="tw-p-6 tw-space-y-3">
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-slate-500">Status:</span>
                        @php
                            $statusColors = [
                                'published' => 'tw-bg-emerald-100 tw-text-emerald-800',
                                'draft' => 'tw-bg-amber-100 tw-text-amber-800',
                                'archived' => 'tw-bg-slate-100 tw-text-slate-800'
                            ];
                        @endphp
                        <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-bold tw-uppercase {{ $statusColors[$quiz->status] ?? '' }}">
                            {{ $quiz->status_label }}
                        </span>
                    </div>
                    @if($quiz->tribunal)
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-slate-500">Tribunal:</span>
                            <span class="tw-font-medium tw-text-slate-800">{{ $quiz->tribunal }}</span>
                        </div>
                    @endif
                    @if($quiz->category)
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-slate-500">Categoria:</span>
                            <span class="tw-font-medium tw-text-slate-800">{{ $quiz->category->name }}</span>
                        </div>
                    @endif
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-slate-500">Dificuldade:</span>
                        <span class="tw-font-medium tw-text-slate-800">{{ $quiz->difficulty_label }}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="tw-text-slate-500">Tempo:</span>
                        <span class="tw-font-medium tw-text-slate-800">~{{ $quiz->estimated_time }} min</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200">
                 <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 hover:tw-bg-slate-50 tw-rounded-t-xl">
                    <h3 class="tw-font-bold tw-text-slate-800">Ações Rápidas</h3>
                </div>
                <div class="tw-p-6 tw-space-y-3">
                    <a href="{{ route('admin.questions.create') }}?quiz_id={{ $quiz->id }}" class="tw-block tw-w-full tw-text-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-brand-600 tw-text-brand-600 tw-font-medium tw-rounded-lg hover:tw-bg-brand-50 tw-transition-colors">
                        <i class="fas fa-plus tw-mr-2"></i> Criar Nova Pergunta
                    </a>
                    <a href="{{ route('admin.questions.index') }}" class="tw-block tw-w-full tw-text-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                        <i class="fas fa-database tw-mr-2"></i> Banco de Perguntas
                    </a>
                    @if($quiz->status == 'published')
                        <a href="{{ route('quiz.show', $quiz->slug) }}" class="tw-block tw-w-full tw-text-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-emerald-600 tw-text-emerald-600 tw-font-medium tw-rounded-lg hover:tw-bg-emerald-50 tw-transition-colors" target="_blank">
                            <i class="fas fa-external-link-alt tw-mr-2"></i> Ver no Site
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div x-show="showAddModal" class="tw-fixed tw-inset-0 tw-z-50 tw-overflow-y-auto" style="display: none;"
         x-transition:enter="tw-transition tw-ease-out tw-duration-300"
         x-transition:enter-start="tw-opacity-0"
         x-transition:enter-end="tw-opacity-100"
         x-transition:leave="tw-transition tw-ease-in tw-duration-200"
         x-transition:leave-start="tw-opacity-100"
         x-transition:leave-end="tw-opacity-0">
        
        <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-px-4 tw-pt-4 tw-pb-20 tw-text-center sm:tw-block sm:tw-p-0">
            <div class="tw-fixed tw-inset-0 tw-transition-opacity" aria-hidden="true" @click="showAddModal = false">
                <div class="tw-absolute tw-inset-0 tw-bg-slate-900 tw-opacity-75"></div>
            </div>

            <span class="tw-hidden sm:tw-inline-block sm:tw-align-middle sm:tw-h-screen" aria-hidden="true">&#8203;</span>

            <div class="tw-inline-block tw-align-bottom tw-bg-white tw-rounded-lg tw-text-left tw-overflow-hidden tw-shadow-xl tw-transform tw-transition-all sm:tw-my-8 sm:tw-align-middle sm:tw-max-w-3xl sm:tw-w-full"
                 @click.stop>
                <div class="tw-bg-white tw-px-4 tw-pt-5 tw-pb-4 sm:tw-p-6 sm:tw-pb-4">
                    <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                        <h3 class="tw-text-lg tw-leading-6 tw-font-bold tw-text-slate-900">Adicionar Pergunta ao Quiz</h3>
                        <button @click="showAddModal = false" class="tw-text-slate-400 hover:tw-text-slate-500">
                            <i class="fas fa-times tw-text-xl"></i>
                        </button>
                    </div>

                    <div class="tw-mb-4">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Buscar no banco de perguntas:</label>
                        <div class="tw-flex tw-gap-2">
                            <input type="text" x-model="searchQuery" @input="debouncedSearch" 
                                   class="tw-flex-1 tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" 
                                   placeholder="Digite para buscar...">
                            <select x-model="searchCategory" @change="search" class="tw-w-48 tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500">
                                <option value="">Todas categorias</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="tw-h-96 tw-overflow-y-auto tw-border tw-border-slate-200 tw-rounded-lg tw-p-2 tw-bg-slate-50">
                        <template x-if="isLoading">
                            <div class="tw-text-center tw-py-12 tw-text-slate-500">
                                <i class="fas fa-spinner fa-spin tw-mr-2"></i> Buscando...
                            </div>
                        </template>
                        
                        <template x-if="!isLoading && searchResults.length === 0 && (searchQuery.length >= 2 || searchCategory)">
                            <div class="tw-text-center tw-py-12 tw-text-slate-500">
                                Nenhum resultado encontrado.
                            </div>
                        </template>

                         <template x-if="!isLoading && searchResults.length === 0 && searchQuery.length < 2 && !searchCategory">
                            <div class="tw-text-center tw-py-12 tw-text-slate-500">
                                Digite algo para buscar perguntas existentes.
                            </div>
                        </template>

                        <div class="tw-space-y-2">
                            <template x-for="question in searchResults" :key="question.id">
                                <div @click="addQuestion(question.id)" 
                                     class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-slate-200 tw-cursor-pointer hover:tw-border-brand-500 hover:tw-shadow-sm tw-transition-all">
                                    <div class="tw-mb-2">
                                        <div class="tw-font-medium tw-text-slate-800" x-text="question.text.substring(0, 200) + (question.text.length > 200 ? '...' : '')"></div>
                                    </div>
                                    <div class="tw-flex tw-gap-2 tw-text-xs">
                                        <template x-if="question.category">
                                            <span class="tw-px-2 tw-py-0.5 tw-bg-cyan-50 tw-text-cyan-700 tw-rounded tw-font-bold" x-text="question.category.name"></span>
                                        </template>
                                        <span class="tw-px-2 tw-py-0.5 tw-bg-slate-100 tw-text-slate-600 tw-rounded tw-font-bold" x-text="question.options.length + ' opções'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="tw-bg-slate-50 tw-px-4 tw-py-3 sm:tw-px-6 sm:tw-flex sm:tw-flex-row-reverse">
                    <a href="{{ route('admin.questions.create') }}?quiz_id={{ $quiz->id }}" class="tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-shadow-sm tw-px-4 tw-py-2 tw-bg-white tw-text-base tw-font-medium tw-text-brand-600 hover:tw-bg-brand-50 hover:tw-text-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 sm:tw-ml-3 sm:tw-w-auto sm:tw-text-sm">
                        <i class="fas fa-plus tw-mr-2"></i> Criar Nova Pergunta
                    </a>
                    <button type="button" @click="showAddModal = false" class="tw-mt-3 tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-slate-300 tw-shadow-sm tw-px-4 tw-py-2 tw-bg-white tw-text-base tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-slate-500 sm:tw-mt-0 sm:tw-ml-3 sm:tw-w-auto sm:tw-text-sm">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Question Modal -->
    <div x-show="showViewModal" class="tw-fixed tw-inset-0 tw-z-50 tw-overflow-y-auto" style="display: none;"
         x-transition:enter="tw-transition tw-ease-out tw-duration-300"
         x-transition:enter-start="tw-opacity-0"
         x-transition:enter-end="tw-opacity-100"
         x-transition:leave="tw-transition tw-ease-in tw-duration-200"
         x-transition:leave-start="tw-opacity-100"
         x-transition:leave-end="tw-opacity-0">
         
        <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-px-4 tw-pt-4 tw-pb-20 tw-text-center sm:tw-block sm:tw-p-0">
            <div class="tw-fixed tw-inset-0 tw-transition-opacity" aria-hidden="true" @click="showViewModal = false">
                <div class="tw-absolute tw-inset-0 tw-bg-slate-900 tw-opacity-75"></div>
            </div>

            <span class="tw-hidden sm:tw-inline-block sm:tw-align-middle sm:tw-h-screen" aria-hidden="true">&#8203;</span>

            <div class="tw-inline-block tw-align-bottom tw-bg-white tw-rounded-lg tw-text-left tw-overflow-hidden tw-shadow-xl tw-transform tw-transition-all sm:tw-my-8 sm:tw-align-middle sm:tw-max-w-3xl sm:tw-w-full"
                 @click.stop>
                <div class="tw-bg-white tw-px-4 tw-pt-5 tw-pb-4 sm:tw-p-6 sm:tw-pb-4">
                    <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                        <h3 class="tw-text-lg tw-leading-6 tw-font-bold tw-text-slate-900">Detalhes da Pergunta</h3>
                        <button @click="showViewModal = false" class="tw-text-slate-400 hover:tw-text-slate-500">
                            <i class="fas fa-times tw-text-xl"></i>
                        </button>
                    </div>

                    <div x-show="selectedQuestion" class="tw-space-y-6">
                        <div>
                            <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-2">Enunciado</h4>
                            <div class="tw-text-slate-900 tw-bg-slate-50 tw-p-4 tw-rounded-lg" x-text="selectedQuestion?.text"></div>
                        </div>

                        <div>
                            <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-2">Alternativas</h4>
                            <div class="tw-space-y-2">
                                <template x-for="option in selectedQuestion?.options" :key="option.id">
                                    <div class="tw-flex tw-items-start tw-gap-3 tw-p-3 tw-rounded-lg tw-border"
                                         :class="option.is_correct ? 'tw-bg-emerald-50 tw-border-emerald-200' : 'tw-bg-white tw-border-slate-200'">
                                        <div class="tw-flex-shrink-0 tw-w-6 tw-h-6 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-xs"
                                             :class="option.is_correct ? 'tw-bg-emerald-200 tw-text-emerald-800' : 'tw-bg-slate-200 tw-text-slate-600'"
                                             x-text="option.letter">
                                        </div>
                                        <div class="tw-flex-1 tw-text-sm" x-text="option.text"></div>
                                        <template x-if="option.is_correct">
                                            <i class="fas fa-check tw-text-emerald-600"></i>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <template x-if="selectedQuestion?.explanation">
                            <div>
                                <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-2">Explicação</h4>
                                <div class="tw-text-slate-600 tw-bg-blue-50 tw-p-4 tw-rounded-lg" x-text="selectedQuestion.explanation"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="tw-bg-slate-50 tw-px-4 tw-py-3 sm:tw-px-6 sm:tw-flex sm:tw-flex-row-reverse">
                    <template x-if="selectedQuestion">
                        <a :href="'/admin/questions/' + selectedQuestion.id + '/edit'" class="tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-shadow-sm tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-base tw-font-medium tw-text-white hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 sm:tw-ml-3 sm:tw-w-auto sm:tw-text-sm">
                            <i class="fas fa-pencil tw-mr-2"></i> Editar Pergunta
                        </a>
                    </template>
                    <button type="button" @click="showViewModal = false" class="tw-mt-3 tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-slate-300 tw-shadow-sm tw-px-4 tw-py-2 tw-bg-white tw-text-base tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-slate-500 sm:tw-mt-0 sm:tw-ml-3 sm:tw-w-auto sm:tw-text-sm">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('questionsManager', (quizId, questions) => ({
        showAddModal: false,
        showViewModal: false,
        searchQuery: '',
        searchCategory: '',
        searchResults: [],
        isLoading: false,
        searchTimeout: null,
        selectedQuestion: null,
        questions: questions,

        init() {
            // Initialize Sortable
            const list = document.getElementById('questionsList');
            if (list) {
                new Sortable(list, {
                    animation: 150,
                    handle: '.fas.fa-grip-vertical', // Ensure this matches the drag handle icon class
                    ghostClass: 'tw-bg-slate-100',
                    onEnd: () => {
                        this.updateOrder();
                    }
                });
            }
        },

        openAddModal() {
            this.showAddModal = true;
            this.searchQuery = '';
            this.searchResults = [];
            this.$nextTick(() => {
                 document.querySelector('[x-model="searchQuery"]').focus();
            });
        },

        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.search();
            }, 300);
        },

        search() {
            if (this.searchQuery.length < 2 && !this.searchCategory) {
                this.searchResults = [];
                return;
            }

            this.isLoading = true;
            fetch(`/admin/quizzes/${quizId}/questions/search?q=${encodeURIComponent(this.searchQuery)}&category_id=${this.searchCategory}`)
                .then(response => response.json())
                .then(data => {
                    this.searchResults = data.questions;
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.isLoading = false;
                });
        },

        addQuestion(questionId) {
            fetch(`/admin/quizzes/${quizId}/questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ question_id: questionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Erro ao adicionar pergunta');
                }
            });
        },

        removeQuestion(questionId) {
            if (!confirm('Remover esta pergunta do quiz?')) return;
            
            fetch(`/admin/quizzes/${quizId}/questions/${questionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Erro ao remover pergunta');
                }
            });
        },

        viewQuestion(questionId) {
            this.selectedQuestion = this.questions.find(q => q.id === questionId);
            this.showViewModal = true;
        },

        updateOrder() {
            const items = document.querySelectorAll('#questionsList .question-item');
            const order = Array.from(items).map(item => item.dataset.id);
            
            fetch(`/admin/quizzes/${quizId}/questions/reorder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update numbers visually
                    items.forEach((item, index) => {
                        item.querySelector('.question-number').textContent = index + 1;
                    });
                }
            });
        }
    }));
});
</script>
@endsection
@endsection
