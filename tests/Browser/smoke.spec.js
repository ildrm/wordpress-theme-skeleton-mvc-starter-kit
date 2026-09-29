import { expect, test } from '@playwright/test';

test.skip(!process.env.WP_BASE_URL, 'Set WP_BASE_URL to a running WordPress site.');

test('front page has navigation, content, and accessible search', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('main#main-content')).toBeVisible();
  await expect(page.locator('a.skip-link')).toHaveAttribute('href', '#main-content');
  await expect(page.locator('nav[aria-label="Primary navigation"]')).toBeVisible();
});

test('search page renders a search form', async ({ page }) => {
  await page.goto('/?s=example');
  await expect(page.locator('main#main-content h1')).toBeVisible();
  await expect(page.getByRole('searchbox')).toBeVisible();
});

test('mobile navigation opens and closes with keyboard', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');
  const toggle = page.locator('[data-navigation-toggle]');
  const navigation = page.locator('[data-navigation]');

  await expect(toggle).toBeVisible();
  await expect(navigation).toBeHidden();
  await toggle.click();
  await expect(toggle).toHaveAttribute('aria-expanded', 'true');
  await expect(navigation).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await expect(navigation).toBeHidden();
  await expect(toggle).toBeFocused();
});
