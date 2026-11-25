/**
 * Stand-ins for the parts of Nova's `laravel-nova` package that FormField.vue uses.
 * They copy only the behaviour the field relies on: Nova calls setInitialValue when
 * the field is created, reads `currentField`, and calls fill() when the form is
 * submitted. The harness calls fill() on every change, so the page always shows the
 * value Nova would submit.
 */
import { reportFieldValue } from '../submittedValues'

export const DependentFormField = {
  props: {
    showHelpText: { type: Boolean, default: false },
    fullWidthContent: { type: Boolean, default: false },
  },

  data() {
    return {
      value: null,
    }
  },

  computed: {
    currentField() {
      return this.field
    },

    currentlyIsReadonly() {
      return this.field.readonly === true
    },

    currentlyIsVisible() {
      return true
    },

    fieldAttribute() {
      return this.field.attribute
    },
  },

  watch: {
    value: {
      deep: true,
      handler() {
        this.reportSubmittedValue()
      },
    },
  },

  created() {
    this.setInitialValue()
  },

  mounted() {
    this.reportSubmittedValue()
  },

  methods: {
    fillIfVisible(formData, attribute, value) {
      formData.append(attribute, value)
    },

    reportSubmittedValue() {
      const formData = new FormData()

      this.fill(formData)
      reportFieldValue(this.fieldAttribute, formData.get(this.fieldAttribute) ?? '')
    },
  },
}

export const HandlesValidationErrors = {
  props: {
    errors: {
      type: Object,
      default: () => ({}),
    },
  },
}
