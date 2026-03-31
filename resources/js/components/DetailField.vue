<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <p v-if="fieldHasValue || usesCustomizedDisplay" :title="field.value">
        {{ formattedDate }}
      </p>
      <p v-else>&mdash;</p>
    </template>
  </PanelItem>
</template>

<script>
import { FieldValue } from 'laravel-nova'
import { parseIsoDate, resolveLocale } from '../dateParsing'

export default {
  mixins: [FieldValue],

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

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
