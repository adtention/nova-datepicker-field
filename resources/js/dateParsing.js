/**
 * Parse an ISO 8601 date string (YYYY-MM-DD) into a local Date object.
 *
 * Returns null when the value is not a string, does not match the expected
 * format, or represents an invalid calendar date.
 *
 * @param {unknown} value
 * @returns {Date|null}
 */
export function parseIsoDate(value) {
  if (typeof value !== 'string') {
    return null
  }

  const dateParts = value.match(/^(\d{4})-(\d{2})-(\d{2})/)

  if (dateParts === null) {
    return null
  }

  const parsedDate = new Date(
    Number(dateParts[1]),
    Number(dateParts[2]) - 1,
    Number(dateParts[3]),
  )

  if (
    Number.isNaN(parsedDate.getTime())
    || parsedDate.getFullYear() !== Number(dateParts[1])
    || parsedDate.getMonth() !== Number(dateParts[2]) - 1
    || parsedDate.getDate() !== Number(dateParts[3])
  ) {
    return null
  }

  return parsedDate
}

/**
 * Resolve a BCP 47 locale string from a field's locale meta value.
 *
 * Returns undefined when the locale is absent or empty so that
 * Intl.DateTimeFormat falls back to the runtime default.
 *
 * @param {object} field
 * @returns {string|undefined}
 */
export function resolveLocale(field) {
  if (typeof field?.locale === 'string' && field.locale !== '') {
    return field.locale
  }

  return undefined
}
