<!DOCTYPE html>
<html
    class="dark"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <header class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <a
                    class="font-semibold text-zinc-900 dark:text-zinc-100"
                    href="{{ route('home') }}"
                >
                    {{ config('app.name') }}
                </a>

                <nav class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                    <a
                        class="{{ request()->routeIs('products.*') ? 'font-semibold' : '' }}"
                        href="{{ route('products.index') }}"
                    >{{ __('Products') }}</a>
                    <a
                        class="{{ request()->routeIs('cart.*') ? 'font-semibold' : '' }}"
                        href="{{ route('cart.index') }}"
                    >
                        {{ __('Cart') }}
                        <livewire:store.cart-badge />
                    </a>
                    @auth
                        <a
                            class="{{ request()->routeIs('wishlist.*') ? 'font-semibold' : '' }}"
                            href="{{ route('wishlist.index') }}"
                        >{{ __('Wishlist') }}</a>
                        <a
                            class="{{ request()->routeIs('checkout.*') ? 'font-semibold' : '' }}"
                            href="{{ route('checkout.index') }}"
                        >{{ __('Checkout') }}</a>
                    @endauth
                </nav>
            </div>

            <div class="flex items-center gap-3 text-sm">
                @auth
                    <a
                        class="text-zinc-700 dark:text-zinc-300"
                        href="{{ route('dashboard') }}"
                    >{{ __('Dashboard') }}</a>
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf
                        <button
                            class="rounded-md bg-zinc-900 px-3 py-1.5 text-white dark:bg-zinc-100 dark:text-zinc-900"
                            type="submit"
                        >{{ __('Log out') }}</button>
                    </form>
                @else
                    <a
                        class="text-zinc-700 dark:text-zinc-300"
                        href="{{ route('login') }}"
                    >{{ __('Login') }}</a>
                    <a
                        class="rounded-md bg-zinc-900 px-3 py-1.5 text-white dark:bg-zinc-100 dark:text-zinc-900"
                        href="{{ route('register') }}"
                    >{{ __('Register') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    @fluxScripts
</body>

</html>
