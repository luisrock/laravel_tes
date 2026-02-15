@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <!-- Header -->
    <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-8 tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">{{ __('Users') }}</h1>
            <p class="tw-text-slate-500">Gerencie os usuários do sistema</p>
        </div>
        <a href="{{ route('users.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
            <i class="fas fa-plus tw-mr-2"></i> Add User
        </a>
    </div>

    @if (session('success'))
        <div class="tw-bg-emerald-50 tw-text-emerald-700 tw-p-4 tw-rounded-lg tw-mb-6 tw-border tw-border-emerald-200 tw-flex tw-justify-between tw-items-center">
            <div><i class="fas fa-check-circle tw-mr-2"></i> {{ session('success') }}</div>
            <button onclick="this.parentElement.remove()" class="tw-text-emerald-500 hover:tw-text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <!-- Users Table -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-border-collapse">
                <thead>
                    <tr class="tw-bg-slate-50 tw-text-slate-600 tw-text-sm tw-uppercase tw-tracking-wider">
                        <th class="tw-px-6 tw-py-3 tw-font-semibold">#</th>
                        <th class="tw-px-6 tw-py-3 tw-font-semibold">Name</th>
                        <th class="tw-px-6 tw-py-3 tw-font-semibold">Email</th>
                        <th class="tw-px-6 tw-py-3 tw-font-semibold">Role</th>
                        <th class="tw-px-6 tw-py-3 tw-font-semibold tw-text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-slate-200">
                    @foreach($users as $user)
                        <tr class="hover:tw-bg-slate-50 tw-transition-colors">
                            <td class="tw-px-6 tw-py-4 tw-text-slate-500 tw-text-sm">{{ $loop->iteration }}</td>
                            <td class="tw-px-6 tw-py-4">
                                <span class="tw-font-medium tw-text-slate-900">{{ $user->name }}</span>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-text-slate-600">{{ $user->email }}</td>
                            <td class="tw-px-6 tw-py-4">
                                @foreach($user->getRoleNames() as $role)
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                                        {{ $role }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-text-right">
                                <a href="{{ route('users.edit', $user) }}" class="tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                                    <i class="fas fa-edit tw-mr-1.5"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(method_exists($users, 'links'))
            <div class="tw-px-6 tw-py-4 tw-border-t tw-border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
