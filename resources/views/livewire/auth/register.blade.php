<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Create an account')"
        :description="__('Enter your details below to create your account')"
    />

    <!-- Session Status -->
    <x-auth-session-status
        class="text-center"
        :status="session('status')"
    />

    <form
        class="flex flex-col gap-6"
        method="POST"
        wire:submit="register"
    >
        <!-- Name -->
        <flux:input
            type="text"
            wire:model.blur="name"
            :label="__('Name')"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Email Address -->
        <flux:input
            type="email"
            wire:model.blur="email"
            :label="__('Email address')"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <flux:input
            type="password"
            wire:model="password"
            :label="__('Password')"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            type="password"
            wire:model.blur="password_confirmation"
            :label="__('Confirm password')"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            viewable
        />

        <flux:text
            class="text-sm"
            variant="subtle"
        >
            {{ __('Use at least 8 characters with a mix of letters and numbers.') }}
        </flux:text>

        <div class="flex items-center justify-end">
            <flux:button
                class="w-full"
                data-test="register-button"
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="register"
            >
                <span
                    wire:loading.remove
                    wire:target="register"
                >{{ __('Create account') }}</span>
                <span
                    class="inline-flex items-center gap-2"
                    wire:loading
                    wire:target="register"
                >
                    <flux:icon.loading class="size-4" />
                    {{ __('Creating account...') }}
                </span>
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 rtl:space-x-reverse dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link
            :href="route('login')"
            wire:navigate
        >{{ __('Log in') }}</flux:link>
    </div>
</div>
