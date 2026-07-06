@extends('layouts.admin')

@section('title', 'Neuen Benutzer erstellen')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Neuen Benutzer erstellen</h1>
            <p class="text-sm text-gray-500">Erstellt einen neuen Account im System.</p>
        </div>
    </div>

    {{-- Validation Errors --}}
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

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {{-- Name --}}
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="z.B. Andreas Klein"
                       class="w-full border @error('name') border-red-400 @else border-gray-300 @enderror rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            {{-- Email --}}
            <div class="sm:col-span-2">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    E-Mail-Adresse <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       placeholder="z.B. andreas@sck.de"
                       class="w-full border @error('email') border-red-400 @else border-gray-300 @enderror rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            {{-- Role --}}
            <div class="sm:col-span-2">
                <label for="user_role" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Benutzertyp / Rolle <span class="text-red-500">*</span>
                </label>
                <select id="user_role" name="user_role" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                    <option value="">-- Bitte wählen --</option>
                    <option value="user"  {{ old('user_role') === 'user'  ? 'selected' : '' }}>Benutzer (Standard)</option>
                    <option value="SCK"   {{ old('user_role') === 'SCK'   ? 'selected' : '' }}>SCK – Service Center Klein</option>
                    <option value="admin" {{ old('user_role') === 'admin' ? 'selected' : '' }}>Admin (Vollzugriff)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    <strong>SCK</strong> = Zugang zum SCK-Lagersystem. &nbsp;
                    <strong>Admin</strong> = Zugang zu diesem Admin-Panel. &nbsp;
                    <strong>Benutzer</strong> = Standard-Zugang.
                </p>
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Passwort <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password" name="password" required minlength="6"
                       placeholder="Mindestens 6 Zeichen"
                       class="w-full border @error('password') border-red-400 @else border-gray-300 @enderror rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            {{-- Password Confirmation --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Passwort bestätigen <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                       placeholder="Passwort wiederholen"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.users.index') }}"
               class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                Abbrechen
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition-colors shadow-sm">
                Benutzer erstellen
            </button>
        </div>
    </form>
</div>
@endsection
