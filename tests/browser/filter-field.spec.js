import { expect, test } from '@playwright/test'
import { checkMenuKeepsItsSide, openHarness } from './helpers'

test('the filter date menu keeps the side it opened on while the page scrolls', async ({ page }) => {
  const errors = await openHarness(page, 'filter')

  await checkMenuKeepsItsSide(page, page.getByPlaceholder('Start'))

  expect(errors).toEqual([])
})

test('a typed date followed by Enter updates the filter value', async ({ page }) => {
  const errors = await openHarness(page, 'filter')

  await page.getByPlaceholder('Start').click()
  await page.keyboard.type('03.10.2026')
  await page.keyboard.press('Enter')

  // The filter reports changes after a 500 ms debounce.
  await expect(page.getByTestId('value')).toHaveText(JSON.stringify(['2026-10-03', null]))
  expect(errors).toEqual([])
})
