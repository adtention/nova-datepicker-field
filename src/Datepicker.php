<?php

namespace Adtention\DatepickerField;

use Adtention\DatepickerField\Filters\DatepickerFilter;
use Carbon\CarbonImmutable;
use Throwable;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;
use Override;

class Datepicker extends Date
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'datepicker-field';

    /** {@inheritdoc} */
    public function __construct($name, mixed $attribute = null, ?callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->multiple(false);
        $this->locale((string) config('app.locale', 'en'));
    }

    /**
     * Enable or disable multiple date selection.
     *
     * @return $this
     */
    public function multiple(bool $multiple = true): static
    {
        return $this->withMeta([
            'multiple' => $multiple,
        ]);
    }

    /**
     * Set the locale used by the frontend datepicker.
     *
     * @return $this
     */
    public function locale(string $locale): static
    {
        return $this->withMeta([
            'locale' => $locale,
        ]);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Laravel\Nova\Support\Fluent  $model
     */
    #[Override]
    protected function fillAttributeFromRequest(NovaRequest $request, string $requestAttribute, object $model, string $attribute): void
    {
        if (! $request->exists($requestAttribute)) {
            return;
        }

        if (($this->meta()['multiple'] ?? false) !== true) {
            parent::fillAttributeFromRequest($request, $requestAttribute, $model, $attribute);

            return;
        }

        $requestValue = $request[$requestAttribute];

        if (is_array($requestValue)) {
            $decoded = $requestValue;
        } else {
            $decoded = json_decode((string) $requestValue, true);
        }

        if (! is_array($decoded)) {
            $this->fillModelWithData($model, [], $attribute);

            return;
        }

        $dates = Collection::make($decoded)
            ->filter(fn (mixed $value): bool => is_string($value))
            ->map(fn (string $value): string => trim($value))
            ->filter(fn (string $value): bool => $this->isValidDateString($value))
            ->unique()
            ->values()
            ->all();

        $this->fillModelWithData($model, $dates, $attribute);
    }

    private function isValidDateString(string $value): bool
    {
        try {
            $parsedDate = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (Throwable) {
            return false;
        }

        if ($parsedDate === false) {
            return false;
        }

        return $parsedDate->format('Y-m-d') === $value;
    }

    /** {@inheritDoc} */
    #[Override]
    protected function makeFilter(NovaRequest $request)
    {
        return DatepickerFilter::make($this);
    }

    /** {@inheritDoc} */
    #[Override]
    public function serializeForFilter(): array
    {
        return array_merge(parent::serializeForFilter(), [
            'locale' => (string) $this->meta()['locale'],
            'multiple' => (bool) ($this->meta()['multiple'] ?? false),
        ]);
    }
}
