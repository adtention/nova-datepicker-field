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
import {
  normalizeDateFilterValue,
  parseFlexibleDateInput,
  parseIsoDate,
} from '../dateParsing'

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

  data() {
    return {
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
        format: (value) => parseFlexibleDateInput(value, this.field?.locale),
        selectOnFocus: true,
        applyOnBlur: true,
      },
      formats: {
        input: 'yyyy-MM-dd',
      },
    }
  },

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
        return parseIsoDate(value)
      }

      return null
    },

    normalizeDateForFilter(value) {
      return normalizeDateFilterValue(value)
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
