@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-10 px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">Firme</h1>
        <a href="{{ route('dashboard') }}" class="text-sm text-teal-600 hover:text-teal-700">← Nazad na dashboard</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-center">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-2 font-medium text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-4 py-2 font-medium text-gray-600 dark:text-gray-300">Naziv</th>
                    <th class="px-4 py-2 font-medium text-gray-600 dark:text-gray-300">Broj instrumenata</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($companies as $company)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-2 align-middle">{{ ($companies->currentPage()-1)*$companies->perPage() + $loop->iteration }}</td>
                    <td class="px-4 py-2 font-medium align-middle">{{ $company->name }}</td>
                    <td class="px-4 py-2 align-middle">{{ $company->instruments()->count() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-4 text-gray-500 dark:text-gray-400">Nema firmi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $companies->links() }}
    </div>
</div>
@endsection
