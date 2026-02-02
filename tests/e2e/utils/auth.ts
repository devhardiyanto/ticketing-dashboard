import { Page } from '@playwright/test';

/**
 * Test user credentials for each role
 * These match the users created in DatabaseSeeder.php
 */
export const testUsers = {
	super_admin: {
		email: 'admin@example.com',
		password: 'password',
		name: 'Super Admin',
		organization: null,
	},
	platform_staff: {
		email: 'support@example.com',
		password: 'password',
		name: 'Platform Support Staff',
		organization: null,
	},
	org_admin: {
		email: 'orgadmin@konserorganizer.com',
		password: 'password',
		name: 'Admin Konser Organizer',
		organization: 'Konser Organizer',
	},
	org_staff: {
		email: 'staff@konserorganizer.com',
		password: 'password',
		name: 'Staff Konser Organizer',
		organization: 'Konser Organizer',
	},
} as const;

export type RoleType = keyof typeof testUsers;

/**
 * Login as a specific role
 * @param page - Playwright page object
 * @param role - Role to login as
 */
export async function loginAs(page: Page, role: RoleType): Promise<void> {
	const user = testUsers[role];

	console.log(`[Auth] Logging in as ${role}: ${user.email}`);

	await page.goto('/login');
	await page.waitForLoadState('domcontentloaded');

	// Wait for form to be ready
	await page.waitForSelector('input[name="email"]', { state: 'visible', timeout: 10000 });

	// Fill login form
	await page.fill('input[name="email"]', user.email);
	await page.fill('input[name="password"]', user.password);

	// Submit form
	await page.click('button[type="submit"]');

	// Wait for navigation to complete - either dashboard or any page after login
	await page.waitForURL((url) => {
		const path = url.pathname;
		return path.includes('/dashboard') || path === '/';
	}, { timeout: 15000 });

	// Additional wait for page to be fully loaded
	await page.waitForLoadState('networkidle');

	console.log(`[Auth] Login successful, current URL: ${page.url()}`);
}

/**
 * Logout from the application
 * @param page - Playwright page object
 */
export async function logout(page: Page): Promise<void> {
	// Click user menu
	await page.click('[data-test="user-menu-trigger"]');

	// Click logout button
	await page.click('[data-test="logout-button"]');

	// Wait for redirect to login
	await page.waitForURL('**/login', { timeout: 10000 });
}

/**
 * Navigate to events page
 * @param page - Playwright page object
 */
export async function goToEvents(page: Page): Promise<void> {
	await page.goto('/event');
	await page.waitForLoadState('networkidle');
}

/**
 * Check if an element is visible on the page
 * @param page - Playwright page object
 * @param selector - CSS selector
 */
export async function isVisible(page: Page, selector: string): Promise<boolean> {
	try {
		await page.waitForSelector(selector, { timeout: 3000 });
		return true;
	} catch {
		return false;
	}
}

/**
 * Check if a button with specific text exists and is enabled
 * @param page - Playwright page object
 * @param text - Button text to look for (string or RegExp)
 */
export async function buttonExists(page: Page, text: string | RegExp): Promise<boolean> {
	const button = page.getByRole('button', { name: text });
	return await button.isVisible().catch(() => false);
}

/**
 * Check if a link with specific text exists
 * @param page - Playwright page object
 * @param text - Link text to look for (string or RegExp)
 */
export async function linkExists(page: Page, text: string | RegExp): Promise<boolean> {
	const link = page.getByRole('link', { name: text });
	return await link.isVisible().catch(() => false);
}
