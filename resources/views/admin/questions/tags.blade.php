@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Tags de Perguntas</h2>
                    <p class="text-muted mb-0">Gerencie as tags para categorizar perguntas</p>
                </div>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Create Tag -->
            <div class="card mb-4">
                <div class="card-header">Criar Nova Tag</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.questions.tags.store') }}" class="form-inline">
                        @csrf
                        <div class="form-group mr-3 flex-grow-1">
                            <input type="text" class="form-control w-100 @error('name') is-invalid @enderror" 
                                   name="name" placeholder="Nome da tag..." required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Criar Tag
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tags List -->
            <div class="card">
                <div class="card-header">{{ $tags->count() }} tag(s)</div>
                <div class="card-body">
                    @if($tags->count() > 0)
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Slug</th>
                                    <th>Perguntas</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tags as $tag)
                                    <tr>
                                        <td><strong>{{ $tag->name }}</strong></td>
                                        <td><code>{{ $tag->slug }}</code></td>
                                        <td>
                                            <a href="{{ route('admin.questions.index', ['tag_id' => $tag->id]) }}">
                                                {{ $tag->questions_count }} pergunta(s)
                                            </a>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.questions.tags.destroy', $tag) }}" method="POST" 
                                                  class="d-inline" onsubmit="return confirm('Excluir esta tag?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        {{ $tag->questions_count > 0 ? 'disabled' : '' }}>
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-tags fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhuma tag criada ainda.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
