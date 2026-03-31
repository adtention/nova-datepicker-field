<?php

namespace Adtention\DatepickerField\Filters;

use Laravel\Nova\Fields\Filters\DateFilter;

class DatepickerFilter extends DateFilter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'datepicker-field';
}
