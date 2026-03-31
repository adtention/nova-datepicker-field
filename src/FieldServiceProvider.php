<?php

namespace Adtention\DatepickerField;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::serving(function (): void {
            Nova::mix('datepicker-field', __DIR__.'/../dist/mix-manifest.json');
        });
    }
}
