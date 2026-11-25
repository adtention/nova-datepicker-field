import { reactive } from 'vue'

/** Values the page shows, keyed by field attribute or filter key. */
export const submittedValues = reactive({})

export function reportFieldValue(key, value) {
  submittedValues[key] = value
}
