// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * E2E tests for settings form submission
 */
test.describe('Settings Form', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/wp-admin');
    await page.fill('#user_login', 'admin');
    await page.fill('#user_pass', 'password');
    await page.click('#wp-submit');
    await page.waitForURL('**/wp-admin/**');
  });

  test('should save settings successfully', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-settings');
    
    // Fill form fields
    await page.fill('input[name="option_name"]', 'test_value');
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Verify success message
    await expect(page.locator('.notice-success')).toBeVisible();
  });

  test('should validate required fields', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-settings');
    
    // Try to submit without required fields
    await page.click('button[type="submit"]');
    
    // Verify validation error
    await expect(page.locator('.notice-error')).toBeVisible();
  });
});










