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
        :class="{ 'nova-datepicker-field--multiple': isMultiple }"
        :style="datePickerStyle"
        :teleport="true"
        :locale="dateFnsLocale"
        :dark="isDarkMode"
        :multi-dates="isMultiple"
        :multi-dates-separator="multiDatesSeparator"
        :time-config="timeConfiguration"
        :min-date="minimumDate"
        :max-date="maximumDate"
        :auto-apply="autoApply"
        :close-on-auto-apply="closeOnAutoApply"
        :config="calendarConfiguration"
        :action-row="actionRowConfiguration"
        :format="inputDisplayFormat"
        :text-input="textInputOptions"
        :formats="formatOptions"
        :input-attrs="inputAttributes"
        :placeholder="currentField.name"
        :clearable="!isMultiple"
        :disabled="currentlyIsReadonly"
        :readonly="currentlyIsReadonly"
        @date-click="handleDateClick"
      >
        <template
          #dp-input="{
            value: inputValue,
            onBlur,
            onEnter,
            onFocus,
            onInput,
            onKeypress,
            onPaste,
            onTab,
            openMenu,
          }"
        >
          <div
            v-if="isMultiple"
            class="nova-datepicker-chip-input"
            :class="{
              'nova-datepicker-chip-input--readonly': currentlyIsReadonly,
              'nova-datepicker-chip-input--empty': selectedDateChips.length === 0,
            }"
            tabindex="0"
            role="button"
            :aria-label="__('Open date picker')"
            @click="openMenu"
            @keydown.enter.prevent="openMenu"
            @keydown.space.prevent="openMenu"
            @keydown.delete.stop.prevent="removeLastSelectedDate"
            @keydown.backspace.stop.prevent="removeLastSelectedDate"
          >
            <div class="nova-datepicker-chip-input__header">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="nova-datepicker-chip-input__icon"
                aria-hidden="true"
              >
                <path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd" />
              </svg>

              <span
                v-if="selectedDateChips.length > 0"
                class="nova-datepicker-chip-input__count"
              >
                {{ selectedDateChips.length }} {{ __('selected') }}
              </span>

              <span
                v-else
                class="nova-datepicker-chip-input__placeholder"
              >
                {{ __('Click to select dates') }}
              </span>

              <button
                v-if="selectedDateChips.length > 0 && !currentlyIsReadonly"
                type="button"
                class="nova-datepicker-chip-input__clear"
                :aria-label="__('Clear all dates')"
                @mousedown.prevent.stop
                @click.prevent.stop="clearSelectedDates"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                  <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
              </button>
            </div>

            <div
              v-if="selectedDateChips.length > 0"
              class="nova-datepicker-chip-input__chips"
            >
              <span
                v-for="selectedDate in selectedDateChips"
                :key="selectedDate.key"
                class="nova-datepicker-chip"
              >
                <span>{{ selectedDate.label }}</span>
                <button
                  v-if="!currentlyIsReadonly"
                  type="button"
                  class="nova-datepicker-chip-remove"
                  :aria-label="__('Remove') + ' ' + selectedDate.label"
                  @mousedown.prevent.stop
                  @click.prevent.stop="removeSelectedDate(selectedDate.index)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                  </svg>
                </button>
              </span>
            </div>
          </div>

          <input
            v-else
            class="w-full form-control form-input form-control-bordered"
            :disabled="currentlyIsReadonly"
            :placeholder="currentField.name"
            :readonly="currentlyIsReadonly"
            :value="inputValue"
            @blur="onBlur"
            @focus="onFocus"
            @input="onInput"
            @keydown.enter="onEnter"
            @keydown.tab="onTab"
            @keydown="onKeypress"
            @paste="onPaste"
          >
        </template>
      </VueDatePicker>
    </template>
  </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import { resolveDateFnsLocale } from '../dateFnsLocale'
