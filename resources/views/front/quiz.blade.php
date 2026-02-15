@extends('front.base')

@section('page-title', $quiz->title)

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-6xl"
     x-data="quizGame({
         quizId: {{ $quiz->id }},
         attemptId: {{ $attempt->id }},
         slug: '{{ $quiz->slug }}',
         totalQuestions: {{ $questions->count() }},
         showFeedbackImmediately: {{ $quiz->show_feedback_immediately ? 'true' : 'false' }},
         csrfToken: '{{ csrf_token() }}',
         initialAnswers: @js($attempt->answers->mapWithKeys(function($a) { return [$a->question_id => ['selected' => $a->selected_option_id, 'is_correct' => $a->is_correct]]; }))
     })">

    <!-- Breadcrumbs -->
    <nav class="tw-flex tw-text-sm tw-text-slate-500 tw-mb-8 hidden md:tw-flex" aria-label="Breadcrumb">
        <ol class="tw-inline-flex tw-items-center tw-space-x-1 md:tw-space-x-3">
            <li class="tw-inline-flex tw-items-center">
                <a href="{{ url('/') }}" class="tw-inline-flex tw-items-center tw-text-slate-500 hover:tw-text-brand-600">
                    <i class="fa fa-home tw-mr-2"></i> Início
                </a>
            </li>
            <li class="tw-inline-flex tw-items-center">
                <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                <a href="{{ route('quizzes.index') }}" class="tw-text-slate-500 hover:tw-text-brand-600">Quizzes</a>
            </li>
            @if($quiz->category)
            <li class="tw-inline-flex tw-items-center">
                <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                <a href="{{ route('quizzes.category', $quiz->category->slug) }}" class="tw-text-slate-500 hover:tw-text-brand-600">{{ $quiz->category->name }}</a>
            </li>
            @endif
            <li class="tw-inline-flex tw-items-center">
                <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                <span class="tw-text-slate-700 tw-font-medium">{{ Str::limit($quiz->title, 40) }}</span>
            </li>
        </ol>
    </nav>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8 tw-items-start">
        
        <!-- Main Quiz Area -->
        <div class="lg:tw-col-span-2">
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
                
                <!-- Header -->
                <div class="tw-p-6 tw-bg-brand-600 tw-bg-gradient-to-r tw-from-brand-600 tw-to-brand-700 tw-text-white">
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-3 tw-mb-3">
                        @if($quiz->category)
                            <span class="tw-bg-white/20 tw-backdrop-blur-sm tw-px-2.5 tw-py-0.5 tw-rounded tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide">
                                {{ $quiz->category->name }}
                            </span>
                        @endif
                        @if($quiz->tribunal)
                            <span class="tw-bg-white/20 tw-backdrop-blur-sm tw-px-2.5 tw-py-0.5 tw-rounded tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide">
                                {{ $quiz->tribunal }}
                            </span>
                        @endif
                    </div>
                    
                    <h1 class="tw-text-2xl tw-font-bold tw-mb-4">{{ $quiz->title }}</h1>
                    
                    <div class="tw-flex tw-flex-wrap tw-gap-6 tw-text-sm tw-font-medium tw-text-brand-100">
                        <span class="tw-flex tw-items-center tw-gap-2">
                            <i class="fa fa-clock-o"></i> ~{{ $quiz->estimated_time }} min
                        </span>
                        <span class="tw-flex tw-items-center tw-gap-2">
                            <i class="fa fa-signal"></i> {{ $quiz->difficulty_label }}
                        </span>
                    </div>
                </div>

                <!-- Progress Bar (Visible Only) -->
                @if($quiz->show_progress)
                <div class="tw-bg-slate-50 tw-px-6 tw-py-4 tw-border-b tw-border-slate-100">
                    <div class="tw-flex tw-justify-between tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-2">
                        <span>Questão <span x-text="currentQuestionIndex + 1"></span> de {{ $questions->count() }}</span>
                        <span x-text="Math.round(((currentQuestionIndex) / totalQuestions) * 100) + '%'"></span>
                    </div>
                    <div class="tw-w-full tw-bg-slate-200 tw-rounded-full tw-h-2.5 tw-overflow-hidden">
                        <div class="tw-bg-brand-600 tw-h-2.5 tw-rounded-full tw-transition-all tw-duration-500"
                             :style="'width: ' + ((currentQuestionIndex / totalQuestions) * 100) + '%'"></div>
                    </div>
                </div>
                @endif

                <!-- Questions Container -->
                <div class="tw-p-6 md:tw-p-8 tw-relative tw-min-h-[400px]">
                    <!-- Loading Overlay -->
                    <div x-show="loading" 
                         class="tw-absolute tw-inset-0 tw-bg-white/80 tw-backdrop-blur-sm tw-z-50 tw-flex tw-items-center tw-justify-center"
                         x-transition.opacity>
                        <div class="tw-animate-spin tw-rounded-full tw-h-12 tw-w-12 tw-border-b-2 tw-border-brand-600"></div>
                    </div>

                    @foreach($questions as $index => $question)
                        <div x-show="currentQuestionIndex === {{ $index }}" 
                             x-transition:enter="tw-transition tw-ease-out tw-duration-300"
                             x-transition:enter-start="tw-opacity-0 tw-translate-x-8"
                             x-transition:enter-end="tw-opacity-100 tw-translate-x-0"
                             x-transition:leave="tw-transition tw-ease-in tw-duration-200"
                             x-transition:leave-start="tw-opacity-100 tw-translate-x-0"
                             x-transition:leave-end="tw-opacity-0 tw-translate-x-[-2rem]"
                             style="display: none;">
                            
                            <div class="tw-mb-8">
                                <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-rounded-full tw-bg-brand-50 tw-text-brand-600 tw-text-xl tw-font-bold tw-mb-4 tw-shadow-sm">
                                    {{ $index + 1 }}
                                </span>
                                <div class="tw-prose tw-prose-slate tw-max-w-none">
                                    <h3 class="tw-text-xl tw-font-medium tw-text-slate-800 tw-leading-relaxed">
                                        {{ $question->text }}
                                    </h3>
                                </div>
                            </div>

                            <div class="tw-space-y-4">
                                @foreach($question->options as $option)
                                    <button 
                                        @click="selectOption({{ $question->id }}, {{ $option->id }})"
                                        :disabled="isAnswered({{ $question->id }})"
                                        :class="{
                                            'tw-border-slate-200 hover:tw-border-brand-300 hover:tw-bg-brand-50 hover:tw-shadow-md': !isAnswered({{ $question->id }}) && selectedOption({{ $question->id }}) !== {{ $option->id }},
                                            'tw-border-brand-500 tw-bg-brand-50 tw-ring-1 tw-ring-brand-500': !isAnswered({{ $question->id }}) && selectedOption({{ $question->id }}) === {{ $option->id }},
                                            'tw-opacity-60 tw-cursor-not-allowed': isAnswered({{ $question->id }}) && !isFeedbackVisible({{ $question->id }}),
                                            'tw-border-emerald-500 tw-bg-emerald-50 tw-ring-1 tw-ring-emerald-500': isFeedbackVisible({{ $question->id }}) && {{ $option->is_correct ? 'true' : 'false' }},
                                            'tw-border-rose-500 tw-bg-rose-50 tw-ring-1 tw-ring-rose-500': isFeedbackVisible({{ $question->id }}) && !{{ $option->is_correct ? 'true' : 'false' }} && selectedOption({{ $question->id }}) === {{ $option->id }}
                                        }"
                                        class="tw-w-full tw-text-left tw-flex tw-items-start tw-p-4 tw-rounded-xl tw-border-2 tw-transition-all tw-duration-200 tw-relative tw-group">
                                        
                                        <div :class="{
                                                'tw-bg-slate-100 tw-text-slate-500 group-hover:tw-bg-brand-200 group-hover:tw-text-brand-700': !isAnswered({{ $question->id }}) && selectedOption({{ $question->id }}) !== {{ $option->id }},
                                                'tw-bg-brand-600 tw-text-white': !isAnswered({{ $question->id }}) && selectedOption({{ $question->id }}) === {{ $option->id }},
                                                'tw-bg-emerald-500 tw-text-white': isFeedbackVisible({{ $question->id }}) && {{ $option->is_correct ? 'true' : 'false' }},
                                                'tw-bg-rose-500 tw-text-white': isFeedbackVisible({{ $question->id }}) && !{{ $option->is_correct ? 'true' : 'false' }} && selectedOption({{ $question->id }}) === {{ $option->id }}
                                            }" 
                                            class="tw-flex-shrink-0 tw-w-8 tw-h-8 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-font-bold tw-mr-4 tw-transition-colors">
                                            {{ $option->letter }}
                                        </div>
                                        
                                        <div class="tw-flex-1 tw-pt-1">
                                            {{ $option->text }}
                                        </div>

                                        <!-- Feedback Icons -->
                                        <div x-show="isFeedbackVisible({{ $question->id }})" class="tw-ml-3">
                                            @if($option->is_correct)
                                                <i class="fa fa-check-circle tw-text-emerald-500 tw-text-xl"></i>
                                            @else
                                                <i x-show="selectedOption({{ $question->id }}) === {{ $option->id }}" class="fa fa-times-circle tw-text-rose-500 tw-text-xl"></i>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            <!-- Actions -->
                            <div class="tw-mt-8 tw-flex tw-justify-between tw-items-center">
                                <button @click="prevQuestion()" 
                                        x-show="currentQuestionIndex > 0"
                                        class="tw-px-4 tw-py-2 tw-text-slate-500 tw-font-medium hover:tw-text-brand-600 tw-transition-colors">
                                    <i class="fa fa-arrow-left tw-mr-2"></i> Anterior
                                </button>
                                <div x-show="currentQuestionIndex === 0"></div> <!-- Spacer -->

                                <div>
                                    <button @click="confirmAnswer({{ $question->id }})"
                                            x-show="selectedOption({{ $question->id }}) && !isAnswered({{ $question->id }})"
                                            class="tw-px-6 tw-py-3 tw-bg-brand-600 tw-text-white tw-font-semibold tw-rounded-lg tw-shadow-md hover:tw-bg-brand-700 hover:tw-shadow-lg tw-transition-all tw-transform hover:tw--translate-y-0.5">
                                        Confirmar Resposta <i class="fa fa-check tw-ml-2"></i>
                                    </button>

                                    <button @click="nextQuestion()"
                                            x-show="isAnswered({{ $question->id }}) && currentQuestionIndex < totalQuestions - 1"
                                            class="tw-px-6 tw-py-3 tw-bg-brand-600 tw-text-white tw-font-semibold tw-rounded-lg tw-shadow-md hover:tw-bg-brand-700 tw-transition-all">
                                        Próxima Pergunta <i class="fa fa-arrow-right tw-ml-2"></i>
                                    </button>

                                    <a href="{{ route('quiz.results', $quiz->slug) }}"
                                       x-show="isAnswered({{ $question->id }}) && currentQuestionIndex === totalQuestions - 1"
                                       class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-emerald-600 tw-text-white tw-font-semibold tw-rounded-lg tw-shadow-md hover:tw-bg-emerald-700 tw-transition-all">
                                        Ver Resultado <i class="fa fa-trophy tw-ml-2"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Explanation Feedback -->
                            <div x-show="isFeedbackVisible({{ $question->id }})" 
                                 x-transition:enter="tw-transition tw-ease-out tw-duration-300"
                                 x-transition:enter-start="tw-opacity-0 tw-translate-y-4"
                                 x-transition:enter-end="tw-opacity-100 tw-translate-y-0"
                                 class="tw-mt-8 tw-p-6 tw-rounded-xl tw-border-l-4"
                                 :class="isCorrect({{ $question->id }}) ? 'tw-bg-emerald-50 tw-border-emerald-500' : 'tw-bg-rose-50 tw-border-rose-500'">
                                
                                <h4 class="tw-font-bold tw-flex tw-items-center tw-gap-2 tw-mb-2"
                                    :class="isCorrect({{ $question->id }}) ? 'tw-text-emerald-800' : 'tw-text-rose-800'">
                                    <i class="fa" :class="isCorrect({{ $question->id }}) ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    <span x-text="isCorrect({{ $question->id }}) ? 'Resposta Correta!' : 'Resposta Incorreta'"></span>
                                </h4>
                                
                                <div class="tw-text-slate-700 tw-leading-relaxed">
                                    {{ $question->explanation }}
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            
            </div>
        </div>

        <!-- Sidebar Stats -->
        <aside class="tw-space-y-6 lg:tw-sticky lg:tw-top-8">
             
             @if($quiz->show_ads)
             <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-4 tw-text-center">
                 <div class="tw-bg-slate-100 tw-rounded-lg tw-p-8 tw-text-slate-400 tw-text-sm">
                     <span class="tw-text-xs tw-uppercase tw-tracking-wider tw-block tw-mb-1">Publicidade</span>
                     Espaço para Anúncio
                 </div>
             </div>
             @endif

             <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
                 <div class="tw-p-4 tw-bg-slate-50 tw-border-b tw-border-slate-100 tw-font-semibold tw-text-slate-700">
                     <i class="fa fa-bar-chart tw-mr-2 tw-text-brand-500"></i> Seu Progresso
                 </div>
                 <div class="tw-p-6">
                     <div class="tw-grid tw-grid-cols-3 tw-gap-4 tw-text-center">
                         <div>
                             <div class="tw-text-2xl tw-font-bold tw-text-emerald-500" x-text="score.correct">0</div>
                             <div class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase">Acertos</div>
                         </div>
                         <div>
                             <div class="tw-text-2xl tw-font-bold tw-text-rose-500" x-text="score.wrong">0</div>
                             <div class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase">Erros</div>
                         </div>
                         <div>
                             <div class="tw-text-2xl tw-font-bold tw-text-slate-700">
                                 <span x-text="score.correct + score.wrong"></span>/<span x-text="totalQuestions"></span>
                             </div>
                             <div class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase">Feitas</div>
                         </div>
                     </div>
                 </div>
             </div>

        </aside>

    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quizGame', (config) => ({
            currentQuestionIndex: 0,
            answers: {}, // Tracks selected options: { questionId: optionId }
            results: {}, // Tracks correctness: { questionId: boolean }
            loading: false,
            score: {
                correct: 0,
                wrong: 0
            },
            
            totalQuestions: config.totalQuestions,

            init() {
                // Restore state from backend if returning to quiz
                if (config.initialAnswers) {
                    Object.entries(config.initialAnswers).forEach(([qId, data]) => {
                        this.answers[qId] = data.selected;
                        this.results[qId] = data.is_correct;
                        if (data.is_correct) this.score.correct++;
                        else this.score.wrong++;
                    });
                    
                    // Advance to first unanswered question
                    // You might need logic here to find index of first unanswered
                    // For now, let's just stay at 0 or calc
                }
            },

            selectOption(questionId, optionId) {
                if (this.isAnswered(questionId)) return;
                this.answers[questionId] = optionId;
            },

            selectedOption(questionId) {
                return this.answers[questionId];
            },

            isAnswered(questionId) {
                return this.results.hasOwnProperty(questionId);
            },

            isCorrect(questionId) {
                return this.results[questionId] === true;
            },

            isFeedbackVisible(questionId) {
                return this.isAnswered(questionId) && config.showFeedbackImmediately;
            },

            confirmAnswer(questionId) {
                const optionId = this.answers[questionId];
                if (!optionId) return;

                this.loading = true;
                const startTime = Date.now(); // Simplified timing

                fetch(`/quiz/${config.slug}/answer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        attempt_id: config.attemptId,
                        question_id: questionId,
                        option_id: optionId,
                        time_spent: 10 // Simplified, ideally track real time
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.results[questionId] = data.is_correct;
                        if (data.is_correct) this.score.correct++;
                        else this.score.wrong++;
                    }
                })
                .catch(err => {
                    this.loading = false;
                    console.error(err);
                    alert('Ocorreu um erro ao enviar sua resposta. Tente novamente.');
                });
            },

            nextQuestion() {
                if (this.currentQuestionIndex < this.totalQuestions - 1) {
                    this.currentQuestionIndex++;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },

            prevQuestion() {
                if (this.currentQuestionIndex > 0) {
                    this.currentQuestionIndex--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }));
    });
</script>
@endsection
