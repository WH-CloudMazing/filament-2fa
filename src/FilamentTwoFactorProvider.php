<?php

namespace Webbingbrasil\FilamentTwoFactor;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webbingbrasil\FilamentTwoFactor\Http\Livewire\Auth;
use Webbingbrasil\FilamentTwoFactor\Http\Livewire\TwoFactorAuthenticationForm;

class FilamentTwoFactorProvider extends PackageServiceProvider
{
    public static string $name = 'filament-2fa';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('filament-2fa')
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('web')
            ->hasMigration('add_two_factor_columns_to_users_table');
    }

    public function packageBooted(): void
    {
//        Livewire::component(Auth\Login::getName(), Auth\Login::class);
//        Livewire::component(Auth\TwoFactorChallenge::getName(), Auth\TwoFactorChallenge::class);
        Livewire::component('filament-two-factor-form', TwoFactorAuthenticationForm::class);
    }
}
