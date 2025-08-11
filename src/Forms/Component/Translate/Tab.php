<?php

namespace Filamerce\FilamentTranslatable\Forms\Component\Translate;

class Tab extends \Filament\Schemas\Components\Tabs\Tab
{
    protected string $view = 'filament-translatable::forms.components.translate-tab';

    protected ?string $locale = null;

    public function locale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getKey(bool $isAbsolute = true): ?string
    {
        return parent::getKey() ?? (count($this->getActions()) > 0 ? $this->getId() : null);
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
