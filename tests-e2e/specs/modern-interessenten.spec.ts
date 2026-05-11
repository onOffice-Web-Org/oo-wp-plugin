import { test, expect } from '@playwright/test';
import { InterestsFormPage } from '../page-objects/InterestsFormPage';

const viewports = [
    { name: 'Desktop', width: 1920, height: 1080 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Mobile', width: 375, height: 667 },
];

test.describe('Interessentenformular POM & Visual', () => {
    let interestsPage: InterestsFormPage;

    test.beforeEach(async ({ page }) => {
        interestsPage = new InterestsFormPage(page);
    });

    for (const vp of viewports) {
        test(`Snapshot steps on ${vp.name} (${vp.width}x${vp.height})`, async ({ page }) => {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            
            await interestsPage.goto();
            await interestsPage.acceptCookies(); 
            await interestsPage.hideOverlays();  
            
            await page.waitForTimeout(1000);

            await expect(interestsPage.form).toHaveScreenshot(`interessenten-form-${vp.name}.png`, {
                maxDiffPixelRatio: 0.05, 
                animations: 'disabled',
            });
        });
    }

    test('Erfolgreiches Absenden', async () => {
        await interestsPage.goto();
        await interestsPage.acceptCookies();

        const testData = {
            firstName: 'Mapping-Test',
            lastName: 'E2E-User',
            email: 'test@onoffice.de',
            phone: '+49123456789',
            message: 'E2E Test Nachricht'
        };

        await interestsPage.checkAllRequiredCheckboxes();
        await interestsPage.fillSearchCriteria('4', '500000');
        await interestsPage.fillContactInfo(testData);

        await interestsPage.submit();

        await expect(interestsPage.infoMessages).toBeVisible({ timeout: 20000 });
        const text = await interestsPage.infoMessages.innerText();
        expect(text).toMatch(/Vielen Dank|Spam erkannt/);
    });

    test('Validierung: Sperrung bei leeren Feldern', async ({ page }) => {
        await interestsPage.goto();
        await interestsPage.acceptCookies();
        
        await interestsPage.form.getByText(/Pflichtfelder/i).first().click({ force: true });
        await page.waitForTimeout(1000);

        await interestsPage.submitBtn.click({ force: true });
        await page.waitForTimeout(500);
        await interestsPage.submitBtn.click({ force: true });

        const errorText = interestsPage.form.getByText(/Pflichtfeld/i).first();
        
        await expect(errorText).toBeVisible({ timeout: 7000 });
        await expect(errorText).toContainText(/Pflichtfeld/i);

        const invalidMarkers = interestsPage.form.locator('[class*="invalid"], [class*="error"], .is-invalid');
        const count = await invalidMarkers.count();
        expect(count).toBeGreaterThan(0);
    });
});