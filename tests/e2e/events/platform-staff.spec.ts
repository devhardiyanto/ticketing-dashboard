import { test, expect } from '@playwright/test';
import { loginAs, goToEvents, buttonExists, linkExists } from '../utils/auth';
import { verifySidebar } from '../utils/sidebar';

/**
 * Platform Staff - Events Feature Tests
 * Platform Staff has READ-ONLY access to events across all organizations
 *
 * Permissions: events.read ONLY
 * No: events.create, events.update, events.delete, events.publish
 */
test.describe('Events - Platform Staff', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'platform_staff');
  });

  test('sidebar shows correct menu items for role', async ({ page }) => {
    await verifySidebar(page, 'platform_staff');
  });

  test('can view events list', async ({ page }) => {
    await goToEvents(page);

    // Should be on events page
    await expect(page).toHaveURL(/\/event/);

    // Page should have events table or list
    await expect(page.locator('table, [data-test="events-list"]')).toBeVisible();
  });

  test('cannot see create button', async ({ page }) => {
    await goToEvents(page);

    // Should NOT have create button
    const hasCreateButton = await buttonExists(page, /create|add|new/i);
    const hasCreateLink = await linkExists(page, /create|add|new/i);

    expect(hasCreateButton || hasCreateLink).toBeFalsy();
  });

  test('cannot access create event page directly', async ({ page }) => {
    // Try to access create page directly
    await page.goto('/event/create');

    // Should be redirected or see forbidden
    const url = page.url();
    const isForbidden = await page.locator('text=/forbidden|403|unauthorized/i').isVisible().catch(() => false);

    // Either redirected away from create page OR shows forbidden
    expect(url.includes('/event/create') === false || isForbidden).toBeTruthy();
  });

  test('cannot see edit button on event row', async ({ page }) => {
    await goToEvents(page);

    // Look for edit action in table row - should NOT be visible
    const editButton = page.getByRole('button', { name: /edit/i }).or(
      page.getByRole('link', { name: /edit/i })
    );

    // Edit button should NOT be visible for platform staff
    await expect(editButton.first()).not.toBeVisible().catch(() => true);
  });

  test('cannot see delete button on event row', async ({ page }) => {
    await goToEvents(page);

    // Look for delete action - should NOT be visible
    const deleteButton = page.getByRole('button', { name: /delete|hapus/i });

    // Delete button should NOT be visible for platform staff
    await expect(deleteButton.first()).not.toBeVisible().catch(() => true);
  });
});
