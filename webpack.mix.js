const mix = require('laravel-mix')
const NovaExtension = require('./vendor/laravel/nova-devtool/nova.mix.js')

mix.extend('nova', new NovaExtension())

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .vue({ version: 3 })
  .css('resources/css/field.css', 'css')
  .nova('adtention/datepicker-field')
  .version()
