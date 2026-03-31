import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import FilterField from './components/FilterField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-datepicker-field', IndexField)
  app.component('detail-datepicker-field', DetailField)
  app.component('form-datepicker-field', FormField)
  app.component('filter-datepicker-field', FilterField)
  app.component('preview-datepicker-field', PreviewField)
})
