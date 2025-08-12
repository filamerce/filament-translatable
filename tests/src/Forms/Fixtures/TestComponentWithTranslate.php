<?php

namespace Filamerce\FilamentTranslatable\Tests\Forms\Fixtures;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filamerce\FilamentTranslatable\Enums\TranslationMode;
use Filamerce\FilamentTranslatable\Forms\Component\Translations;
use Illuminate\Contracts\View\View;

class TestComponentWithTranslate extends Livewire
{
    public array $translateConfig = [];

    public $locales;

    public function form(Schema $form): Schema
    {
        $exclude = $this->translateConfig['exclude'] ?? [];

        return $form
            ->components([
                Translations::make('translations')
                    ->localeLabels([
                        'pl' => 'pl',
                        'fr' => 'fr',
                    ])
                    ->displayFlagsInLocaleLabels(false)
                    ->displayNamesInLocaleLabels(true)
                    ->locales($this->locales)
                    ->defaultLocale('en')
                    ->translationMode(TranslationMode::Spatie)
                    ->schema([
                        TextInput::make('title')->requiredDefaultLocale(),
                        Textarea::make('content'),
                    ])

                    ->exclude($exclude),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('forms.fixtures.form');
    }
}
