@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <x-filament::grid :default="2" class="items-center">
            <div class="flex items-center gap-x-3">
                <x-filament-panels::avatar.user size="lg" :user="$user" />

                <div class="flex-1">
                <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ filament()->getUserName($user) }}
                </p>
            </div>

            <form action="{{ filament()->getLogoutUrl() }}" method="post" class="my-auto">
                @csrf

                <x-filament::button
                    color="gray"
                    icon="heroicon-m-arrow-left-on-rectangle"
                    icon-alias="panels::widgets.account.logout-button"
                    labeled-from="sm"
                    tag="button"
                    type="submit"
                >
                    {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                </x-filament::button>
            </form>
        </div>

            @if($user->hasRole(\App\Enums\RolesEnum::Vendor->value) && !$user->stripe_account_active)
                <div class="flex justify-end">
                    <form action="{{ route('stripe.connect') }}" method="post">
                        @csrf
                        <x-filament::button type="submit" color="primary">
                            Connect to Stripe
                        </x-filament::button>
                    </form>
                </div>
            @endif
        </x-filament::grid>
    </x-filament::section>
</x-filament-widgets::widget>
