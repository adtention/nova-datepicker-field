<?php

namespace Adtention\DatepickerField\Tests\Feature;

use Adtention\DatepickerField\Datepicker;
use Adtention\DatepickerField\Filters\DatepickerFilter;
use Adtention\DatepickerField\Tests\TestCase;
use Carbon\Carbon;
use Laravel\Nova\Contracts\FilterableField;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;

class DatepickerTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Component
    // -------------------------------------------------------------------------

    #[Test]
    public function it_keeps_using_the_custom_datepicker_component(): void
    {
        // Arrange
        $field = Datepicker::make('Start', 'date_start');

        // Act
        $serialized = $field->jsonSerialize();

        // Assert
        $this->assertSame('datepicker-field', $serialized['component']);
    }

    #[Test]
    public function it_overrides_the_parent_date_component(): void
    {
        // Arrange
        $parentField = Date::make('Start', 'date_start');
        $datepickerField = Datepicker::make('Start', 'date_start');

        // Act & Assert
        $this->assertSame('date-field', $parentField->component);
        $this->assertSame('datepicker-field', $datepickerField->component);
    }

    // -------------------------------------------------------------------------
    // Constructor & Attribute Inference
    // -------------------------------------------------------------------------

    #[Test]
    public function it_extends_the_nova_date_field(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start');

        // Assert
        $this->assertInstanceOf(Date::class, $field);
    }

    #[Test]
    public function it_infers_attribute_from_name_when_attribute_is_omitted(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start Date');

        // Assert
        $this->assertSame('start_date', $field->attribute);
    }

    #[Test]
    public function it_uses_an_explicit_attribute_when_given(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'custom_attribute');

        // Assert
        $this->assertSame('custom_attribute', $field->attribute);
    }

    // -------------------------------------------------------------------------
    // Locale
    // -------------------------------------------------------------------------

    #[Test]
    public function it_defaults_locale_meta_to_the_application_locale(): void
    {
        // Arrange
        config()->set('app.locale', 'da');

        // Act
        $field = Datepicker::make('Start', 'date_start');

        // Assert
        $this->assertSame('da', $field->meta()['locale']);
        $this->assertSame('da', $field->jsonSerialize()['locale']);
    }

    #[Test]
    public function it_allows_overriding_locale_via_field_api(): void
    {
        // Arrange
        config()->set('app.locale', 'da');

        // Act
        $field = Datepicker::make('Start', 'date_start')->locale('de');

        // Assert
        $this->assertSame('de', $field->meta()['locale']);
        $this->assertSame('de', $field->jsonSerialize()['locale']);
    }

    #[Test]
    public function it_preserves_empty_locale_overrides_without_fallback(): void
    {
        // Arrange
        config()->set('app.locale', 'da');

        // Act
        $field = Datepicker::make('Start', 'date_start')->locale('');

        // Assert
        $this->assertSame('', $field->meta()['locale']);
        $this->assertSame('', $field->jsonSerialize()['locale']);
    }

    #[Test]
    public function it_casts_null_locale_to_empty_string(): void
    {
        // Arrange
        config()->set('app.locale', null);

        // Act
        $field = Datepicker::make('Start', 'date_start');

        // Assert
        $this->assertSame('', $field->meta()['locale']);
    }

    #[Test]
    public function locale_method_returns_the_field_for_chaining(): void
    {
        // Arrange
        $field = Datepicker::make('Start', 'date_start');

        // Act
        $result = $field->locale('fr');

        // Assert
        $this->assertSame($field, $result);
    }

    #[Test]
    #[DataProvider('localeProvider')]
    public function it_accepts_various_locale_codes(string $locale): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start')->locale($locale);

        // Assert
        $this->assertSame($locale, $field->meta()['locale']);
    }

    public static function localeProvider(): array
    {
        return [
            'english' => ['en'],
            'danish' => ['da'],
            'german' => ['de'],
            'french' => ['fr'],
            'japanese' => ['ja'],
            'bcp47 style' => ['en-US'],
            'underscore style' => ['pt_BR'],
        ];
    }

    // -------------------------------------------------------------------------
    // Inherited Date Features (min, max, step)
    // -------------------------------------------------------------------------

    #[Test]
    public function it_supports_min_date_with_string(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start')->min('2025-01-01');

        // Assert
        $serialized = $field->jsonSerialize();
        $this->assertSame('2025-01-01', $serialized['min']);
    }

    #[Test]
    public function it_supports_min_date_with_carbon_instance(): void
    {
        // Arrange
        $this->freezeTime();
        $date = Carbon::parse('2025-06-15');

        // Act
        $field = Datepicker::make('Start', 'date_start')->min($date);

        // Assert
        $this->assertSame('2025-06-15', $field->jsonSerialize()['min']);
    }

    #[Test]
    public function it_supports_max_date_with_string(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start')->max('2026-12-31');

        // Assert
        $this->assertSame('2026-12-31', $field->jsonSerialize()['max']);
    }

    #[Test]
    public function it_supports_max_date_with_carbon_instance(): void
    {
        // Arrange
        $this->freezeTime();
        $date = Carbon::parse('2026-12-31');

        // Act
        $field = Datepicker::make('Start', 'date_start')->max($date);

        // Assert
        $this->assertSame('2026-12-31', $field->jsonSerialize()['max']);
    }

    #[Test]
    public function it_supports_step(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start')->step(7);

        // Assert
        $this->assertSame(7, $field->jsonSerialize()['step']);
    }

    #[Test]
    public function it_defaults_step_to_any(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start');

        // Assert
        $this->assertSame('any', $field->jsonSerialize()['step']);
    }

    #[Test]
    public function it_chains_min_max_step_and_locale_together(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Travel Date', 'travel_date')
            ->min('2025-01-01')
            ->max('2025-12-31')
            ->step(1)
            ->locale('da');

        // Assert
        $serialized = $field->jsonSerialize();
        $this->assertSame('2025-01-01', $serialized['min']);
        $this->assertSame('2025-12-31', $serialized['max']);
        $this->assertSame(1, $serialized['step']);
        $this->assertSame('da', $serialized['locale']);
        $this->assertSame('datepicker-field', $serialized['component']);
    }

    // -------------------------------------------------------------------------
    // Filterable & Filter
    // -------------------------------------------------------------------------

    #[Test]
    public function it_implements_the_filterable_field_contract(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start');

        // Assert
        $this->assertInstanceOf(FilterableField::class, $field);
        $this->assertTrue(method_exists($field, 'filterable'));
    }

    #[Test]
    public function it_uses_a_custom_filter_component_for_filterable_datepicker_fields(): void
    {
        // Arrange
        config()->set('app.locale', 'da');
        $field = Datepicker::make('Start', 'date_start')->filterable();

        $makeFilter = new ReflectionMethod($field, 'makeFilter');
        $makeFilter->setAccessible(true);

        // Act
        $filter = $makeFilter->invoke($field, app(NovaRequest::class));

        // Assert
        $this->assertInstanceOf(DatepickerFilter::class, $filter);
        $this->assertSame('filter-datepicker-field', $filter->jsonSerialize()['component']);
        $this->assertSame('da', $filter->jsonSerialize()['field']['locale']);
    }

    #[Test]
    public function it_includes_locale_in_serialize_for_filter(): void
    {
        // Arrange
        config()->set('app.locale', 'fr');
        $field = Datepicker::make('Start', 'date_start');

        // Act
        $serialized = $field->serializeForFilter();

        // Assert
        $this->assertArrayHasKey('locale', $serialized);
        $this->assertSame('fr', $serialized['locale']);
    }

    #[Test]
    public function it_includes_standard_filter_keys_in_serialize_for_filter(): void
    {
        // Arrange
        $field = Datepicker::make('Start', 'date_start');

        // Act
        $serialized = $field->serializeForFilter();

        // Assert
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('attribute', $serialized);
        $this->assertArrayHasKey('locale', $serialized);
    }

    #[Test]
    public function it_passes_overridden_locale_through_filter_serialization(): void
    {
        // Arrange
        config()->set('app.locale', 'da');
        $field = Datepicker::make('Start', 'date_start')->locale('ja');

        // Act
        $serialized = $field->serializeForFilter();

        // Assert
        $this->assertSame('ja', $serialized['locale']);
    }

    #[Test]
    public function filter_defaults_to_null_range(): void
    {
        // Arrange
        $field = Datepicker::make('Start', 'date_start')->filterable();
        $makeFilter = new ReflectionMethod($field, 'makeFilter');
        $makeFilter->setAccessible(true);

        // Act
        $filter = $makeFilter->invoke($field, app(NovaRequest::class));

        // Assert
        $this->assertSame([null, null], $filter->default());
    }

    // -------------------------------------------------------------------------
    // Dependent Fields
    // -------------------------------------------------------------------------

    #[Test]
    public function it_exposes_dependent_field_methods_from_native_date_field(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start', 'date_start');

        // Assert
        $this->assertTrue(method_exists($field, 'dependsOn'));
        $this->assertTrue(method_exists($field, 'dependsOnCreating'));
        $this->assertTrue(method_exists($field, 'dependsOnUpdating'));
    }

    // -------------------------------------------------------------------------
    // JSON Serialization
    // -------------------------------------------------------------------------

    #[Test]
    public function it_serializes_name_and_attribute_correctly(): void
    {
        // Arrange & Act
        $field = Datepicker::make('Start Date', 'start_date');
        $serialized = $field->jsonSerialize();

        // Assert
        $this->assertSame('Start Date', $serialized['name']);
        $this->assertSame('start_date', $serialized['attribute']);
    }

    #[Test]
    public function it_does_not_leak_parent_date_field_component_after_serialization(): void
    {
        // Arrange
        $field = Datepicker::make('Start', 'date_start')
            ->min('2025-01-01')
            ->max('2025-12-31')
            ->locale('da');

        // Act
        $serialized = $field->jsonSerialize();

        // Assert
        $this->assertSame('datepicker-field', $serialized['component']);
        $this->assertNotSame('date-field', $serialized['component']);
    }

    #[Test]
    public function it_includes_locale_in_json_serialization(): void
    {
        // Arrange
        config()->set('app.locale', 'de');

        // Act
        $field = Datepicker::make('Date', 'date');
        $serialized = $field->jsonSerialize();

        // Assert
        $this->assertArrayHasKey('locale', $serialized);
        $this->assertSame('de', $serialized['locale']);
    }
}
