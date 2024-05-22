<?php

namespace Webbingbrasil\FilamentTwoFactor\Http\Livewire;

use Closure;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Webbingbrasil\FilamentTwoFactor\ConfirmsPasswords;
use Webbingbrasil\FilamentTwoFactor\FilamentTwoFactor;

class TwoFactorAuthenticationForm extends Component implements Forms\Contracts\HasForms, Actions\Contracts\HasActions
{
    use Actions\Concerns\InteractsWithActions;
    use Forms\Concerns\InteractsWithForms;
    use ConfirmsPasswords;

    /**
     * Indicates if two factor authentication recovery codes are being displayed.
     *
     * @var bool
     */
    public $showingRecoveryCodes = false;

    /**
     * The two factor authentication provider.
     *
     * @var \Webbingbrasil\FilamentTwoFactor\FilamentTwoFactor
     */
    protected $twoFactor;

    /**
     * The OTP code for confirming two factor authentication.
     *
     * @var string|null
     */
    public $code;

    public function boot()
    {
        $this->twoFactor = app(FilamentTwoFactor::class);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('code')
                ->label(__('filament-2fa::two-factor.field.code'))
                ->rules('nullable|string'),
        ];
    }

    protected function passwordConfirmableAction(string $name, Closure $callback): Actions\Action
    {
        $action = Actions\Action::make($name)->button();

        if (! $this->passwordIsConfirmed()) {
            $callback = function () use ($callback) {
                session(['auth.password_confirmed_at' => time()]);

                $callback();
            };

            $action->requiresConfirmation(! $this->passwordIsConfirmed())
                ->modalHeading(__('filament-2fa::two-factor.message.confirm_password'))
                ->modalDescription(__('filament-2fa::two-factor.message.confirm_password_instructions'))
                ->modalSubmitActionLabel(__('filament-2fa::two-factor.button.confirm'))
                ->form([
                    Forms\Components\TextInput::make('password')
                        ->label(__('filament-2fa::two-factor.field.password'))
                        ->required()
                        ->password()
                        ->rule(function () {
                            return function ($attribute, $value, $fail) {
                                $guard = Filament::auth();

                                if (! $guard->validate([
                                    'email' => $guard->user()->email,
                                    'password' => $value,
                                ])) {
                                    $fail(__('filament-2fa::two-factor.message.password_not_match'));
                                }
                            };
                        })
                ]);
        }

        return $action->action(fn () => $callback());
    }

    /**
     * Enable two factor authentication for the user.
     */
    public function enableTwoFactorAuthentication(): Actions\Action
    {
        return $this->passwordConfirmableAction('enableTwoFactorAuthentication', function () {
            $this->ensurePasswordIsConfirmed();

            $this->user->forceFill([
                'two_factor_secret' => encrypt($this->twoFactor->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return $this->twoFactor->generateRecoveryCode();
                })->all())),
            ])->save();
        })
            ->label(__('filament-2fa::two-factor.button.enable'));
    }

    /**
     * Confirm two factor authentication for the user.
     */
    public function confirmTwoFactorAuthentication(): Actions\Action
    {
        return $this->passwordConfirmableAction('confirmTwoFactorAuthentication', function () {
            $this->ensurePasswordIsConfirmed();

            if (empty($this->user->two_factor_secret) ||
                empty($this->code) ||
                ! $this->twoFactor->verify(decrypt($this->user->two_factor_secret), $this->code)) {
                $this->addError('code', __('filament-2fa::two-factor.message.invalid_code'));

                return;
            }

            $this->user->forceFill([
                'two_factor_confirmed_at' => now(),
            ])->save();

            $this->showingRecoveryCodes = true;
        })
            ->color('primary')
            ->label(__('filament-2fa::two-factor.button.confirm'));
    }

    /**
     * Display the user's recovery codes.
     */
    public function showRecoveryCodes(): Actions\Action
    {
        return $this->passwordConfirmableAction('showRecoveryCodes', function () {
            $this->ensurePasswordIsConfirmed();
            $this->showingRecoveryCodes = true;
        })
            ->label(__('filament-2fa::two-factor.button.show_recovery_codes'));
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(): Actions\Action
    {
        return $this->passwordConfirmableAction('regenerateRecoveryCodes', function () {
            $this->ensurePasswordIsConfirmed();

            $this->user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return $this->twoFactor->generateRecoveryCode();
                })->all())),
            ])->save();

            $this->showingRecoveryCodes = true;
        })
            ->label(__('filament-2fa::two-factor.button.regenerate_recovery_code'));
    }

    /**
     * Disable two factor authentication for the user.
     */
    public function disableTwoFactorAuthentication(): Actions\Action
    {
        $twoFactorEnabled = $this->user->two_factor_secret;

        return $this->passwordConfirmableAction('disableTwoFactorAuthentication', function () {
            $this->ensurePasswordIsConfirmed();

            $this->user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            $this->showingRecoveryCodes = false;
        })
            ->color('danger')
            ->label($twoFactorEnabled ? __('filament-2fa::two-factor.button.disable') : __('filament-2fa::two-factor.button.cancel'));
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Filament::auth()->user();
    }

    /**
     * Determine if two factor authentication is enabled.
     *
     * @return bool
     */
    public function getEnabledProperty()
    {
        return ! empty($this->user->two_factor_secret);
    }

    /**
     * Determine if two factor authentication is enabled.
     *
     * @return bool
     */
    public function getConfirmedProperty()
    {
        return ! empty($this->user->two_factor_confirmed_at);
    }

    public function render()
    {
        return view('filament-2fa::livewire.two-factor-authentication-form');
    }
}
