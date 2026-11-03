const { test, expect } = require('@playwright/test');

test('homepage makes the private conversation immediately discoverable', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/Kristohanon/);
  await expect(page.locator('h1')).toHaveCount(1);
  await expect(page.locator('h1')).toContainText('Scripture and prayer');

  const message = page.locator('#message');
  await expect(message).toBeVisible();
  await expect(message).toBeFocused();
  await expect(page.locator('#chatForm button[type="submit"]')).toBeVisible();
  await expect(page.locator('#newsletterEmail')).toBeVisible();
});

test('English and Cebuano conversation controls are keyboard-accessible', async ({ page }) => {
  await page.goto('/');
  const cebuano = page.getByRole('button', { name: 'Cebuano' }).first();
  await cebuano.focus();
  await page.keyboard.press('Enter');
  await expect(cebuano).toHaveAttribute('aria-pressed', 'true');
  await expect(page.locator('html')).toHaveAttribute('lang', 'ceb');
});

test('Rosary opens with today’s mysteries and switches language', async ({ page }) => {
  await page.goto('/rosary');
  await expect(page.locator('h1')).toHaveText('The Holy Rosary');
  await expect(page.locator('#stepCount')).toContainText('Step 1');
  await page.locator('[data-rosary-language="ceb"]').click();
  await expect(page.locator('html')).toHaveAttribute('lang', 'ceb');
  await expect(page.locator('#rosaryTitle')).not.toHaveText('The Holy Rosary');
});

test('Prayer library can be searched and shown in Cebuano', async ({ page }) => {
  await page.goto('/prayers');
  await expect(page.locator('h1')).toHaveCount(1);
  await page.locator('[data-prayer-language="ceb"]').click();
  await expect(page.locator('html')).toHaveAttribute('lang', 'ceb');
  await page.locator('#prayerSearch').fill('Maria');
  await expect(page.locator('#prayerLibrary')).toContainText(/Maria/i);
});

test('clean public routes do not expose PHP filenames', async ({ page }) => {
  for (const route of ['/about', '/contact', '/prayers', '/rosary', '/privacy', '/safety', '/sources']) {
    const response = await page.goto(route);
    expect(response.status(), route).toBe(200);
    expect(page.url(), route).not.toContain('.php');
    await expect(page.locator('h1')).toHaveCount(1);
  }
});
