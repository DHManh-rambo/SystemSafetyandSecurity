<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Tên đăng nhập -->
        <div>
            <x-input-label for="ten_dang_nhap" :value="__('Tên đăng nhập')" />
            <x-text-input id="ten_dang_nhap" class="block mt-1 w-full"
                type="text"
                name="ten_dang_nhap"
                :value="old('ten_dang_nhap')"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('ten_dang_nhap')" class="mt-2" />
        </div>

        <!-- Mật khẩu -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mật khẩu')" />
            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Ghi nhớ -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-rose-600 shadow-sm focus:ring-rose-300"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Ghi nhớ đăng nhập') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <!-- Nút đăng ký -->
            <a href="{{ route('register') }}"
                class="text-sm text-rose-600 hover:text-rose-800 underline">
                {{ __('Chưa có tài khoản? Đăng ký') }}
            </a>

            <x-primary-button>
                {{ __('Đăng nhập') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>