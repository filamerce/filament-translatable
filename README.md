# Filament Translatable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/filamerce/filament-translatable.svg?style=flat-square)](https://packagist.org/packages/filamerce/filament-translatable)
[![Total Downloads](https://img.shields.io/packagist/dt/filamerce/filament-translatable.svg?style=flat-square)](https://packagist.org/packages/filamerce/filament-translatable)


Filament Translatable is a bunch of components that helps managing translations.

## Installation

| Filament Version | Filament Translate Field Version |
|------------------|---------------------------|
| v4.x             | v2.x


You can install the package via composer:

```bash
composer require filamerce/filament-translatable
```

Publish the assets:

```bash
php artisan filament:assets
```


## Configuration

- Define translatable fields in your model using a translatable package (e.g., "spatie/laravel-translatable" or "dimsav/laravel-translatable").
- Configure translatable fields in the model's *$translatable* property.

## Setup

```php
use Filamerce\FilamentTranslatable\FilamentTranslateFieldPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(FilamentTranslateFieldPlugin::make());
}
```
  
### Setting translatable locales
 
To set up the locales that can be used to translate content, you can pass an array of locales to the `locales()` plugin method:
  
```php
FilamentTranslateFieldPlugin::make()
     ->locales(['en', 'pl', 'fr']),
```

### Setting default locale

You can set default locale using `defaultLocale()` method:

```php
FilamentTranslateFieldPlugin::make()
     ->defaultLocale('pl'),
```

### Setting locale labels

You can set locale labels `getLocaleLabelUsing()` method:

```php
FilamentTranslateFieldPlugin::make()
    ->getLocaleLabelUsing(fn ($locale) => match ($locale) {
        'pl' => __('Polish'),
        'en' => __('English')
    })
```

### Enable or disable flags in locale labels

You can enable or disable flags in locale labels using (disabled by default):

```php
FilamentTranslateFieldPlugin::make()
    ->useFlagsInLocaleLabels(true)
```

### Setting flag width

You can set flag width using:

```php
FilamentTranslateFieldPlugin::make()
    ->flagWidth('24px')
```

### Enable or disable names in locale labels

You can enable or disable locale names in locale labels using (enabled by default):

```php
FilamentTranslateFieldPlugin::make()
    ->useNamesInLocaleLabels(false)
```

Otherwise, config `app.locale` will be taken.

It has effect in couple methods that can be used on fields.

## Usage

### `translatable()` macro

By using the `translatable()` macro, you can quickly configure single [form field](https://filamentphp.com/docs/4.x/forms/fields/getting-started) to support multiple languages and provide translations for each locale.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->translatable()
```

#### Marking field as required for specified locale

By this way "name" field only in "en" language will be required.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->requiredLocale('en')
    ->translatable()
```

#### Marking field as required for default locale

By this way "name" field only in default language will be required.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->requiredDefaultLocale()
    ->translatable()
```

#### Decorating language specified fields

You can fully customize language specified fields using `decorateTranslationField()` method.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('price')
    ->decorateTranslationField('pl', fn (TextInput $field) => $field->suffix('PLN'))
    ->decorateTranslationField('en', fn (TextInput $field) => $field->prefix('USD')),
    ->translatable()
```

#### Customizing `Translations` component

After using `translatable()` method, the context of field is switched to `Translations` component, so you can use any of method that belongs to component.

```php
use Filament\Forms\Components\TextInput;

TextInput::make('price')
    ->requiredDefaultLocale()
    ->translatable() // Here context is switched from TextInput to Translations component
    ->vertical()
    ->flagWidth('48px')
```

> [!CAUTION]
> Take care to set field specific methods like `required()`, `requiredDefaultLocale()` **before** calling `translatable()` method.


### `Translations` component

By using the `Translations` component, you can easily configure your [form fields](https://filamentphp.com/docs/4.x/forms/fields/getting-started) to support multiple languages and provide translations for each locale.

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

Translations::make('translations') // name is required to properly handle actions
    ->schema([
        TextInput::make('name')
    ])
```

> [!NOTE]  
> Using `translatable()` method within `Translations` component is not needed.

> [!IMPORTANT]  
> Take care to set diffrent names for each `Translations` component in multiple usage.

#### Setting the translatable locales for a particular fields

By default, the translatable locales can be set globally for all translate form component in the plugin configuration. Alternatively, you can customize the translatable locales for a particular resource by overriding the `locales()` method in `Translate` class:

```php
Translations::make('translations')
    ->locales(['en', 'es'])
```


#### Setting the translatable label for a particular field

You have the flexibility to customize the translate label for each field in each locale. You can use the `fieldTranslatableLabel()` method to provide custom labels based on the field instance and the current locale.

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

 Translations::make()
    ->schema([
        // Fields
    ])
    ->fieldTranslatableLabel(fn ($field, $locale) => __($field->getName(), locale: $locale))
```

#### Adding prefix/suffix locale label to the field

If you simply want to add a prefix or suffix locale label to the form field, you can use the `prefixLocaleLabel()` or `suffixLocaleLabel()` method. This makes it easier for users to identify the language associated with each field.

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

Translations::make('translations')
    ->schema([
        // Fields
    ])
    ->prefixLocaleLabel()
    ->suffixLocaleLabel()
```

> `prefixLocaleLabel:
> 
> ![filament-translate-field-3](https://github.com/webard/filament-translate-field/assets/68525320/0203e682-f324-4957-8680-4cffccab300c)

> `suffixLocaleLabel`:
> 
> ![filament-translate-field-4](https://github.com/webard/filament-translate-field/assets/68525320/7f4403e9-c857-4ebf-b022-8fed12094426)


#### Setting the locale display name

By default, the prefix/suffix locale display name is generated by locale code and enclosed in parentheses, "()". You may customize this using the `preformLocaleLabelUsing()` method:

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

Translations::make('translations')
    ->preformLocaleLabelUsing(fn (string $locale, string $label) => "[{$label}]");
```

#### Injecting the current form field

Additionally, if you need to access the current form field instance, you can inject the `$field` parameter into the callback functions. This allows you to perform specific actions or conditions based on the field being processed.

```php
use Filament\Forms\Components\Component;
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

Translations::make('translations')
    // ...
    ->prefixLocaleLabel(function(Component $field) {
        // need return boolean value
        return $field->getName() == 'title';
    })
    ->suffixLocaleLabel(function(Component $field) {
        // need return boolean value
        return $field->getName() == 'title';
    })

```

![filament-translate-field-5](https://github.com/webard/filament-translate-field/assets/68525320/a88fcb69-a63d-43a6-857b-5323df877134)



#### Adding action 

You may add actions before each container of children components using the `actions()` method:

```php

use Filament\Forms\Components\Actions\Action;
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

Translations::make('translations')
    ->actions([
        Action::make('fillDumpTitle')
    ])
```
> *If have multiple `Translate` components and have action in each component, please add id to `Translate` component by `id()` method*


#### Injecting the locale on current child container

If you wish to access the locale that have been passed to the action, define an `$arguments` parameter and get the value of `locale` from `$arguments`:

```php

use Filament\Forms\Components\Actions\Action;
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

Translations::make()
    ->actions([
        Action::make('fillDumpTitle')
            ->action(function (array $arguments) {
                $locale = $arguments['locale'];
                // ...
            })
    ])
```


#### Injecting the locale to form field

If you wish to access the current locale instance for the field, define a `$locale` parameter:

```php

use Filament\Forms\Components\TextInput;
use Filamerce\FilamentTranslatable\Forms\Component\Translations;

Translations::make()
    ->schema(fn (string $locale) => [TextInput::make('title')->required($locale == 'en')])
```

#### Removing the styled container
By default, translate component and their content are wrapped in a container styled as a card. You may remove the styled container using `contained()`:

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;
 
Translations::make()
    ->contained(false)
```

#### Vertical tabs

You can display translations as a vertical tabs:

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;
 
Translations::make()
    ->vertical()
```

#### Changing plugin settings

You can customize plugin settings directly on component:

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;
 
Translations::make()
    ->useNamesInLocaleLabels(false)
    ->useFlagsInLocaleLabels(true)
    ->flagWidth('48px')
```

#### Exclude 
The `exclude` feature allows you to specify fields that you don't want to be included in the translation process. This can be useful for fields that contain dynamic content or that shouldn't be translated into other languages.

```php
use Filamerce\FilamentTranslatable\Forms\Component\Translations;
 
Translations::make('translations')
    ->schema([
        Forms\Components\TextInput::make('title'),
        Forms\Components\TextInput::make('description'),
    ])
    ->exclude(['description'])
```
Without exclude
```json
{
    "title": {
        "en": "Dump",
        "es": "Dump",
        "fr": "Dump"
    },
    "description": {
        "en": null,
        "es": null,
        "fr": null
    }
}
```
With Exclude
```json
{
    "title": {
        "en": "Dump",
        "es": "Dump",
        "fr": "Dump"
    },
    "description": null
}
```
## Publishing Views

To publish the views, use:

```bash
php artisan vendor:publish --provider="Filamerce\\FilamentTranslatable\\FilamentTranslatableProvider" --tag="filament-translate-field-views"
```
## Publishing Configuration file

To publish the configuration file, use:

```bash
php artisan vendor:publish --provider="Filamerce\\FilamentTranslatable\\FilamentTranslatableProvider" --tag="filament-translate-field-config"
```

## Testing

```bash
composer test
```

## Changelog

See the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

See [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email code@webard.me instead of using the issue tracker.

## Credits

- [Lipis](https://github.com/lipis/flag-icons) for icons
- [Solution Forest](https://github.com/solutionforest/filament-translate-field) for great inspiration
- [Outer Web](https://github.com/outer-web/filament-translatable-fields) for Macro idea
- [All Contributors](../../contributors)

## License

Filament Tree is open-sourced software licensed under the [MIT license](LICENSE.md).

