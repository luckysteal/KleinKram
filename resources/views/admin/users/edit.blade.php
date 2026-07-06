@extends('layouts.admin')

@section('title', 'Benutzer bearbeiten – ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Benutzer bearbeiten</h1>
            <p class="text-sm text-gray-500">Account von <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>
        </div>
    </div>

    {{-- Flash / Validation Errors --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 space-y-1">
            <p class="text-sm font-semibold text-red-700">Bitte korrigiere die folgenden Fehler:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li class="text-sm text-red-600">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {{-- Name --}}
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border @error('name') border-red-400 @else border-gray-300 @enderror rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            {{-- Email --}}
            <div class="sm:col-span-2">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    E-Mail-Adresse <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full border @error('email') border-red-400 @else border-gray-300 @enderror rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            {{-- Role --}}
            <div class="sm:col-span-2">
                <label for="user_role" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Benutzertyp / Rolle <span class="text-red-500">*</span>
                </label>
                @php
                    $currentRole = $user->is_admin ? 'admin' : ($user->role === 'SCK' ? 'SCK' : 'user');
                @endphp
                <select id="user_role" name="user_role" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                    <option value="user"  {{ old('user_role', $currentRole) === 'user'  ? 'selected' : '' }}>Benutzer (Standard)</option>
                    <option value="SCK"   {{ old('user_role', $currentRole) === 'SCK'   ? 'selected' : '' }}>SCK – Service Center Klein</option>
                    <option value="admin" {{ old('user_role', $currentRole) === 'admin' ? 'selected' : '' }}>Admin (Vollzugriff)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    <strong>SCK</strong> = Zugang zum SCK-Lagersystem. &nbsp;
                    <strong>Admin</strong> = Zugang zu diesem Admin-Panel.
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-between items-center pt-4 border-t border-gray-100">
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('reset-modal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-amber-300 text-sm font-semibold text-amber-700 hover:bg-amber-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Passwort zurücksetzen
                </button>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    Abbrechen
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition-colors shadow-sm">
                    Änderungen speichern
                </button>
            </div>
        </div>
    </form>

    {{-- Danger Zone --}}
    @if($user->id !== auth()->id())
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-6">
        <h3 class="text-sm font-bold text-red-700 mb-1">Gefahrenzone</h3>
        <p class="text-sm text-gray-500 mb-4">Das Löschen dieses Accounts kann nicht rückgängig gemacht werden.</p>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
              onsubmit="return confirm('Benutzer \"{{ addslashes($user->name) }}\" wirklich permanent löschen?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Benutzer löschen
            </button>
        </form>
    </div>
    @endif
</div>

{{-- Password Reset Modal --}}
<div id="reset-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 space-y-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Passwort zurücksetzen</h3>
                <p class="text-sm text-gray-500">Für: <strong>{{ $user->name }}</strong></p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Neues Passwort</label>
                <input type="password" name="new_password" required minlength="6"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Passwort bestätigen</label>
                <input type="password" name="new_password_confirmation" required minlength="6"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('reset-modal').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    Abbrechen
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold transition-colors">
                    Passwort setzen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('reset-modal').classList.add('hidden');
        }
    });
</script>
@endsection
