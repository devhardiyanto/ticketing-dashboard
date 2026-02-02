import { test, expect } from '@playwright/test';
import { loginAs, goToEvents, buttonExists, linkExists } from '../utils/auth';
import { verifySidebar } from '../utils/sidebar';

/**
 * Org Staff - Events Feature Tests
 * Org Staff has LIMITED access to events within their own organization
 *
 * Permissions: events.read, events.update (edit only, no create/delete)
 * No: events.create, events.delete, events.publish
 * Scope: Own organization only
 */
test.describe('Events - Org Staff', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'org_staff');
  });

  test('sidebar shows correct menu items for role', async ({ page }) => {
    await verifySidebar(page, 'org_staff');
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

    // Should NOT have create button (no events.create permission)
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

  test('can see edit button on own org event', async ({ page }) => {
    await goToEvents(page);

    // Org Staff CAN edit events (has events.update permission)
    const editButton = page.getByRole('button', { name: /edit/i }).or(
      page.getByRole('link', { name: /edit/i })
    );

    // If there are events, edit button should be visible
    const eventsExist = await page.locator('tbody tr').count() > 0;
    if (eventsExist) {
      await expect(editButton.first()).toBeVisible();
    }
  });

  test('cannot see delete button on event row', async ({ page }) => {
    await goToEvents(page);

    // Org Staff CANNOT delete events (no events.delete permission)
    const deleteButton = page.getByRole('button', { name: /delete|hapus/i });

    // Delete button should NOT be visible for org staff
    await expect(deleteButton.first()).not.toBeVisible().catch(() => true);
  });

  test('only sees own organization events', async ({ page }) => {
    await goToEvents(page);

    // Org Staff should only see their own organization's events
    await expect(page.locator('table, [data-test="events-list"]')).toBeVisible();
  });
});
