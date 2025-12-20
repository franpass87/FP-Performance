// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * E2E tests for cache purging
 */
test.describe('Cache Purge', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/wp-admin');
    await page.fill('#user_login', 'admin');
    await page.fill('#user_pass', 'password');
    await page.click('#wp-submit');
    await page.waitForURL('**/wp-admin/**');
  });

  test('should purge all cache', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-cache');
    
    // Click purge all button
    await page.click('button:has-text("Purge All Cache")');
    
    // Confirm action
    await page.click('button:has-text("Confirm")');
    
    // Verify success message
    await expect(page.locator('.notice-success')).toBeVisible();
  });

  test('should purge URL cache', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-cache');
    
    // Fill URL field
    await page.fill('input[name="purge_url"]', 'https://example.com/page');
    
    // Click purge button
    await page.click('button:has-text("Purge URL")');
    
    // Verify success message
    await expect(page.locator('.notice-success')).toBeVisible();
  });
});










