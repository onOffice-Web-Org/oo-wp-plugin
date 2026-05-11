import { test, expect } from '@playwright/test';
import { MultiStepFormPage } from '../page-objects/MultiStepFormPage';

const viewports = [
    { name: 'Desktop', width: 1920, height: 1080 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Mobile', width: 375, height: 667 },
];

test.describe('Multi-Step Form POM & Visual', () => {
    let multiStepPage: MultiStepFormPage;

    test.beforeEach(async ({ page }) => {
        multiStepPage = new MultiStepFormPage(page);
    });

    // --- Visual Regression ---
    for (const vp of viewports) {
        test(`Snapshot steps on ${vp.name}`, async ({ page }) => {
            await page.setViewportSize(vp);
            await multiStepPage.goto();
            
            await multiStepPage.acceptCookies();
            await multiStepPage.hideOverlays(); 

            await page.waitForTimeout(1000);

            // Snapshot Step 1
            await expect(multiStepPage.form).toHaveScreenshot(`step-1-${vp.name}.png`, {
                mask: [multiStepPage.form.locator('.c-form__progress-bar')],
                maxDiffPixelRatio: 0.05, 
                animations: 'disabled'
            });

            await multiStepPage.fillStep1Contact('Tester', 'test@onoffice.de');
            await page.waitForTimeout(500);

            // Snapshot Step 2
            await expect(multiStepPage.form).toHaveScreenshot(`step-2-${vp.name}.png`, {
                maxDiffPixelRatio: 0.05,
                animations: 'disabled'
            });
        });
    }

    // --- Functional Tests ---
    test.describe('Functional Flow', () => {
        test.beforeEach(async () => {
            await multiStepPage.goto();
            await multiStepPage.acceptCookies();
        });

        test('Happy Flow: Full submission', async () => {
            await multiStepPage.fillStep1Contact('Owner', 'happy@owner.de');
            
            await multiStepPage.fillStep2Interests({
                area: '150',
                rooms: '5',
                plz: '52068',
                city: 'Aachen'
            });

            await multiStepPage.fillStep3Message('Test Message via POM');
            await multiStepPage.fillStep4Compliance();
            await multiStepPage.submit();

            await expect(multiStepPage.infoMessages).toBeVisible({ timeout: 15000 });
            const msg = await multiStepPage.infoMessages.innerText();
            expect(msg).toMatch(/Vielen Dank|Spam erkannt/);
        });

        test('Negative: Validation blocks transition', async ({ page }) => {
            const mandatoryText = multiStepPage.form.getByText(/Pflichtfelder/i).first();
            
            await mandatoryText.waitFor({ state: 'attached' });
            await mandatoryText.click({ force: true });
            
            await page.waitForTimeout(1000);

            await multiStepPage.nextBtn.click({ force: true });
            await page.waitForTimeout(1000);

            await multiStepPage.nextBtn.click({ force: true });
            
            await expect(multiStepPage.form.getByText(/1\s*\|\s*Ihre kontakdaten/i)).toBeVisible();
            
            const errorMessage = multiStepPage.form.getByText(/Bitte füllen Sie das Pflichtfeld aus/i).first();
            await expect(errorMessage).toBeVisible({ timeout: 7000 });
        });
    });
});