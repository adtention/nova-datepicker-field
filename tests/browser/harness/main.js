/**
 * Mounts the package's FormField or FilterField without Nova, so Playwright can
 * drive them with a real mouse and keyboard. Pick the mount with `?mount=`:
 *
 * - `field` (default): a single date field, locale `da`.
 * - `multiple`: the same field with `multiple: true`.
 * - `filter`: the From/To date filter.
 *
 * The component sits between two tall spacers, so a test can scroll it to any
 * height in the viewport. The value Nova would submit (or the filter's last
 * `change` value) is shown in `[data-testid="value"]`.
 */
import { createApp, h } from 'vue'
import FormField from '../../../resources/js/components/FormField.vue'
import FilterField from '../../../resources/js/components/FilterField.vue'
import { reportFieldValue, submittedValues } from './submittedValues'
import '../../../resources/css/field.css'

const mount = new URLSearchParams(window.location.search).get('mount') ?? 'field'

const filter = {
  name: 'Dates',
  uniqueKey: 'dates',
  currentValue: [null, null],
  field: { locale: 'da' },
}

window.Nova = {
  $on() {},
  $off() {},
}

const DefaultField = {
  setup(props, { slots }) {
    return () => h('div', { class: 'default-field' }, slots.field?.())
  },
}

const FilterContainer = {
  setup(props, { slots }) {
    return () => h('div', { class: 'filter-container' }, slots.filter?.())
  },
}

function renderComponent() {
  if (mount === 'filter') {
    return h(FilterField, {
      resourceName: 'bookings',
      filterKey: 'dates',
      onChange: ({ value }) => reportFieldValue('dates', JSON.stringify(value)),
    })
  }

  return h(FormField, {
    resourceName: 'bookings',
    field: {
      attribute: 'date',
      uniqueKey: 'date',
      name: 'Date',
      locale: 'da',
      value: null,
      multiple: mount === 'multiple',
    },
  })
}

function valueKey() {
  if (mount === 'filter') {
    return 'dates'
  }

  return 'date'
}

const app = createApp({
  render() {
    return h('main', [
      h('div', { class: 'spacer', 'data-testid': 'spacer-above' }),
      h('div', { class: 'component', 'data-testid': 'component' }, [renderComponent()]),
      h('output', { 'data-testid': 'value' }, submittedValues[valueKey()] ?? ''),
      h('div', { class: 'spacer' }),
    ])
  },
})

app.config.globalProperties.__ = (key) => key
app.config.globalProperties.$store = {
  getters: {
    'bookings/getFilter': () => filter,
  },
}
app.component('DefaultField', DefaultField)
app.component('FilterContainer', FilterContainer)
app.mount('#app')
