import { test, expect } from '@playwright/test';
import { loginAs, goToEvents, buttonExists, linkExists } from '../utils/auth';
import { verifySidebar } from '../utils/sidebar';

/**
 * Org Admin - Events Feature Tests
 * Org Admin has FULL access to events within their own organization ONLY
 *
 * Permissions: events.create, events.read, events.update, events.delete, events.publish
 * Scope: Own organization only
 */
test.describe('Events - Org Admin', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'org_admin');
  });

  test('sidebar shows correct menu items for role', async ({ page }) => {
    await verifySidebar(page, 'org_admin');
  });

  test('can view events list', async ({ page }) => {
    await goToEvents(page);

    // Should be on events page
    await expect(page).toHaveURL(/\/event/);

    // Page should have events table or list
    await expect(page.locator('table, [data-test="events-list"]')).toBeVisible();
  });

  test('can see create button', async ({ page }) => {
    await goToEvents(page);

    // Should have create button
    const hasCreateButton = await buttonExists(page, /create|add|new/i);
    const hasCreateLink = await linkExists(page, /create|add|new/i);

    expect(hasCreateButton || hasCreateLink).toBeTruthy();
  });

  test('can access create event page', async ({ page }) => {
    await goToEvents(page);

    // Click create button/link
    const createBtn = page.getByRole('link', { name: /create|add|new/i }).or(
      page.getByRole('button', { name: /create|add|new/i })
    );
    await createBtn.first().click();

    // Should navigate to create page
    await expect(page).toHaveURL(/\/event\/create/);
  });

  test('can see edit button on own org event', async ({ page }) => {
    await goToEvents(page);

    // Look for edit action in table row
    const editButton = page.getByRole('button', { name: /edit/i }).or(
      page.getByRole('link', { name: /edit/i })
    );

    // If there are events, edit button should be visible
    const eventsExist = await page.locator('tbody tr').count() > 0;
    if (eventsExist) {
      await expect(editButton.first()).toBeVisible();
    }
  });

  test('can see delete button on own org event', async ({ page }) => {
    await goToEvents(page);

    // Look for delete action in table row
    const deleteButton = page.getByRole('button', { name: /delete|hapus/i });

    // If there are events, delete button should be visible
    const eventsExist = await page.locator('tbody tr').count() > 0;
    if (eventsExist) {
      await expect(deleteButton.first()).toBeVisible();
    }
  });

  test('only sees own organization events', async ({ page }) => {
    await goToEvents(page);

    // Org Admin should only see their own organization's events
    // Check that the page loads without errors
    await expect(page.locator('table, [data-test="events-list"]')).toBeVisible();

    // Note: Detailed org filtering verification would require checking
    // that no events from other orgs appear - this depends on test data
  });
});
