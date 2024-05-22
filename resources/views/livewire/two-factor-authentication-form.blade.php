<div>
    <h3 class="text-lg font-medium">
        @if ($this->enabled)
            @if (!$this->confirmed)
                {{ __('filament-2fa::two-factor.status.enabling') }}
            @else
                {{ __('filament-2fa::two-factor.status.enabled') }}
            @endif
        @else
            {{ __('filament-2fa::two-factor.status.disabled') }}
        @endif
    </h3>

    <div class="mt-2 max-w-xl text-sm">
        <p>
            {{ __('filament-2fa::two-factor.message.information') }}
        </p>
    </div>

    @if ($this->enabled)
        @if (!$this->confirmed)
            <div class="mt-2 max-w-xl text-sm">
                <p class="font-semibold">
                    {{ __('filament-2fa::two-factor.message.finish_enabling') }}
                </p>
            </div>

            <div class="mt-2">
                {!! $this->user->twoFactorQrCodeSvg() !!}
            </div>

            <div class="mt-2 max-w-xl text-sm">
                <p class="font-semibold">
                    {{ __('filament-2fa::two-factor.field.setup_key') }}: {{ decrypt($this->user->two_factor_secret) }}
                </p>
            </div>

            <div class="mt-2">
                {{ $this->form }}
            </div>
        @endif

        @if ($showingRecoveryCodes)
            <div class="mt-2 max-w-xl text-sm">
                <p class="font-semibold">
                    {{ __('filament-2fa::two-factor.message.store_recovery_codes') }}
                </p>
            </div>

            <div class="grid gap-1 max-w-xl mt-2 px-4 py-4 font-mono text-sm rounded-lg">
                @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                    <div>{{ $code }}</div>
                @endforeach
            </div>
        @endif
    @endif

    <div class="mt-2">
        @if (! $this->enabled)
            {{ $this->enableTwoFactorAuthentication }}
        @else
            @if ($showingRecoveryCodes)
                {{ $this->regenerateRecoveryCodes }}
            @elseif (!$this->confirmed)
                {{ $this->confirmTwoFactorAuthentication }}
            @else
                {{ $this->showRecoveryCodes }}
            @endif

            {{ $this->disableTwoFactorAuthentication }}

        @endif
    </div>

    <x-filament-actions::modals />
</div>
