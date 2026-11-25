import { expect } from '@playwright/test'

/** Where the pointer rests while it scrolls the page: right of the 320px wide field and its menu. */
const SCROLL_POINTER = { x: 1100, y: 400 }

/**
 * Opens the harness and fails the test on any console error or uncaught exception.
 *
 * @param {import('@playwright/test').Page} page
 * @param {'field'|'multiple'|'filter'} mount
 * @returns {Promise<string[]>} The errors seen so far; check it is empty at the end.
 */
export async function openHarness(page, mount) {
  const errors = []

  page.on('console', (message) => {
    if (message.type() === 'error') {
      errors.push(message.text())
    }
  })
  page.on('pageerror', (error) => errors.push(error.message))

  await page.goto(`/?mount=${mount}`)
  await expect(page.getByTestId('component')).toBeVisible()

  return errors
}

/**
 * Scrolls the page with the mouse wheel until the top of `target` sits at `top`
 * pixels from the top of the viewport. The pointer stays outside the menu, because
 * the wheel over an open calendar changes its month instead of scrolling the page.
 *
 * @param {import('@playwright/test').Page} page
 * @param {import('@playwright/test').Locator} target
 * @param {number} top
 */
export async function scrollTargetTo(page, target, top) {
  await page.mouse.move(SCROLL_POINTER.x, SCROLL_POINTER.y)

  await expect(async () => {
    const box = await target.boundingBox()
    const distance = Math.round(box.y - top)

    if (Math.abs(distance) > 2) {
      await page.mouse.wheel(0, distance)
    }

    expect(Math.abs((await target.boundingBox()).y - top)).toBeLessThanOrEqual(2)
  }).toPass({ timeout: 5000 })

  await waitForAnimationFrames(page, 3)
}

/**
 * @param {import('@playwright/test').Page} page
 * @param {number} count
 */
export async function waitForAnimationFrames(page, count) {
  await page.evaluate((frames) => new Promise((resolve) => {
    const next = (remaining) => {
      if (remaining === 0) {
        resolve()

        return
      }

      requestAnimationFrame(() => next(remaining - 1))
    }

    next(frames)
  }), count)
}

/**
 * The open (not leaving) date menu.
 *
 * @param {import('@playwright/test').Page} page
 */
export function openMenu(page) {
  return page.locator('.dp--menu').last()
}

/**
 * Which side of `target` the open menu is on, by comparing vertical centres.
 *
 * @param {import('@playwright/test').Page} page
 * @param {import('@playwright/test').Locator} target
 * @returns {Promise<'above'|'below'>}
 */
export async function menuSide(page, target) {
  await expect(page.locator('.dp--menu')).toHaveCount(1)

  const menuBox = await openMenu(page).boundingBox()
  const targetBox = await target.boundingBox()

  if (menuBox.y + menuBox.height / 2 < targetBox.y + targetBox.height / 2) {
    return 'above'
  }

  return 'below'
}

/**
 * Closes the menu with Escape and waits until its leave animation is over.
 *
 * @param {import('@playwright/test').Page} page
 */
export async function closeMenu(page) {
  await page.keyboard.press('Escape')
  await expect(page.locator('.dp--menu')).toHaveCount(0)
}

/**
 * The checks shared by the field and the filter: the menu keeps the side it opened
 * on while the page scrolls, and a menu closed straight after opening leaves no lock
 * behind for the next opening.
 *
 * @param {import('@playwright/test').Page} page
 * @param {import('@playwright/test').Locator} input The element that opens the menu.
 */
export async function checkMenuKeepsItsSide(page, input) {
  const lowInViewport = 640
  const highInViewport = 120

  // Low on the screen there is room only above, so the menu opens above.
  await scrollTargetTo(page, input, lowInViewport)
  await input.click()
  await waitForAnimationFrames(page, 3)
  expect(await menuSide(page, input)).toBe('above')

  // Scrolled up the screen, there is room below now; the menu must not jump there.
  await scrollTargetTo(page, input, highInViewport)
  expect(await menuSide(page, input)).toBe('above')

  await closeMenu(page)

  // High on the screen it opens below, and stays below when scrolled back down.
  await input.click()
  await waitForAnimationFrames(page, 3)
  expect(await menuSide(page, input)).toBe('below')

  await scrollTargetTo(page, input, lowInViewport)
  expect(await menuSide(page, input)).toBe('below')

  await closeMenu(page)

  // Open below and close again before the side lock has run...
  await scrollTargetTo(page, input, highInViewport)
  await input.click()
  await page.keyboard.press('Escape')
  await expect(page.locator('.dp--menu')).toHaveCount(0)

  // ...then where only the top has room, the next opening still goes above.
  await scrollTargetTo(page, input, lowInViewport)
  await input.click()
  await waitForAnimationFrames(page, 3)
  expect(await menuSide(page, input)).toBe('above')

  await closeMenu(page)
}