import {
  formatIsoDate,
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
      isShiftPressed: false,
      isDarkMode: false,
      // Shift-range state: the anchor date decides whether the range adds or removes dates.
      rangeSelectionAnchor: null,
      rangeSelectionMode: null,
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
    }
  },

  mounted() {
    this.updateDarkModeState()
    this.updateNovaFontFamily()
    this.startDarkModeObserver()

    if (typeof window !== 'undefined') {
      // Date cells live in the teleported picker menu, so Shift must be tracked globally.
      window.addEventListener('keydown', this.handleGlobalKeyDown)
      window.addEventListener('keyup', this.handleGlobalKeyUp)
      window.addEventListener('blur', this.resetShiftRangeState)
    }
  },

  beforeUnmount() {
    if (typeof window !== 'undefined') {
      window.removeEventListener('keydown', this.handleGlobalKeyDown)
      window.removeEventListener('keyup', this.handleGlobalKeyUp)
      window.removeEventListener('blur', this.resetShiftRangeState)
    }

    if (this.darkModeObserver !== null) {
      this.darkModeObserver.disconnect()
      this.darkModeObserver = null
    }
  },

  computed: {
    calendarConfiguration() {
      if (this.isMultiple) {
        return {
          closeOnAutoApply: false,
        }
      }

      return null
    },

    actionRowConfiguration() {
      if (this.isMultiple) {
        return {
          showCancel: false,
          showPreview: false,
          showSelect: false,
        }
      }

      return null
    },

    autoApply() {
      return true
    },

    closeOnAutoApply() {
      return !this.isMultiple
    },

    isMultiple() {
      return this.currentField?.multiple === true
    },

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

    multiDatesSeparator() {
      return ', '
    },

    inputAttributes() {
      return {
        id: this.currentField.uniqueKey ?? this.currentField.attribute,
        name: this.currentField.attribute,
        autocomplete: 'off',
        inputmode: 'text',
      }
    },

    formatOptions() {
      return {
        input: this.inputDisplayFormat,
      }
    },

    inputDisplayFormat() {
      return (value) => {
        if (this.isMultiple) {
          if (Array.isArray(value)) {
            return value
              .map((item) => this.formatDateForDisplay(item))
              .filter((item) => item !== '')
              .join(', ')
          }

          return this.formatDateForDisplay(value)
        }

        return this.formatDateForDisplay(value)
      }
    },

    textInputOptions() {
      if (this.isMultiple) {
        return {
          ...this.textInputConfiguration,
          applyOnBlur: false,
          enterSubmit: false,
          selectOnFocus: false,
          tabSubmit: false,
        }
      }

      return this.textInputConfiguration
    },

    selectedDateChips() {
      if (!this.isMultiple || !Array.isArray(this.value)) {
        return []
      }

      return this.value
        .map((item, index) => {
          const normalizedDate = normalizeDateFilterValue(item)

          if (normalizedDate === null) {
            return null
          }

          return {
            index,
            key: `${normalizedDate}-${index}`,
            label: this.formatDateForDisplay(item),
            sortDate: normalizedDate,
          }
        })
        .filter((item) => item !== null)
        .sort((left, right) => {
          if (left.sortDate < right.sortDate) {
            return -1
          }

          if (left.sortDate > right.sortDate) {
            return 1
          }

          return left.index - right.index
        })
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

    handleGlobalKeyDown(event) {
      if (event.key === 'Shift') {
        this.isShiftPressed = true
      }
    },

    handleGlobalKeyUp(event) {
      if (event.key === 'Shift') {
        this.isShiftPressed = false
      }
    },

    resetShiftRangeState() {
      // A window blur can swallow keyup, leaving Shift stuck as pressed.
      this.isShiftPressed = false
    },

    handleDateClick(date) {
      const clickedDate = this.normalizeCalendarDate(date)

      if (!this.isMultiple || this.currentlyIsReadonly || clickedDate === null) {
        return
      }

      if (!this.isShiftPressed || this.rangeSelectionAnchor === null) {
        // Clicking a selected anchor means the next Shift-click removes that range.
        this.rangeSelectionAnchor = clickedDate
        this.rangeSelectionMode = this.isDateSelected(clickedDate) ? 'remove' : 'add'

        return
      }

      const anchorDate = this.normalizeCalendarDate(this.rangeSelectionAnchor)

      if (anchorDate === null) {
        this.rangeSelectionAnchor = clickedDate

        return
      }

      this.$nextTick(() => {
        // vue-datepicker applies its own clicked-date toggle before we expand the range.
        const selectedDates = Array.isArray(this.value) ? this.value : []
        const rangeDates = this.buildDateRange(anchorDate, clickedDate)

        this.value = this.rangeSelectionMode === 'remove'
          ? this.removeDateSelections(selectedDates, rangeDates)
          : this.mergeDateSelections([...selectedDates, ...rangeDates])
      })
    },

    normalizeCalendarDate(value) {
      const parsedDate = this.parseDateValue(value)

      if (parsedDate === null) {
        return null
      }

      return new Date(parsedDate.getFullYear(), parsedDate.getMonth(), parsedDate.getDate())
    },

    buildDateRange(startDate, endDate) {
      const step = startDate <= endDate ? 1 : -1
      const dates = []

      for (
        let date = new Date(startDate);
        step === 1 ? date <= endDate : date >= endDate;
        date.setDate(date.getDate() + step)
      ) {
        dates.push(new Date(date))
      }

      return dates
    },

    mergeDateSelections(dates) {
      const datesByIsoDate = new Map()

      dates.forEach((date) => {
        const normalizedDate = this.normalizeCalendarDate(date)

        if (normalizedDate !== null) {
          // ISO date keys de-dupe by local calendar day, ignoring object identity.
          datesByIsoDate.set(formatIsoDate(normalizedDate), normalizedDate)
        }
      })

      return [...datesByIsoDate.entries()]
        .sort(([left], [right]) => left.localeCompare(right))
        .map(([, date]) => date)
    },

    removeDateSelections(selectedDates, datesToRemove) {
      const datesToRemoveByIsoDate = new Set(
        datesToRemove.map((date) => formatIsoDate(date)),
      )

      return this.mergeDateSelections(selectedDates)
        .filter((date) => !datesToRemoveByIsoDate.has(formatIsoDate(date)))
    },

    isDateSelected(date) {
      const isoDate = formatIsoDate(date)

      return Array.isArray(this.value)
        && this.value.some((selectedDate) => {
          const normalizedDate = this.normalizeCalendarDate(selectedDate)

          return normalizedDate !== null && formatIsoDate(normalizedDate) === isoDate
        })
    },

    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.parseInitialValue(this.currentField.value)
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      if (!this.currentlyIsVisible) {
        return
      }

      if (this.isMultiple) {
        this.fillIfVisible(
          formData,
          this.fieldAttribute,
          JSON.stringify(this.normalizeDatesForSubmission(this.value)),
        )

        return
      }

      this.fillIfVisible(
        formData,
        this.fieldAttribute,
        this.normalizeDateForSubmission(this.value),
      )
    },

    parseInitialValue(value) {
      if (!this.isMultiple) {
        return this.parseDateValue(value)
      }

      if (value === null || value === undefined || value === '') {
        return []
      }

      if (Array.isArray(value)) {
        return value
          .map((item) => this.parseDateValue(item))
          .filter((item) => item !== null)
      }

      if (typeof value === 'string') {
        const normalizedValue = value.trim()

        if (normalizedValue === '') {
          return []
        }

        if (normalizedValue.startsWith('[')) {
          try {
            const parsedJson = JSON.parse(normalizedValue)

            if (Array.isArray(parsedJson)) {
              return parsedJson
                .map((item) => this.parseDateValue(item))
                .filter((item) => item !== null)
            }
          } catch {
            // Fallback to comma-separated parsing below.
          }
        }

        return normalizedValue
          .split(',')
          .map((item) => this.parseDateValue(item.trim()))
          .filter((item) => item !== null)
      }

      const parsedValue = this.parseDateValue(value)

      return parsedValue === null ? [] : [parsedValue]
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

    formatDateForDisplay(value) {
      const parsedDate = this.parseDateValue(value)

      if (parsedDate === null) {
        return ''
      }

      return new Intl.DateTimeFormat(this.currentField?.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      }).format(parsedDate)
    },

    normalizeDatesForSubmission(value) {
      if (!Array.isArray(value)) {
        return []
      }

      return [...new Set(value
        .map((item) => normalizeDateFilterValue(item))
        .filter((item) => item !== null))]
    },

    removeSelectedDate(indexToRemove) {
      if (!Array.isArray(this.value)) {
        return
      }

      this.value = this.value.filter((item, index) => index !== indexToRemove)

      if (this.value.length === 0) {
        this.rangeSelectionAnchor = null
        this.rangeSelectionMode = null
      }
    },

    removeLastSelectedDate() {
      if (this.currentlyIsReadonly || !Array.isArray(this.value) || this.value.length === 0) {
        return
      }

      this.value = this.value.slice(0, -1)

      if (this.value.length === 0) {
        this.rangeSelectionAnchor = null
        this.rangeSelectionMode = null
      }
    },

    clearSelectedDates() {
      this.value = []
      this.rangeSelectionAnchor = null
      this.rangeSelectionMode = null
    },
  },
}
</script>
