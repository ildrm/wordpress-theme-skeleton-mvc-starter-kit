import { expect, test } from '@playwright/test';

test('front page has navigation, content, and accessible search', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('main#main-content')).toBeVisible();
  await expect(page.locator('a.skip-link')).toHaveAttribute('href', '#main-content');
  await expect(page.locator('nav[aria-label="Primary navigation"]')).toBeVisible();
});

test('search page renders a search form', async ({ page }) => {
  await page.goto('/?s=CI+Post');
  await expect(page.locator('main#main-content h1')).toBeVisible();
  await expect(page.getByRole('searchbox')).toBeVisible();
  await expect(page.locator('main#main-content')).toContainText('CI Post');
});

test('seeded post and page render their content', async ({ page }) => {
  await page.goto('/ci-post/');
  await expect(page.locator('main#main-content')).toContainText('A seeded integration post.');
  await page.goto('/ci-page/');
  await expect(page.locator('main#main-content')).toContainText('A seeded integration page.');
});

test('content and navigation remain available without JavaScript', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  try {
    const page = await context.newPage();
    await page.goto('/ci-page/');
    await expect(page.locator('main#main-content')).toContainText('A seeded integration page.');
    await expect(page.locator('nav[aria-label="Primary navigation"]')).toBeVisible();
  } finally {
    await context.close();
  }
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
