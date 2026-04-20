<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <VueDatePicker
        v-model="value"
        class="w-full nova-datepicker-field"
        :style="datePickerStyle"
        :locale="dateFnsLocale"
        :dark="isDarkMode"
        :time-config="timeConfiguration"
        :min-date="minimumDate"
        :max-date="maximumDate"
        :auto-apply="true"
        :text-input="textInputConfiguration"
        :formats="formats"
        :input-attrs="inputAttributes"
        :placeholder="currentField.name"
        :disabled="currentlyIsReadonly"
        :readonly="currentlyIsReadonly"
      />
    </template>
  </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import { resolveDateFnsLocale } from '../dateFnsLocale'
import {
  normalizeDateFilterValue,
  parseFlexibleDateInput,
  parseIsoDate,
} from '../dateParsing'

export default {
  mixins: [DependentFormField, HandlesValidationErrors],

  components: {
    VueDatePicker,
  },

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      darkModeObserver: null,
      isDarkMode: false,
      novaFontFamily: '',
      textInputConfiguration: {
        enterSubmit: true,
        tabSubmit: true,
        openMenu: 'open',
        format: (value) => parseFlexibleDateInput(value, this.currentField?.locale),
        selectOnFocus: true,
        applyOnBlur: true,
      },
      timeConfiguration: {
        enableTimePicker: false,
      },
      formats: {
        input: 'yyyy-MM-dd',
      },
    }
  },

  mounted() {
    this.updateDarkModeState()
    this.updateNovaFontFamily()
    this.startDarkModeObserver()
  },

  beforeUnmount() {
    if (this.darkModeObserver !== null) {
      this.darkModeObserver.disconnect()
      this.darkModeObserver = null
    }
  },

  computed: {
    dateFnsLocale() {
      return resolveDateFnsLocale(this.currentField?.locale)
    },

    datePickerStyle() {
      if (this.novaFontFamily === '') {
        return null
      }

      return {
        '--dp-font-family': this.novaFontFamily,
      }
    },

    minimumDate() {
      return this.parseDateValue(this.currentField.min)
    },

    maximumDate() {
      return this.parseDateValue(this.currentField.max)
    },

    inputAttributes() {
      return {
        id: this.currentField.uniqueKey ?? this.currentField.attribute,
        name: this.currentField.attribute,
        autocomplete: 'off',
        inputmode: 'text',
      }
    },
  },

  methods: {
    updateDarkModeState() {
      if (typeof document === 'undefined') {
        this.isDarkMode = false

        return
      }

      this.isDarkMode = document.documentElement.classList.contains('dark')
    },

    startDarkModeObserver() {
      if (typeof document === 'undefined' || typeof MutationObserver === 'undefined') {
        return
      }

      this.darkModeObserver = new MutationObserver(() => {
        this.updateDarkModeState()
      })

      this.darkModeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
      })
    },

    updateNovaFontFamily() {
      if (typeof document === 'undefined') {
        this.novaFontFamily = ''

        return
      }

      this.novaFontFamily = getComputedStyle(document.body).fontFamily
    },

    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.parseDateValue(this.currentField.value)
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      if (this.currentlyIsVisible) {
        this.fillIfVisible(
          formData,
          this.fieldAttribute,
          this.normalizeDateForSubmission(this.value),
        )
      }
    },

    parseDateValue(value) {
      if (value === null || value === undefined || value === '') {
        return null
      }

      if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value
      }

      if (typeof value === 'number') {
        const parsedNumberDate = new Date(value)

        return Number.isNaN(parsedNumberDate.getTime()) ? null : parsedNumberDate
      }

      if (typeof value === 'string') {
        const parsedIsoDate = parseIsoDate(value)

        if (parsedIsoDate !== null) {
          return parsedIsoDate
        }

        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
          return null
        }

        const parsedStringDate = new Date(value)

        return Number.isNaN(parsedStringDate.getTime()) ? null : parsedStringDate
      }

      return null
    },

    normalizeDateForSubmission(value) {
      return normalizeDateFilterValue(value) ?? ''
    },
  },
}
</script>
