// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * E2E tests for database cleanup
 */
test.describe('Database Cleanup', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/wp-admin');
    await page.fill('#user_login', 'admin');
    await page.fill('#user_pass', 'password');
    await page.click('#wp-submit');
    await page.waitForURL('**/wp-admin/**');
  });

  test('should run database cleanup in dry-run mode', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-database');
    
    // Select cleanup scope
    await page.check('input[value="revisions"]');
    
    // Enable dry-run
    await page.check('input[name="dry_run"]');
    
    // Run cleanup
    await page.click('button:has-text("Run Cleanup")');
    
    // Verify results shown
    await expect(page.locator('.cleanup-results')).toBeVisible();
  });

  test('should run actual database cleanup', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=fp-performance-database');
    
    // Select cleanup scope
    await page.check('input[value="revisions"]');
    
    // Run cleanup (not dry-run)
    await page.click('button:has-text("Run Cleanup")');
    
    // Confirm action
    await page.click('button:has-text("Confirm")');
    
    // Verify success message
    await expect(page.locator('.notice-success')).toBeVisible();
  });
});










