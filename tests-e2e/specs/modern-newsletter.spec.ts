import { test, expect } from '@playwright/test';
import { WPController } from '../Helpers/wp-utils';

const themes = [
    'onoffice-modern',
    'onoffice-classic',
    'onoffice-pure',
    'onoffice-timeless'
];

const viewports = [
    { name: 'Desktop', width: 1920, height: 1080 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Mobile', width: 375, height: 667 },
];

test.describe('Newsletter Formular POM & Visual', () => {

    test.describe.configure({ mode: 'serial' });

    for (const theme of themes) {
        test.describe(`Theme: ${theme}`, () => {
            test.beforeAll(async () => {
                WPController.activateTheme(theme);
                await new Promise(resolve => setTimeout(resolve, 5000));
            });

            test.beforeEach(async ({ page }) => {
                const url = `/test-modern-formulare/newsletter/?nocache=${Date.now()}&force=${theme}`;
                await page.goto(url, { waitUntil: 'domcontentloaded' });
                await page.waitForSelector('body');
            });

            // --- Visual Regression ---
            for (const vp of viewports) {
                test(`Visual: Snapshot check on ${vp.name}`, async ({ page }) => {
                    await page.setViewportSize({ width: vp.width, height: vp.height });
                    
                    const form = page.locator('form').first();
                    await expect(form).toBeVisible();
                    
                    await expect(form).toHaveScreenshot(`newsletter-form-${theme}-${vp.name}.png`, {
                        maxDiffPixelRatio: 0.02,
                        threshold: 0.2,
                        animations: 'disabled',
                    });
                });
            }

            // --- Functional Tests ---
            test.describe('Functional Scenarios', () => {
                test.beforeEach(async ({ page }) => {
                    await page.waitForLoadState('domcontentloaded');
                });

                test('Erfolgreiches Newsletter Absenden', async ({ page }) => {
                    const form = page.locator('form').first();
                    const emailInput = form.locator('input[type="email"]').first();
                    
                    if (await emailInput.count() > 0) {
                        await emailInput.fill(`newsletter-test-${Date.now()}@onoffice.de`);
                        await form.locator('button[type="submit"]').click();
                        
                        await expect(page.getByText(/Vielen Dank|erfolgreich/i)).toBeVisible({ timeout: 10000 });
                    }
                });

                test('Newsletter - Email Validierung', async ({ page }) => {
                    const form = page.locator('form').first();
                    const emailInput = form.locator('input[type="email"]').first();
                    
                    if (await emailInput.count() > 0) {
                        await emailInput.fill('invalid-email');
                        await form.locator('button[type="submit"]').click();
                        
                        // Validierungsfehler sollte erscheinen
                        await expect(page.getByText(/gültig|invalid/i)).toBeVisible({ timeout: 5000 });
                    }
                });
            }); 
        }); 
    } 
});
