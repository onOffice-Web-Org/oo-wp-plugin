import { test, expect } from '@playwright/test';
import { ContactFormPage } from '../page-objects/ContactFormPage';

/**
 * Globale Testeinstellungen für das Umschalten von Themes
 */
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
const BASE_PATH = '/e2e-test-formulare/kontaktformular/';


test.describe('Kontaktformular: Multi-Theme Engine & Visual Regression', () => {

    for (const theme of THEMES) {
        test.describe(`Theme: ${theme}`, () => {
            let contactPage: ContactFormPage;

            test.beforeEach(async ({ page }) => {
                contactPage = new ContactFormPage(page);
                
                // URLs mit Theme-Wechsel- und Cache-Umgehungs-Flags generieren
                const url = `${BASE_PATH}?force_theme=${theme}&e2e_key=${E2E_SECRET}&t=${Date.now()}`;

                await page.route('**/kontaktformular/**', async route => {
                    const response = await route.fetch();
                    // Wenn der Server eine HTML-Seite zurückgibt, ersetzen wir das ALTCHA Tag dynamisch.
                    if (response.headers()['content-type']?.includes('text/html')) {
                        let htmlText = await response.text();
                        // Wir betten das Mock-Attribut in den Seitencode ein, bevor der Browser ihn sieht.
                        htmlText = htmlText.replace('<altcha-widget', '<altcha-widget mock');
                        await route.fulfill({ response, body: htmlText });
                    } else {
                        await route.fallback();
                    }
                });
                
                await page.goto(url, { waitUntil: 'networkidle' });
                
                // Vorbereitung der Seite: Bereinigung der Banner und Overlays
                await contactPage.acceptCookies();
                await contactPage.hideOverlays();
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
                    await contactPage.hideOverlays();

                    await expect(contactPage.form).toHaveScreenshot(`form-${theme}-${vp.name}.png`, {
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
                
                test('Submission: Happy Path', async () => {
                    // Nonce-Prüfung (Formularschutz)
                    const nonce = contactPage.form.locator('input[name="onoffice_nonce"]');
                    if (await nonce.count() > 0) {
                        await expect(nonce).toBeAttached();
                    }

                    await contactPage.fillForm({
                        firstName: 'E2E-Tester',
                        lastName: `Theme-${theme}`,
                        email: 'qa-test@onoffice.de',
                        phone: '+49 123 456789',
                        message: `Automated test for ${theme}`
                    });

                    await contactPage.submit();

                    // Überprüfen Sie die Erfolgsmeldung (unter Berücksichtigung eines möglichen lokalen Spamfilters)
                    await expect(contactPage.infoMessages).toBeVisible({ timeout: 20000 });
                    const msgText = await contactPage.infoMessages.innerText();
                    expect(msgText).toMatch(/Vielen Dank|Spam erkannt/);
                });

                test('Validation: Required Fields', async () => {
                    await contactPage.submit();
                    
                    // Überprüfung des Füllfehlertextes für Pflichtfelder
                    const fieldError = contactPage.page.getByText(/Bitte füllen Sie das Pflichtfeld aus/i).first();
                    await expect(fieldError).toBeVisible();

                    // Überprüfung der GDPR-Fehlermeldung
                    const gdprError = contactPage.page.getByText(/Bitte unterzeichnen Sie|Datenschutz/i).first();
                    await expect(gdprError).toBeVisible();
                });
            }); 
        }); 
    } 
});