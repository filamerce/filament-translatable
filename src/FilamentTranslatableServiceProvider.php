<?php

namespace Filamerce\FilamentTranslatable;

use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filamerce\FilamentTranslatable\Testing\TestsFilamentTranslateField;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTranslatableServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-translatable';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasConfigFile();

        $this->publishes([
            __DIR__ . '/../resources/flags' => public_path('vendor/filament-translatable/flags'),
        ], 'public');
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        // Testing
        Testable::mixin(new TestsFilamentTranslateField);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'filamerce/filament-translatable';
    }

    /**
     * @return Asset[]
     */
    protected function getAssets(): array
    {
        return [
            Css::make('filament-translatable-styles', __DIR__ . '/../resources/dist/filament-translatable.css'),
        ];
    }
}
