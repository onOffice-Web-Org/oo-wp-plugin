import { test, expect } from '@playwright/test';
import { NewsletterPage } from '../page-objects/NewsletterPage';

/**
 * Globale Testeinstellungen für das Umschalten von Themes und Viewports
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
const BASE_PATH = '/test-modern-formulare/newsletter/';

test.describe('Newsletter Formular: Multi-Theme Engine & Visual Regression', () => {

    for (const theme of THEMES) {
        test.describe(`Theme: ${theme}`, () => {
            let newsletterPage: NewsletterPage;

            test.beforeEach(async ({ page }) => {
                newsletterPage = new NewsletterPage(page);
                
                // URL-Generierung mit schnellem Themenwechsel und controllerlosem Sicherheitsschlüssel
                const url = `${BASE_PATH}?force_theme=${theme}&e2e_key=${E2E_SECRET}&t=${Date.now()}`;
                await page.goto(url, { waitUntil: 'networkidle' });
                
                // Vorbereitung der Seite über das Page Object
                await newsletterPage.acceptCookies();
                await newsletterPage.hideOverlays();
            });

            /**
             * 1. TECHNISCHE PRÜFUNG
             * Wir überprüfen, ob unser PHP-Filter die Theme-Klasse im Body-Tag erfolgreich ersetzt hat.
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
             * Wir vergleichen die Gestaltung des Newsletter-Formulars mit Referenz-Screenshots.
             */
            for (const vp of VIEWPORTS) {
                test(`Visual: Snapshot on ${vp.name}`, async ({ page }) => {
                    await page.setViewportSize({ width: vp.width, height: vp.height });
                    
                    await page.waitForTimeout(300);
                    await newsletterPage.hideOverlays();

                    // Wir erstellen einen Screenshot des Formulars mithilfe eines Elements aus dem Seitenobjekt.
                    await expect(newsletterPage.form).toHaveScreenshot(`newsletter-form-${theme}-${vp.name}.png`, {
                        maxDiffPixelRatio: 0.02,
                        animations: 'disabled',
                    });
                });
            }

            /**
             * 3. FUNKTIONALE PRÜFUNG
             * Wir überprüfen die Logik der Felder und die Übermittlung des Newsletter-Formulars.
             */
            test.describe('Functional Scenarios', () => {
                
                test('Submission: Happy Path', async () => {
                    const emailInput = newsletterPage.form.locator('input[type="email"]').first();
                    
                    // Wir überprüfen, ob der Email-Input sichtbar ist
                    await expect(emailInput).toBeVisible();
                    await emailInput.fill(`newsletter-test-${Date.now()}@onoffice.de`);
                    
                    // Wir suchen die Absende-Taste (universell für verschiedene Themes)
                    const submitBtn = newsletterPage.form.getByRole('button', { name: /Absenden|Eintragen|Submit/i })
                        .or(newsletterPage.form.locator('button[type="submit"]'))
                        .first();
                        
                    await submitBtn.click({ force: true });
                    
                    // Oder wir erwarten einen erfolgreichen Antworttext
                    await expect(newsletterPage.page.getByText(/Vielen Dank|erfolgreich/i)).toBeVisible({ timeout: 10000 });
                });

                test('Newsletter: Email Validation', async () => {
                    const emailInput = newsletterPage.form.locator('input[type="email"]').first();
                    
                    await expect(emailInput).toBeVisible();
                    await emailInput.fill('invalid-email');
                    
                    const submitBtn = newsletterPage.form.getByRole('button', { name: /Absenden|Eintragen|Submit/i })
                        .or(newsletterPage.form.locator('button[type="submit"]'))
                        .first();
                        
                    await submitBtn.click({ force: true });
                    
                    // Der Validierungsfehler sollte angezeigt werden
                    await expect(newsletterPage.page.getByText(/gültig|invalid/i)).toBeVisible({ timeout: 5000 });
                });
            });
        });
    }
});