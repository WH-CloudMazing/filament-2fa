<?php

namespace Webbingbrasil\FilamentTwoFactor;

use Filament\Facades\Filament;
use Illuminate\Validation\ValidationException;

trait ConfirmsPasswords
{
    /**
     * Ensure that the user's password has been recently confirmed.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return void
     */
    protected function ensurePasswordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        $this->passwordIsConfirmed($maximumSecondsSinceConfirmation) ? null : abort(403);
    }

    /**
     * Determine if the user's password has been recently confirmed.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return bool
     */
    protected function passwordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        return (time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
