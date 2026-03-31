<template>
  <FilterContainer>
    <template #filter>
      <label class="block">
        <span class="uppercase text-xs font-bold tracking-wide">
          {{ `${filter.name} - ${__('From')}` }}
        </span>

        <VueDatePicker
          v-model="startValue"
          class="w-full mt-1 nova-datepicker-field"
          :teleport="true"
          :locale="dateFnsLocale"
          :time-config="timeConfiguration"
          :auto-apply="true"
          :text-input="textInputConfiguration"
          :formats="formats"
          :placeholder="__('Start')"
          :input-attrs="{ dusk: `${filter.uniqueKey}-range-start` }"
        />
      </label>

      <label class="block mt-2">
        <span class="uppercase text-xs font-bold tracking-wide">
          {{ `${filter.name} - ${__('To')}` }}
        </span>

        <VueDatePicker
          v-model="endValue"
          class="w-full mt-1 nova-datepicker-field"
          :teleport="true"
          :locale="dateFnsLocale"
          :time-config="timeConfiguration"
          :auto-apply="true"
          :text-input="textInputConfiguration"
          :formats="formats"
          :placeholder="__('End')"
          :input-attrs="{ dusk: `${filter.uniqueKey}-range-end` }"
        />
      </label>
    </template>
  </FilterContainer>
</template>

<script>
import debounce from 'lodash/debounce'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import { resolveDateFnsLocale } from '../dateFnsLocale'

export default {
  components: {
    VueDatePicker,
  },

  emits: ['change'],

  props: {
    resourceName: {
      type: String,
      required: true,
    },
    filterKey: {
      type: String,
      required: true,
    },
    lens: {
      type: String,
      default: '',
    },
  },

  data: () => ({
    startValue: null,
    endValue: null,
    debouncedEventEmitter: null,
    timeConfiguration: {
      enableTimePicker: false,
    },
    textInputConfiguration: {
      enterSubmit: true,
      tabSubmit: true,
      openMenu: 'open',
      format: 'yyyy-MM-dd',
      selectOnFocus: true,
      applyOnBlur: true,
    },
    formats: {
      input: 'yyyy-MM-dd',
    },
  }),

  created() {
    this.debouncedEventEmitter = debounce(() => this.emitFilterChange(), 500)
    this.setCurrentFilterValue()
  },

  mounted() {
    Nova.$on('filter-reset', this.handleFilterReset)
    Nova.$on('clear-filter-values', this.handleFilterReset)
  },

  beforeUnmount() {
    Nova.$off('filter-reset', this.handleFilterReset)
    Nova.$off('clear-filter-values', this.handleFilterReset)
  },

  watch: {
    startValue() {
      this.debouncedEventEmitter()
    },

    endValue() {
      this.debouncedEventEmitter()
    },
  },

  methods: {
    setCurrentFilterValue() {
      const [startValue, endValue] = this.filter.currentValue || [null, null]

      this.startValue = this.parseDateValue(startValue)
      this.endValue = this.parseDateValue(endValue)
    },

    emitFilterChange() {
      this.$emit('change', {
        filterClass: this.filterKey,
        value: [
          this.normalizeDateForFilter(this.startValue),
          this.normalizeDateForFilter(this.endValue),
        ],
      })
    },

    handleFilterReset() {
      this.startValue = null
      this.endValue = null

      this.setCurrentFilterValue()
    },

    parseDateValue(value) {
      if (value === null || value === undefined || value === '') {
        return null
      }

      if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value
      }

      if (typeof value === 'string') {
        const dateOnlyMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})$/)

        if (dateOnlyMatch === null) {
          return null
        }

        const parsedDate = new Date(
          Number(dateOnlyMatch[1]),
          Number(dateOnlyMatch[2]) - 1,
          Number(dateOnlyMatch[3]),
        )

        if (
          Number.isNaN(parsedDate.getTime())
          || parsedDate.getFullYear() !== Number(dateOnlyMatch[1])
          || parsedDate.getMonth() !== Number(dateOnlyMatch[2]) - 1
          || parsedDate.getDate() !== Number(dateOnlyMatch[3])
        ) {
          return null
        }

        return parsedDate
      }

      return null
    },

    normalizeDateForFilter(value) {
      const parsedDate = this.parseDateValue(value)

      if (parsedDate === null) {
        return null
      }

      const year = String(parsedDate.getFullYear())
      const month = String(parsedDate.getMonth() + 1).padStart(2, '0')
      const day = String(parsedDate.getDate()).padStart(2, '0')

      return `${year}-${month}-${day}`
    },
  },

  computed: {
    filter() {
      return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey)
    },

    field() {
      return this.filter.field
    },

    dateFnsLocale() {
      return resolveDateFnsLocale(this.field?.locale)
    },
  },
}
</script>
