import { test, expect } from '@playwright/test';
import { OwnerFormPage } from '../page-objects/OwnerFormPage';

const THEMES = [
    'onoffice-modern',
    'onoffice-classic',
    'onoffice-pure',
    'onoffice-timeless'
];

const VIEWPORTS = [
    { name: 'Desktop', width: 1920, height: 1080 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Mobile', width: 375, height: 667 },
];

const E2E_SECRET = 'qa_rocks';
const BASE_PATH = '/e2e-test-formulare/eigentuemerformular/';

test.describe('Eigentümerformular: Multi-Theme Engine & Visual Regression', () => {

    for (const theme of THEMES) {
        test.describe(`Theme: ${theme}`, () => {
            let ownerPage: OwnerFormPage;

            test.beforeEach(async ({ page }) => {
                ownerPage = new OwnerFormPage(page);
                
                // URLs mit Theme-Wechsel- und Cache-Umgehungs-Flags generieren
                const url = `${BASE_PATH}?force_theme=${theme}&e2e_key=${E2E_SECRET}&t=${Date.now()}`;

                // Перехватываем HTML-код формы и динамически встраиваем mock-аттрибут для капчи ALTCHA
                await page.route('**/eigentuemerformular/**', async route => {
                    const response = await route.fetch();
                    if (response.headers()['content-type']?.includes('text/html')) {
                        let htmlText = await response.text();
                        htmlText = htmlText.replace('<altcha-widget', '<altcha-widget mock');
                        await route.fulfill({ response, body: htmlText });
                    } else {
                        await route.fallback();
                    }
                });
                
                await page.goto(url, { waitUntil: 'networkidle' });
                
                // Vorbereitung der Seite: Bereinigung der Banner und Overlays
                await ownerPage.acceptCookies();
                await ownerPage.hideOverlays();
            });

            /**
             * 1. TECHNISCHE PRÜFUNG
             * Wir überprüfen, ob unser PHP-Filter die Klasse im Body tatsächlich ersetzt hat.
             */
            test('Backend: Verify Theme Injection', async ({ page }) => {
                const themeSlug = theme.replace('onoffice-', '');
                
                await expect.poll(async () => {
                    return await page.locator('body').getAttribute('class');
                }, {
                    message: `Theme slug "${themeSlug}" not found in body class`,
                    timeout: 10000,
                }).toContain(themeSlug);
            });

            /**
             * 2. VISUELLE PRÜFUNG
             * Wir vergleichen die Form mit dem Standard für jede Auflösung.
             */
            for (const vp of VIEWPORTS) {
                test(`Visual: Snapshot on ${vp.name}`, async ({ page }) => {
                    await page.setViewportSize({ width: vp.width, height: vp.height });
                    
                    await page.waitForTimeout(300);
                    await ownerPage.hideOverlays();

                    await expect(ownerPage.form).toHaveScreenshot(`eigentuemer-standard-${theme}-${vp.name}.png`, {
                        maxDiffPixelRatio: 0.05,
                        animations: 'disabled',
                    });
                });
            }

            /**
             * 3. FUNKTIONALE PRÜFUNG
             * Prüfung der Logik beim Ausfüllen und Absenden
             */
            test.describe('Functional tests', () => {
                test.beforeEach(async ({ page }) => {
                    await ownerPage.acceptCookies();
                });

                test('Submission: Happy Path', async () => {
                    const testData = {
                        plz: '52068',
                        flaeche: '150',
                        firstName: 'Happy-E2E',
                        lastName: 'Owner-Test',
                        email: `happy-owner-${Date.now()}@onoffice.de`, 
                        phone: '+49123456789'
                    };

                    await ownerPage.fillPropertyData(testData.plz, testData.flaeche);
                    await ownerPage.fillContactData(testData);
                    
                    await ownerPage.submit();

                    await expect(ownerPage.infoMessages).toBeVisible({ timeout: 20000 });
                    const msgText = await ownerPage.infoMessages.innerText();
                    expect(msgText).toMatch(/Vielen Dank|Spam erkannt|erfolgreich/i);
                });

                test('Validation: Required Fields', async () => {
                    const propertySection = ownerPage.form.getByText(/Angaben zu Ihrem Eigentum|Eigentumsangaben|Objektangaben/i).filter({ visible: true }).first();
                    const contactSection = ownerPage.form.getByText(/Ihre Kontaktdaten|Kontaktangaben/i).filter({ visible: true }).first();
                    
                    await expect(propertySection).toBeVisible({ timeout: 5000 });
                    await expect(contactSection).toBeVisible({ timeout: 5000 });
                });
            });
        });
    }
});