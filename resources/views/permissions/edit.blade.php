@extends('layouts.admin')

@section('content')
<div class="tw-max-w-5xl tw-mx-auto">
    
    <div class="tw-mb-6">
        <nav class="tw-text-sm tw-font-medium tw-text-slate-500 tw-mb-2">
            <a href="{{ route('permissions.index') }}" class="hover:tw-text-indigo-600 transition-colors">Permissões</a> 
            <span class="tw-mx-2">/</span> 
            <span class="tw-text-slate-800">Editar Permissão</span>
        </nav>
        <div class="tw-flex tw-items-center">
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Editando: <span class="tw-text-indigo-600">{{ $permission->name }}</span></h1>
            <span class="tw-ml-3 tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600">ID: {{ $permission->id }}</span>
        </div>
    </div>

    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-overflow-hidden tw-border tw-border-slate-200">
        <div class="tw-p-6">
            <form action="{{ route('permissions.update', $permission) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="tw-mb-6">
                    <label for="name" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Nome da Permissão</label>
                    <input type="text" name="name" id="name" class="tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-indigo-500 focus:tw-ring-indigo-500 sm:tw-text-sm" value="{{ old('name', $permission->name) }}" required>
                    @error('name')
                        <p class="tw-mt-1 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-flex tw-justify-end tw-space-x-3 tw-pt-4 tw-border-t tw-border-slate-100">
                    <a href="{{ route('permissions.index') }}" class="tw-bg-white tw-py-2 tw-px-4 tw-border tw-border-slate-300 tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500">
                        Cancelar
                    </a>
                    <button type="submit" class="tw-inline-flex tw-justify-center tw-py-2 tw-px-4 tw-border tw-border-transparent tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-indigo-600 hover:tw-bg-indigo-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500">
                        <i class="fa fa-save tw-mr-2"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
