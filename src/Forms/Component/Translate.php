<?php

namespace Filamerce\FilamentTranslatable\Forms\Component;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Contracts\HasRenderHookScopes;
use Filament\Schemas\Schema;
use Filamerce\FilamentTranslatable\FilamentTranslatablePlugin;
use Filamerce\FilamentTranslatable\Forms\Component\Translate\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class Translate extends Tabs
{
    /**
     * @var view-string
     */
    protected string $view = 'filament-translatable::forms.components.translate';

    protected null | Closure | array | Collection $locales = null;

    protected null | Closure | array | Collection $exclude = [];

    protected null | Closure | array | Collection $localeLabels = null;

    protected Closure | bool $hasPrefixLocaleLabel = false;

    protected Closure | bool $hasSuffixLocaleLabel = false;

    protected ?Closure $fieldTranslatableLabel = null;

    protected ?Closure $preformLocaleLabelUsing = null;

    protected int | Closure $activeTab = 1;

    protected string | Closure | null $tabQueryStringKey = null;

    protected string | Closure | null $livewireProperty = null;

    protected bool | Closure $isVertical = false;

    protected string | Closure | null $flagWidth = null;

    protected bool | Closure | null $flagsInLabels = null;

    protected bool | Closure | null $namesInLabels = null;

    /**
     * @var array<string>
     */
    protected array $startRenderHooks = [];

    /**
     * @var array<string>
     */
    protected array $endRenderHooks = [];

    public static function make(string | Htmlable | Closure | null $label = null): static
    {
        $static = app(static::class, ['label' => $label]);
        $static->configure();

        return $static;
    }

    public function exclude(Closure | array | Collection $exclude): static
    {
        $this->exclude = $exclude;

        return $this;
    }

    /**
     * @param  Closure|array<string>|Collection<string>|null  $locales
     */
    public function locales(Closure | array | Collection | null $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function localeLabels(Closure | array | Collection $labels): static
    {
        $this->localeLabels = $labels;

        return $this;
    }

    public function prefixLocaleLabel(Closure | bool $condition = true): static
    {
        $this->hasPrefixLocaleLabel = $condition;

        return $this;
    }

    public function suffixLocaleLabel(Closure | bool $condition = true): static
    {
        $this->hasSuffixLocaleLabel = $condition;

        return $this;
    }

    public function fieldTranslatableLabel(?Closure $fieldTranslatableLabel = null): static
    {
        $this->fieldTranslatableLabel = $fieldTranslatableLabel;

        return $this;
    }

    public function preformLocaleLabelUsing(?Closure $preformLocaleLabelUsing = null): static
    {
        $this->preformLocaleLabelUsing = $preformLocaleLabelUsing;

        return $this;
    }

    public function actions(null | Closure | array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function persistTabInQueryString(string | Closure | null $key = 'tab'): static
    {
        $this->tabQueryStringKey = $key;

        return $this;
    }

    /**
     * @return array<string>|Collection<string>
     */
    public function getLocales(): array | Collection
    {
        return $this->evaluate($this->locales) ?? FilamentTranslatablePlugin::get()->getLocales();
    }

    /**
     * @return array<string>|Collection<string>
     */
    public function getLocaleLabels(): array | Collection
    {
        return $this->evaluate($this->localeLabels)
            ?? collect($this->getLocales())
                ->map(fn ($locale) => FilamentTranslatablePlugin::get()->getLocaleLabel($locale, $locale))
                ->all();
    }

    public function getLocaleLabel(string $locale): string | Htmlable
    {

        $labels = $this->evaluate($this->localeLabels, [
            'locale' => $locale,
        ]) ?? FilamentTranslatablePlugin::get()->getLocaleLabel($locale, $locale);

        $label = null;

        if ($this->hasFlagsInLabels()) {
            $label .= '<img src="' . \asset('vendor/filament-translatable/flags/' . $locale . '.svg') . '" style="width:' . $this->getFlagWidth() . '" alt="' . $locale . '" class="inline-block align-middle' . ($this->hasNamesInLabels() ? ' me-2' : '') . '" />';
        }

        if ($this->hasNamesInLabels()) {
            if ($labels && is_array($labels)) {
                $label .= data_get($labels, $locale);
            } elseif ($labels && is_string($labels)) {
                $label .= $labels;
            }
        }

        $label = new HtmlString('<div class="text-nowrap">' . $label ?? $locale . '</div>');

        return $label ?? $locale;
    }

    public function hasPrefixLocaleLabel(Component $component, string $locale): bool
    {
        return boolval($this->evaluate($this->hasPrefixLocaleLabel, [
            'field' => $component,
            'locale' => $locale,
        ]) ?? false);
    }

    public function hasSuffixLocaleLabel(Component $component, string $locale): bool
    {
        return boolval($this->evaluate($this->hasSuffixLocaleLabel, [
            'field' => $component,
            'locale' => $locale,
        ]) ?? false);
    }

    public function getFieldTranslatableLabel(Component $component, string $locale): ?string
    {
        return $this->evaluate($this->fieldTranslatableLabel, [
            'field' => $component,
            'locale' => $locale,
        ]);
    }

    /**
     * @return array<Component>
     */
    public function getChildComponentsByLocale(string $locale): array
    {
        /** @var array<Component> */
        return $this->evaluate($this->childComponents, [
            'locale' => $locale,
        ]) ?? [];
    }

    public function getActiveTab(): int
    {
        if ($this->isTabPersistedInQueryString()) {

            $queryStringTab = request()->query($this->getTabQueryStringKey());

            $tabs = collect($this->getChildSchemas())
                ->map(fn (Schema $schema) => collect($schema->getComponents())->first() ?? null)
                ->values();

            foreach ($tabs as $index => $tab) {

                if ($tab?->getId() !== $queryStringTab) {
                    continue;
                }

                return $index + 1;
            }
        }

        return $this->evaluate($this->activeTab, ['locales' => $this->getLocales()]);
    }

    public function getTabQueryStringKey(): ?string
    {
        return $this->evaluate($this->tabQueryStringKey);
    }

    public function isTabPersistedInQueryString(): bool
    {
        return filled($this->getTabQueryStringKey());
    }

    /**
     * @return array<Schema>
     */
    public function getChildSchemas(bool $withHidden = false): array
    {
        $containers = [];

        $locales = $this->getLocales();

        foreach ($locales as $locale) {
            $containers[$locale] = Schema::make($this->getLivewire())
                ->parentComponent($this)
                ->components([
                    Tab::make($locale)
                        ->registerActions($this->getActions())
                        ->label($this->getLocaleLabel($locale))
                        ->locale($locale)

                        ->schema(
                            collect($this->getChildComponentsByLocale($locale)['default'])
                                ->map(fn ($component) => $this->prepareTranslateLocaleComponent($component, $locale))
                                ->all()
                        ),
                ])
                ->getClone();
        }

        return $containers;
    }

    protected function prepareTranslateLocaleComponent(Component $component, string $locale): Component
    {
        $localeComponent = clone $component;

        if ($localeComponent instanceof Field || method_exists($localeComponent, 'getName')) {

            $localeComponentName = $localeComponent->getName();

            if (filled($localeComponentName) && is_string($localeComponentName) && ! in_array($localeComponentName, $this->exclude)) {

                $localeComponent->label($this->getFieldTranslatableLabel($component, $locale) ?? $component->getLabel());

                $localeLabel = $this->getLocaleLabel($locale);
                $performedLocaleLabel = $this->preformLocaleLabelUsing
                    ? $this->evaluate($this->preformLocaleLabelUsing, [
                        'locale' => $locale,
                        'label' => $localeLabel,
                    ])
                    : null;
                if (! $performedLocaleLabel) {
                    $performedLocaleLabel = "({$localeLabel})";
                }
                if ($this->hasPrefixLocaleLabel($component, $locale)) {
                    $localeComponent->label("{$performedLocaleLabel} {$localeComponent->getLabel()}");
                }
                if ($this->hasSuffixLocaleLabel($component, $locale)) {
                    $localeComponent->label("{$localeComponent->getLabel()} {$performedLocaleLabel}");
                }

                // Spatie transltable field format
                if (method_exists($localeComponent, 'name')) {
                    $localeComponent->name($localeComponentName . '.' . $locale);
                }
                if (method_exists($localeComponent, 'statePath')) {
                    $localeComponent->statePath($localeComponent->getName());
                    $localeComponent->flushCachedAbsoluteStatePath();
                }

                $decorator = $localeComponent->translationFieldDecorators ?? null;

                if (isset($decorator[$locale]) && is_array($decorator[$locale])) {
                    foreach ($decorator[$locale] as $callback) {
                        $localeComponent = $callback($localeComponent);
                    }
                }
            }

        } else {

            $childComponents = $localeComponent->getDefaultChildComponents();

            if ($childComponents) {


                $localeComponent->schema(
                    collect($childComponents)
                        ->map(fn ($childComponent) => $this->prepareTranslateLocaleComponent($childComponent, $locale))
                        ->all()
                );
            }
        }

        return $localeComponent;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        if ($parameterName == 'locales') {
            return [$this->getLocales()];
        }

        return parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName);
    }

    /**
     * @param  array<string>  $hooks
     */
    public function startRenderHooks(array $hooks): static
    {
        $this->startRenderHooks = $hooks;

        return $this;
    }

    /**
     * @param  array<string>  $hooks
     */
    public function endRenderHooks(array $hooks): static
    {
        $this->endRenderHooks = $hooks;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getStartRenderHooks(): array
    {
        return $this->startRenderHooks;
    }

    /**
     * @return array<string>
     */
    public function getEndRenderHooks(): array
    {
        return $this->endRenderHooks;
    }

    /**
     * @return array<string>
     */
    public function getRenderHookScopes(): array
    {
        $livewire = $this->getLivewire();

        if (! ($livewire instanceof HasRenderHookScopes)) {
            return [];
        }

        return $livewire->getRenderHookScopes();
    }

    public function livewireProperty(string | Closure | null $property): static
    {
        $this->livewireProperty = $property;

        return $this;
    }

    public function getLivewireProperty(): ?string
    {
        return $this->evaluate($this->livewireProperty);
    }

    public function vertical(bool | Closure $condition = true): static
    {
        $this->isVertical = $condition;

        return $this;
    }

    public function isVertical(): bool
    {
        return (bool) $this->evaluate($this->isVertical);
    }

     public function useFlagsInLabels(bool|Closure $condition = true)
    {
        $this->flagsInLabels = $condition;

        return $this;
    }

    public function hasFlagsInLabels(): bool
    {
        return $this->flagsInLabels !== null ? $this->evaluate($this->flagsInLabels) : FilamentTranslatablePlugin::get()->hasFlagsInLabels();
    }

    public function useNamesInLabels(bool $condition = true)
    {
        $this->namesInLabels = $condition;

        return $this;
    }

    public function hasNamesInLabels(): bool
    {
        return $this->namesInLabels !== null ? $this->evaluate($this->namesInLabels) : FilamentTranslatablePlugin::get()->hasNamesInLabels();
    }

    public function flagWidth(string | Closure $width): static
    {
        $this->flagWidth = $width;

        return $this;
    }

    public function getFlagWidth(): string
    {
        return $this->flagWidth !== null ? $this->evaluate($this->flagWidth) : FilamentTranslatablePlugin::get()->getFlagWidth();
    }
}
