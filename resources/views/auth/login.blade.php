<x-app-layout>
    <h1>Login</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username -->
        <div class="input-container">
            <label class="input-label" for="name">Username</label>
            <input class="text-input" id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Password -->
        <div class="input-container">
            <label class="input-label" for="password">Password</label>
            <input class="text-input" id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember me -->
        <div class="input-container">
            <label for="remember_me">
                <input class="checkbox-input" id="remember_me" type="checkbox" name="remember">
                <span class="inline-input-label">Remember me</span>
            </label>
        </div>

        <div class="input-container">
            <button type="submit" style="margin-top: ">
                Log in
            </button>
        </div>
    </form>
</x-app-layout>