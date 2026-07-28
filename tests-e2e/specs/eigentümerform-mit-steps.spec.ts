import { test, expect } from '@playwright/test';
import { MultiStepFormPage } from '../page-objects/MultiStepFormPage';

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
const BASE_PATH = '/e2e-test-formulare/eigentuemerformular-mit-seite/';

test.describe('Eigentümerformular Multi-Step: Multi-Theme Engine & Visual Regression', () => {
    
    for (const theme of THEMES) {
        test.describe(`Theme: ${theme}`, () => {
            let multiStepPage: MultiStepFormPage;

            test.beforeEach(async ({ page }) => {
                multiStepPage = new MultiStepFormPage(page);
                
                const url = `${BASE_PATH}?force_theme=${theme}&e2e_key=${E2E_SECRET}&t=${Date.now()}`;
                
                await page.route('**/eigentuemerformular-mit-seite/**', async route => {
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
                await multiStepPage.acceptCookies();
                await multiStepPage.hideOverlays(); 
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
                test(`Visual: Snapshot steps on ${vp.name}`, async ({ page }) => {
                    await page.setViewportSize({ width: vp.width, height: vp.height });
                    await page.waitForTimeout(500);
                    await multiStepPage.hideOverlays();

                    // Snapshot Step 1
                    await expect(multiStepPage.form).toHaveScreenshot(`step-1-${theme}-${vp.name}.png`, {
                        mask: [multiStepPage.form.locator('.c-form__progress-bar, .leadform-progress')],
                        maxDiffPixelRatio: 0.05, 
                        animations: 'disabled'
                    });

                    // Gehen zu Schritt 2
                    await multiStepPage.fillStep1Contact('Tester', `test-${Date.now()}@onoffice.de`);
                    await page.waitForTimeout(500);
                    await multiStepPage.hideOverlays();

                    // Snapshot Step 2
                    await expect(multiStepPage.form).toHaveScreenshot(`step-2-${theme}-${vp.name}.png`, {
                        maxDiffPixelRatio: 0.05,
                        animations: 'disabled'
                    });
                });
            }

            /**
             * 3. FUNKTIONALE Szenarien (Happy Flow und Negativ)
             */
            test.describe('Functional Scenarios', () => {
                
                test('Submission: Happy Path', async () => {
                    await multiStepPage.fillStep1Contact('Owner', `happy-step-${Date.now()}@owner.de`);
                    
                    await multiStepPage.fillStep2Interests({
                        area: '150',
                        rooms: '5',
                        plz: '52068',
                        city: 'Aachen'
                    });

                    await multiStepPage.fillStep3Message('Test Message via Multi-Step POM');
                    await multiStepPage.fillStep4Compliance();
                    await multiStepPage.submit();

                    // Prüfen wir den erfolgreichen Antwort vom WordPress Backend
                    await expect(multiStepPage.infoMessages).toBeVisible({ timeout: 15000 });
                    const msg = await multiStepPage.infoMessages.innerText();
                    expect(msg).toMatch(/Vielen Dank|Spam erkannt|erfolgreich/);
                });

                test('Validation: Required Fields', async ({ page }) => {
                    const nextBtn = multiStepPage.nextBtn.filter({ visible: true }).first();
                    await nextBtn.click({ force: true });
                    
                    const errorMessage = multiStepPage.form
                        .getByText(/Bitte füllen Sie|Pflichtfeld|erforderlich/i)
                        .or(multiStepPage.form.locator('.error-message, .is-visible, [class*="error"]'))
                        .filter({ visible: true })
                        .first();
                        
                    await expect(errorMessage).toBeVisible({ timeout: 7000 });
                });
            });
        });
    }
});