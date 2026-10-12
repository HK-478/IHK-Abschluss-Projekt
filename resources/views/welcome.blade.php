<x-layout title="Login">
    <!-- action zeigt auf unsere Route, method MUSS POST sein -->
    <form action="{{ route('login.post') }}" method="POST" style="margin: auto">
        <!-- @csrf ist in Laravel Pflicht bei jedem POST-Formular! -->
        @csrf

        <fieldset id="login" class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
            <legend class="fieldset-legend">Login</legend>

            <!-- Fehlermeldung bei falschem Passwort -->
            @error('username')
            <div class="text-error text-sm mb-2">{{ $message }}</div>
            @enderror

            <label class="label">Benutzername</label>
            <!-- WICHTIG: Das name="" Attribut hinzugefügt -->
            <input type="text" name="username" class="input" placeholder="Benutzername" required />

            <label class="label">Passwort</label>
            <!-- WICHTIG: Das name="" Attribut hinzugefügt -->
            <input type="password" name="password" class="input" placeholder="Passwort" required />

            <!-- type="submit" sorgt dafür, dass das Formular abgeschickt wird -->
            <button type="submit" class="btn btn-neutral mt-4">Login</button>
        </fieldset>
    </form>
</x-layout>
