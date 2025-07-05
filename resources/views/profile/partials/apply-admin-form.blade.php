<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Apply for Admin') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('If you know the admin password, you can apply to become an administrator.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.apply-admin') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="admin_password" value="{{ __('Admin Password') }}" class="sr-only" />

            <x-text-input
                id="admin_password"
                name="admin_password"
                type="password"
                class="mt-1 block w-3/4"
                placeholder="{{ __('Admin Password') }}"
            />

            <x-input-error :messages="$errors->get('admin_password')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Apply for Admin') }}</x-primary-button>

            @if (session('status') === 'admin-applied')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Applied.') }}</p>
            @endif
        </div>
    </form>
</section>
