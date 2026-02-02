import { expect, Page } from '@playwright/test';
import { RoleType } from './auth';
import { getExpectedSidebar, rolePermissions } from './permissions';

/**
 * Sidebar verification helpers for E2E tests
 * Use these to ensure sidebar items match the expected permissions for each role
 */

/**
 * Verify that all expected sidebar items are visible for a role
 * @param page - Playwright page object
 * @param role - Role to verify sidebar for
 */
export async function verifySidebarItems(page: Page, role: RoleType): Promise<void> {
  const expectedItems = getExpectedSidebar(role);

  // Scope to sidebar menu buttons to avoid matching breadcrumb links
  const sidebarLinks = page.locator('[data-sidebar="menu-button"]');

  for (const menuItem of expectedItems) {
    const item = sidebarLinks.filter({ hasText: menuItem }).first();
    await expect(item, `Sidebar item "${menuItem}" should be visible for ${role}`).toBeVisible();
  }
}

/**
 * Verify that specific sidebar items are NOT visible for a role
 * Useful for negative testing - ensuring restricted items are hidden
 * @param page - Playwright page object
 * @param role - Role to verify sidebar for
 */
export async function verifyHiddenSidebarItems(page: Page, role: RoleType): Promise<void> {
  const visibleItems = getExpectedSidebar(role);
  const allItems = rolePermissions.super_admin.sidebar; // Super admin has all items

  const hiddenItems = allItems.filter(item => !visibleItems.includes(item));

  // Scope to sidebar menu buttons
  const sidebarLinks = page.locator('[data-sidebar="menu-button"]');

  for (const menuItem of hiddenItems) {
    const item = sidebarLinks.filter({ hasText: menuItem });
    await expect(item, `Sidebar item "${menuItem}" should NOT be visible for ${role}`).toHaveCount(0);
  }
}

/**
 * Full sidebar verification - checks both visible and hidden items
 * @param page - Playwright page object
 * @param role - Role to verify sidebar for
 */
export async function verifySidebar(page: Page, role: RoleType): Promise<void> {
  await verifySidebarItems(page, role);
  await verifyHiddenSidebarItems(page, role);
}

/**
 * Check if a specific sidebar item is visible
 * @param page - Playwright page object
 * @param itemName - Name of sidebar item to check
 */
export async function isSidebarItemVisible(page: Page, itemName: string): Promise<boolean> {
  const item = page.getByRole('link', { name: itemName, exact: true });
  return await item.isVisible().catch(() => false);
}

/**
 * Navigate to a sidebar item
 * @param page - Playwright page object
 * @param itemName - Name of sidebar item to click
 */
export async function clickSidebarItem(page: Page, itemName: string): Promise<void> {
  const item = page.getByRole('link', { name: itemName, exact: true });
  await item.click();
  await page.waitForLoadState('networkidle');
}
