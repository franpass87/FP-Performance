// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * E2E tests for admin navigation
 */
test.describe('Admin Navigation', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/wp-admin');
    await page.fill('#user_login', 'admin');
    await page.fill('#user_pass', 'password');
    await page.click('#wp-submit');
    await page.waitForURL('**/wp-admin/**');
  });

  test('should navigate to Overview page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-overview');
    await expect(page.locator('h1')).toContainText('Performance');
  });

  test('should navigate to Assets page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-assets');
    await expect(page.locator('h1')).toContainText('Assets');
  });

  test('should navigate to Cache page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-cache');
    await expect(page.locator('h1')).toContainText('Cache');
  });

  test('should navigate to Database page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-database');
    await expect(page.locator('h1')).toContainText('Database');
  });
});










