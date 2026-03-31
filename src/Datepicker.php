<?php

namespace Adtention\DatepickerField;

use Adtention\DatepickerField\Filters\DatepickerFilter;
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

        $this->locale((string) config('app.locale', 'en'));
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
        ]);
    }
}
