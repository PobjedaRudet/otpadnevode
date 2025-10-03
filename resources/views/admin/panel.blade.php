@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-6 lg:px-8">
    <h1 class="text-2xl font-semibold mb-8 text-gray-900">Administratorski panel</h1>
    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-emerald-100 text-emerald-800">{{ session('success') }}</div>
    @endif
    <table class="min-w-full bg-white rounded shadow border">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">Ime</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Rola</th>
                <th class="px-4 py-2 text-left">Akcija</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $user->name }}</td>
                <td class="px-4 py-2">{{ $user->email }}</td>
                <td class="px-4 py-2">
                    <span class="inline-block px-2 py-1 rounded {{ $user->role == 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">{{ $user->role }}</span>
                </td>
                <td class="px-4 py-2">
                    <form method="POST" action="{{ route('admin.user.role', $user->id) }}" class="flex flex-col gap-2">
                        @csrf
                        <div class="flex items-center gap-2 mb-2">
                            <select name="role" class="border rounded px-2 py-1 text-sm">
                                <option value="user" @if($user->role=='user') selected @endif>Korisnik</option>
                                <option value="admin" @if($user->role=='admin') selected @endif>Administrator</option>
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-2">
                            @foreach($availablePermissions as $permKey => $permLabel)
                                <label class="flex items-center gap-1 text-xs">
                                    <input type="checkbox" name="permissions[]" value="{{ $permKey }}" @if(is_array($user->permissions) && in_array($permKey, $user->permissions)) checked @endif>
                                    {{ $permLabel }}
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="px-2 py-1 rounded bg-teal-600 text-white text-sm hover:bg-teal-700">Dodijeli</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
