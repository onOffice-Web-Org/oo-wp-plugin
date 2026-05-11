import { test, expect } from '@playwright/test';
import { OwnerFormPage } from '../page-objects/OwnerFormPage';

const viewports = [
    { name: 'Desktop', width: 1920, height: 1080 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Mobile', width: 375, height: 667 },
];

test.describe('Eigentümerformular POM & Visual', () => {
    let ownerPage: OwnerFormPage;

    test.beforeEach(async ({ page }) => {
        ownerPage = new OwnerFormPage(page);
    });

    for (const vp of viewports) {
        test(`Visual check on ${vp.name}`, async ({ page }) => {
            await page.setViewportSize(vp);
            await ownerPage.goto();
            await ownerPage.hideOverlays();

            await expect(ownerPage.form).toHaveScreenshot(`eigentuemer-standard-${vp.name}.png`, {
                maxDiffPixelRatio: 0.02,
                animations: 'disabled',
            });
        });
    }

    test.describe('Functional tests', () => {
        test.beforeEach(async () => {
            await ownerPage.goto();
            await ownerPage.acceptCookies();
        });

        test('1. Validation von Formularelementen', async () => {
            await expect(ownerPage.form.getByText('Angaben zu Ihrem Eigentum')).toBeVisible();
            await expect(ownerPage.form.getByText('Ihre Kontaktdaten')).toBeVisible();
            
            const headers = ownerPage.form.locator('h2');
            await expect(headers).toHaveCount(2);
        });

        test('2. Vollständiger Zyklus der Formularübermittlung (Lead Generation)', async () => {
            const testData = {
                plz: '52068',
                flaeche: '150',
                firstName: 'Happy',
                lastName: 'Owner',
                email: 'owner@test.de',
                phone: '+49123456789'
            };

            await ownerPage.fillPropertyData(testData.plz, testData.flaeche);
            await ownerPage.fillContactData(testData);
            await ownerPage.submit();

            await expect(ownerPage.infoMessages).toBeVisible({ timeout: 20000 });
            const msgText = await ownerPage.infoMessages.innerText();
            expect(msgText).toMatch(/Vielen Dank|Spam erkannt/);
        });
    });
});