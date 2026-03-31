# Datepicker Field for Laravel Nova

A Laravel Nova date field built as a thin wrapper around [@vuepic/vue-datepicker](https://vue3datepicker.com/).

| Compatibility | Version |
| ---- | ----- |
| Laravel Nova | `^5.0` |
| PHP | `^8.4` |

## Installation

```bash
composer require adtention/nova-datepicker-field
```

## Usage

Use `Datepicker` like any other Nova field in your resource:

```php
<?php

use Adtention\DatepickerField\Datepicker;
use Laravel\Nova\Http\Requests\NovaRequest;

public function fields(NovaRequest $request): array
{
    return [
        Datepicker::make('Start Date', 'start_date')
            ->rules(['nullable', 'date']),

        Datepicker::make('End Date', 'end_date')
            ->rules(['required', 'date'])
            ->readonly(fn (NovaRequest $request): bool => $request->isUpdateOrUpdateAttachedRequest()),
    ];
}
```

The field is a Nova-friendly wrapper around `@vuepic/vue-datepicker` and is designed for date-only values.

`Datepicker` extends `Laravel\Nova\Fields\Date`, so core Nova `Date` field features are supported.

### More examples

Date-range validation:

```php
<?php

use Adtention\DatepickerField\Datepicker;
use Laravel\Nova\Http\Requests\NovaRequest;

public function fields(NovaRequest $request): array
{
    return [
        Datepicker::make('Start Date')
            ->rules(['required', 'date']),

        Datepicker::make('End Date')
            ->rules(['required', 'date', 'after_or_equal:start_date']),
    ];
}
```

Using regular Nova field APIs on top of the datepicker:

```php
Datepicker::make('Optional Date')
    ->rules(['nullable', 'date'])
    ->help('Leave empty if this date is not applicable.')
    ->readonly(fn (NovaRequest $request): bool => $request->isUpdateOrUpdateAttachedRequest());
```

Using inherited Nova `Date` features:

```php
Datepicker::make('Travel Date')
    ->min(now()->toDateString())
    ->max(now()->addYear()->toDateString())
    ->step(1)
    ->locale('da');
```

### Supported scope

This package is intentionally lightweight for public package use: it wraps `@vuepic/vue-datepicker` for Nova, but does **not** expose the full upstream prop/slot API.

- Input is normalized to `YYYY-MM-DD` before submission.
- The UI automatically follows Nova light/dark mode styling.
- Since the field extends Nova `Date`, inherited Date-field behavior (for example `min`, `max`, and `step`) remains available.
- Advanced vue-datepicker features (for example custom slots, full range/time presets, and all component props) are not guaranteed to be configurable via PHP at this time.

If you need additional functionality, pull requests are welcome as long as the API stays Nova-friendly.

## Development

Build for local environment:

```bash
git clone https://github.com/adtention/nova-datepicker-field.git
cd nova-datepicker-field
composer install
npm install
npm run dev
```

Build for release:

```bash
cd nova-datepicker-field
npm run prod
```

## License

MIT

## Credits

Developed by [Adtention A/S](https://adtention.dk)

![Adtention Logo](https://adtention.dk/wp-content/uploads/2020/09/github.png "We are Adtention; A creative digital agency")
