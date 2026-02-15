@extends('layouts.admin')

@section('content')
<div class="tw-max-w-5xl tw-mx-auto">
    
    <div class="tw-mb-6 tw-flex tw-justify-between tw-items-center">
        <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Gerenciar Permissões</h1>
        <a href="{{ route('permissions.create') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500">
            <i class="fa fa-plus tw-mr-2"></i> Nova Permissão
        </a>
    </div>

    @if(session('success'))
    <div class="tw-bg-green-100 tw-border-l-4 tw-border-green-500 tw-text-green-700 tw-p-4 tw-mb-6 tw-rounded-md" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-overflow-hidden tw-border tw-border-slate-200">
        <div class="tw-overflow-x-auto">
            <table class="tw-min-w-full tw-divide-y tw-divide-slate-200">
                <thead class="tw-bg-slate-50">
                    <tr>
                        <th scope="col" class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-slate-500 tw-uppercase tw-tracking-wider">ID</th>
                        <th scope="col" class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-slate-500 tw-uppercase tw-tracking-wider">Nome</th>
                        <th scope="col" class="tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-slate-500 tw-uppercase tw-tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="tw-bg-white tw-divide-y tw-divide-slate-200">
                    @foreach($permissions as $permission)
                    <tr class="hover:tw-bg-slate-50 tw-transition-colors">
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-slate-500">
                            {{ $permission->id }}
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-800">
                                {{ $permission->name }}
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                            <a href="{{ route('permissions.edit', $permission) }}" class="tw-text-indigo-600 hover:tw-text-indigo-900 tw-mr-3">
                                <i class="fa fa-pencil"></i> Editar
                            </a>
                            <form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="tw-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir esta permissão?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tw-text-red-600 hover:tw-text-red-900 tw-bg-transparent tw-border-0 tw-p-0 tw-cursor-pointer">
                                    <i class="fa fa-trash"></i> Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
