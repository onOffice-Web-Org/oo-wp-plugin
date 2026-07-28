import { test, expect } from '@playwright/test';
import { InterestsFormPage } from '../page-objects/InterestsFormPage';

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
const BASE_PATH = '/e2e-test-formulare/interessentenformular/';

for (const theme of THEMES) {
        test.describe(`Theme: ${theme}`, () => {
            let interestsPage: InterestsFormPage;

            test.beforeEach(async ({ page }) => {
                interestsPage = new InterestsFormPage(page);
                
                // URLs mit Theme-Wechsel- und Cache-Umgehungs-Flags generieren
                const url = `${BASE_PATH}?force_theme=${theme}&e2e_key=${E2E_SECRET}&t=${Date.now()}`;

                await page.route('**/interessentenformular/**', async route => {
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
                await interestsPage.acceptCookies();
                await interestsPage.hideOverlays();
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
                    await interestsPage.hideOverlays();

                    await expect(interestsPage.form).toHaveScreenshot(`interessenten-form-${theme}-${vp.name}.png`, {
                        maxDiffPixelRatio: 0.05,
                        animations: 'disabled',
                    });
                });
            }
            
            /**
             * 3. FUNKTIONALE PRÜFUNG
             * Prüfung der Logik beim Ausfüllen und Absenden
             */
            test.describe('Functional Scenarios', () => {
                   test('Submission: Happy Path', async ({ page }) => {
                        await interestsPage.acceptCookies();

                        const testData = {
                            firstName: 'E2E-Mapping',
                            lastName: 'Test-User',
                            email: `happy-interessent-${Date.now()}@onoffice.de`,
                            phone: '+49123456789',
                            message: 'Automatisierter E2E Test der Interessentenformular'
                        };

                        await interestsPage.checkAllRequiredCheckboxes();
                        await interestsPage.fillSearchCriteria('4', '500000');
                        await interestsPage.fillContactInfo(testData);

                        await interestsPage.submit();

                        await expect(interestsPage.infoMessages).toBeVisible({ timeout: 20000 });
                        const text = await interestsPage.infoMessages.innerText();
                        expect(text).toMatch(/Vielen Dank|Spam erkannt|erfolgreich/i);
                    });

            /**
             * 4. FUNKTIONALE PRÜFUNG
             * Prüfung der Validierung beim Absenden ohne Eingaben
             */
                    test('Validation: Required Fields', async ({ page }) => {
                        await interestsPage.acceptCookies();
                        
                        await interestsPage.submit();

                        const errorText = interestsPage.form
                            .getByText(/Pflichtfeld|Bitte füllen Sie|erforderlich/i)
                            .filter({ visible: true })
                            .first();
                        
                        await expect(errorText).toBeVisible({ timeout: 7000 });

                        // Prüfen wir die Anwesenheit roter Klassen in der Markup für Fehlermeldungen auf den Feldern
                        const invalidMarkers = interestsPage.form.locator('[class*="invalid"], [class*="error"], .is-invalid, .has-error');
                        const count = await invalidMarkers.count();
                        expect(count).toBeGreaterThan(0);
                    });
        });
    });
}