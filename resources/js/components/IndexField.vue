<template>
  <div>
    <div :class="`text-${field.textAlign}`">
      <span v-if="fieldHasValue" class="whitespace-nowrap">
        {{ formattedDate }}
      </span>
      <span v-else>&mdash;</span>
    </div>
  </div>
</template>

<script>
import { FieldValue } from 'laravel-nova'
import { parseIsoDate, resolveLocale } from '../dateParsing'

export default {
  mixins: [FieldValue],

  props: ['resourceName', 'field'],

  computed: {
    formattedDate() {
      if (this.field.usesCustomizedDisplay) {
        return this.field.displayedAs
      }

      const parsedDate = parseIsoDate(this.field.value)

      if (parsedDate === null) {
        return this.field.value
      }

      return new Intl.DateTimeFormat(resolveLocale(this.field), {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      }).format(parsedDate)
    },
  },
}
</script>
