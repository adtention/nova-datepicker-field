import { expect, test } from '@playwright/test'
import { checkMenuKeepsItsSide, openHarness, openMenu, scrollTargetTo } from './helpers'

/**
 * @param {import('@playwright/test').Page} page
 * @param {number} day Day of the month shown in the calendar.
 */
function calendarDay(page, day) {
  return openMenu(page)
    .locator('.dp--cell-inner:not(.dp--cell-offset)')
    .filter({ hasText: new RegExp(`^${day}$`) })
}

test('the date menu keeps the side it opened on while the page scrolls', async ({ page }) => {
  const errors = await openHarness(page, 'field')

  await checkMenuKeepsItsSide(page, page.getByPlaceholder('Date'))

  expect(errors).toEqual([])
})

test('a typed date followed by Enter becomes the field value', async ({ page }) => {
  const errors = await openHarness(page, 'field')
  const input = page.getByPlaceholder('Date')

  await input.click()
  await page.keyboard.type('03.10.2026')
  await page.keyboard.press('Enter')

  await expect(page.getByTestId('value')).toHaveText('2026-10-03')
  expect(errors).toEqual([])
})

test('multiple mode adds clicked days, and Shift-click adds the days in between', async ({ page }) => {
  await page.clock.setFixedTime(new Date('2026-03-15T12:00:00'))
  const errors = await openHarness(page, 'multiple')

  const chipInput = page.locator('.nova-datepicker-chip-input')
  const chips = page.locator('.nova-datepicker-chip')

  // Low on the screen the menu opens above, so the chip input growing downwards
  // does not move the calendar under the pointer.
  await scrollTargetTo(page, chipInput, 600)
  await chipInput.click()

  // Wait for each chip before the next click, as a person would.
  await calendarDay(page, 10).click()
  await expect(chips).toHaveCount(1)
  await calendarDay(page, 12).click()
  await expect(chips).toHaveCount(2)
  await calendarDay(page, 14).click()
  await expect(chips).toHaveCount(3)
  await expect(page.getByTestId('value')).toHaveText(
    JSON.stringify(['2026-03-10', '2026-03-12', '2026-03-14']),
  )

  await page.keyboard.down('Shift')
  await calendarDay(page, 18).click()
  await page.keyboard.up('Shift')

  await expect(chips).toHaveCount(7)
  await expect(page.getByTestId('value')).toHaveText(JSON.stringify([
    '2026-03-10',
    '2026-03-12',
    '2026-03-14',
    '2026-03-15',
    '2026-03-16',
    '2026-03-17',
    '2026-03-18',
  ]))
  expect(errors).toEqual([])
})
