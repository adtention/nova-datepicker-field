<?php

namespace Adtention\DatepickerField\Tests\Feature;

use Adtention\DatepickerField\Datepicker;
use Adtention\DatepickerField\Filters\DatepickerFilter;
use Adtention\DatepickerField\Tests\TestCase;
use Laravel\Nova\Contracts\FilterableField;
use Laravel\Nova\Http\Requests\NovaRequest;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;

class DatepickerTest extends TestCase
{
    #[Test]
    public function it_defaults_locale_meta_to_the_application_locale(): void
    {
        config()->set('app.locale', 'da');

        $field = Datepicker::make('Start', 'date_start');

        $this->assertSame('da', $field->meta()['locale']);
        $this->assertSame('da', $field->jsonSerialize()['locale']);
    }

    #[Test]
    public function it_allows_overriding_locale_via_field_api(): void
    {
        config()->set('app.locale', 'da');

        $field = Datepicker::make('Start', 'date_start')->locale('de');

        $this->assertSame('de', $field->meta()['locale']);
        $this->assertSame('de', $field->jsonSerialize()['locale']);
    }

    #[Test]
    public function it_preserves_empty_locale_overrides_without_fallback(): void
    {
        config()->set('app.locale', 'da');

        $field = Datepicker::make('Start', 'date_start')->locale('');

        $this->assertSame('', $field->meta()['locale']);
        $this->assertSame('', $field->jsonSerialize()['locale']);
    }

    #[Test]
    public function it_keeps_using_the_custom_datepicker_component(): void
    {
        $field = Datepicker::make('Start', 'date_start');

        $this->assertSame('datepicker-field', $field->jsonSerialize()['component']);
    }

    #[Test]
    public function it_implements_the_filterable_field_contract(): void
    {
        $field = Datepicker::make('Start', 'date_start');

        $this->assertInstanceOf(FilterableField::class, $field);
        $this->assertTrue(method_exists($field, 'filterable'));
    }

    #[Test]
    public function it_exposes_dependent_field_methods_from_native_date_field(): void
    {
        $field = Datepicker::make('Start', 'date_start');

        $this->assertTrue(method_exists($field, 'dependsOn'));
        $this->assertTrue(method_exists($field, 'dependsOnCreating'));
        $this->assertTrue(method_exists($field, 'dependsOnUpdating'));
    }

    #[Test]
    public function it_uses_a_custom_filter_component_for_filterable_datepicker_fields(): void
    {
        config()->set('app.locale', 'da');

        $field = Datepicker::make('Start', 'date_start')->filterable();

        $makeFilter = new ReflectionMethod($field, 'makeFilter');
        $makeFilter->setAccessible(true);

        $filter = $makeFilter->invoke($field, app(NovaRequest::class));

        $this->assertInstanceOf(DatepickerFilter::class, $filter);
        $this->assertSame('filter-datepicker-field', $filter->jsonSerialize()['component']);
        $this->assertSame('da', $filter->jsonSerialize()['field']['locale']);
    }
}
