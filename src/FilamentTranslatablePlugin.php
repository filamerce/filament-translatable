<?php

namespace Filamerce\FilamentTranslatable;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Field;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filamerce\FilamentTranslatable\Forms\Component\Translate;

class FilamentTranslatablePlugin implements Plugin
{
    use EvaluatesClosures;

    /**
     * @var array<string>
     */
    protected array $locales = [];

    protected ?string $defaultLocale = null;

    protected ?Closure $getLocaleLabelUsing = null;

    protected bool | Closure $flagsInLabels = false;

    protected bool | Closure $namesInLabels = true;

    protected string | Closure $flagWidth = '24px';

    public function getId(): string
    {
        return 'filament-translatable';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {

        Field::macro('requiredDefaultLocale', function (bool | Closure $condition = true) {
            $defaultLocale = FilamentTranslatablePlugin::get()->getDefaultLocale();
            $this->requiredLocale($defaultLocale, $condition);

            return $this;
        });

        Field::macro('requiredLocale', function (string $locale, bool | Closure $condition = true) {
            $this->translationFieldDecorators[$locale][] = function (Field $field) use ($condition) {
                $field->required($condition);

                return $field;
            };

            return $this;
        });

        Field::macro('decorateTranslationField', function (string $locale, ?Closure $decorator = null) {
            $this->translationFieldDecorators[$locale][] = $decorator;

            return $this;
        });

        Field::macro('translatable', function (bool $translatable = true, ?array $locales = null, ?Closure $translationFieldDecorator = null) {

            if (! $translatable) {
                return $this;
            }

            /**
             * @var Field $field
             * @var Field $this
             */
            $field = $this->getClone();

            $tabsField = Translate::make($field->getName() . '_translations')
                ->locales($locales)
                ->schema([
                    $field,
                ]);

            if ($translationFieldDecorator) {
                $tabsField = $translationFieldDecorator($tabsField);
            }

            return $tabsField;
        });
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * @param  array<string> | null  $locales
     */
    public function locales(?array $locales = null): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function useFlagsInLabels(bool $condition = true)
    {
        $this->flagsInLabels = $condition;

        return $this;
    }

    public function hasFlagsInLabels(): bool
    {
        return $this->flagsInLabels;
    }

    public function useNamesInLabels(bool $condition = true)
    {
        $this->namesInLabels = $condition;

        return $this;
    }

    public function hasNamesInLabels(): bool
    {
        return $this->namesInLabels;
    }

    public function getLocaleLabelUsing(?Closure $callback): static
    {
        $this->getLocaleLabelUsing = $callback;

        return $this;
    }

    public function flagWidth(string | Closure $width): static
    {
        $this->flagWidth = $width;

        return $this;
    }

    public function getFlagWidth(): string
    {
        return $this->evaluate($this->flagWidth);
    }

    /**
     * @return array<string>
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    public function defaultLocale(string | Closure $locale): static
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    public function getDefaultLocale(): string
    {
        return $this->evaluate($this->defaultLocale ?? config('app.locale', 'en'));
    }

    public function getLocaleLabel(string $locale, ?string $displayLocale = null): ?string
    {
        $displayLocale ??= app()->getLocale();

        $label = null;

        if ($callback = $this->getLocaleLabelUsing) {
            $label = $callback($locale, $displayLocale);
        }

        return $label ?? (locale_get_display_name($locale, $displayLocale) ?: null);
    }
}
