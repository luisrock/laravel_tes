@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <div class="tw-max-w-4xl tw-mx-auto">
        <!-- Header -->
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-8 tw-gap-4">
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Tags de Perguntas</h1>
                <p class="tw-text-slate-500">Gerencie as tags para categorizar perguntas</p>
            </div>
            <a href="{{ route('admin.questions.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fa fa-arrow-left tw-mr-2"></i> Voltar
            </a>
        </div>

        @if (session('success'))
            <div class="tw-bg-emerald-50 tw-text-emerald-700 tw-p-4 tw-rounded-lg tw-mb-6 tw-border tw-border-emerald-200 tw-flex tw-justify-between tw-items-center">
                <div><i class="fas fa-check-circle tw-mr-2"></i> {{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="tw-text-emerald-500 hover:tw-text-emerald-700"><i class="fas fa-times"></i></button>
            </div>
        @endif

        <!-- Create Tag -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
            <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-4">Criar Nova Tag</h2>
            <form method="POST" action="{{ route('admin.questions.tags.store') }}" class="tw-flex tw-flex-col md:tw-flex-row tw-gap-4 tw-items-start">
                @csrf
                <div class="tw-flex-1 tw-w-full">
                    <input type="text" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('name') tw-border-rose-500 @enderror" 
                           name="name" placeholder="Nome da tag..." required>
                    @error('name')
                        <p class="tw-text-xs tw-text-rose-500 tw-mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="tw-inline-flex tw-items-center tw-justify-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-bold tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors tw-w-full md:tw-w-auto">
                    <i class="fas fa-plus tw-mr-2"></i> Criar Tag
                </button>
            </form>
        </div>

        <!-- Tags List -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100">
                <h3 class="tw-font-bold tw-text-slate-800">{{ $tags->count() }} tag(s)</h3>
            </div>
            
            <div class="tw-overflow-x-auto">
                @if($tags->count() > 0)
                    <table class="tw-w-full tw-text-left tw-border-collapse">
                        <thead>
                            <tr class="tw-bg-slate-50 tw-text-slate-600 tw-text-sm tw-uppercase tw-tracking-wider">
                                <th class="tw-px-6 tw-py-3 tw-font-semibold">Nome</th>
                                <th class="tw-px-6 tw-py-3 tw-font-semibold">Slug</th>
                                <th class="tw-px-6 tw-py-3 tw-font-semibold">Perguntas</th>
                                <th class="tw-px-6 tw-py-3 tw-font-semibold tw-text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-slate-200">
                            @foreach($tags as $tag)
                                <tr class="hover:tw-bg-slate-50 tw-transition-colors">
                                    <td class="tw-px-6 tw-py-4 tw-text-slate-900 tw-font-medium">{{ $tag->name }}</td>
                                    <td class="tw-px-6 tw-py-4">
                                        <code class="tw-px-2 tw-py-1 tw-bg-slate-100 tw-rounded tw-text-slate-600 tw-text-xs">{{ $tag->slug }}</code>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <a href="{{ route('admin.questions.index', ['tag_id' => $tag->id]) }}" class="tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline tw-font-medium">
                                            {{ $tag->questions_count }} pergunta(s)
                                        </a>
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        <form action="{{ route('admin.questions.tags.destroy', $tag) }}" method="POST" class="tw-inline-block" onsubmit="return confirm('Excluir esta tag?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tw-p-2 tw-text-rose-600 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors disabled:tw-opacity-50 disabled:tw-cursor-not-allowed" 
                                                    title="Excluir" {{ $tag->questions_count > 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="tw-text-center tw-py-12">
                        <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-full tw-bg-slate-100 tw-mb-4">
                            <i class="fas fa-tags tw-text-2xl tw-text-slate-400"></i>
                        </div>
                        <p class="tw-text-slate-500">Nenhuma tag criada ainda.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
