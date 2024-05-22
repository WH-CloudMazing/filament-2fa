<?php

namespace Webbingbrasil\FilamentTwoFactor;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Webbingbrasil\FilamentTwoFactor\Pages\TwoFactor;

class FilamentTwoFactorPlugin implements Plugin
{
    public function getId(): string
    {
        return FilamentTwoFactorProvider::$name;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages(
                config('filament-2fa.enable_two_factor_page')
                    ? [TwoFactor::class]
                    : []
            );

        $panel->bootUsing(function (Panel $panel) {
            if (config('filament-2fa.enable_two_factor_page') && config('filament-2fa.show_two_factor_page_in_user_menu')) {
                $panel->userMenuItems([
                    MenuItem::make()
                        ->label(__('filament-2fa::two-factor.navigation_label'))
                        ->url(TwoFactor::getUrl())
                        ->icon('heroicon-s-lock-closed'),
                ]);
            }
        });
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
