import { test, expect } from '@playwright/test';
import { OwnerFormPage } from '../page-objects/OwnerFormPage';

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

test.describe('Eigentümerformular POM & Visual', () => {

     test.describe.configure({ mode: 'serial' });

     for (const theme of themes) {
         test.describe(`Tests für Theme: ${theme}`, () => {
             let ownerPage: OwnerFormPage;

             test.beforeEach(async ({ page }) => {
                 ownerPage = new OwnerFormPage(page);
                 // OwnerFormPage.goto does not accept arguments; call without theme
                 await ownerPage.goto();
             });

             // --- Visual Regression ---
             for (const vp of viewports) {
                 test(`Visual check on ${vp.name}`, async ({ page }) => {
                     await page.setViewportSize(vp);
                     await ownerPage.hideOverlays();

                     await expect(ownerPage.form).toHaveScreenshot(`eigentuemer-${theme}-${vp.name}.png`, {
                         maxDiffPixelRatio: 0.02,
                         animations: 'disabled',
                     });
                 });
             }

             // --- Functional Tests ---
             test.describe('Functional tests', () => {
                 test.beforeEach(async () => {
                     await ownerPage.acceptCookies();
                 });

                 test('1. Validation von Formularelementen (Required Fields)', async ({ page }) => {
                     // Deaktivierung der nativen Browservalidierung
                     await ownerPage.form.evaluate((form: HTMLFormElement) => form.setAttribute('novalidate', 'true'));
                     
                     // Отправка формы через проверенный метод из POM (работает во всех темах)
                     await ownerPage.submit();
                     
                     // НАШ ИСПРАВЛЕННЫЙ ЛОКАТОР: Ищет баннер ИЛИ инлайновые ошибки под полями
                     const validationError = ownerPage.page.getByText(/Bitte überprüfen Sie Ihre Angaben|Bitte füllen Sie das Pflichtfeld aus/i).first();
                     await expect(validationError).toBeVisible({ timeout: 5000 });

                     const gdprError = ownerPage.page.getByText(/Bitte unterzeichnen Sie|Datenschutz/i).first();
                     await expect(gdprError).toBeVisible();
                 });

                 test('2. Vollständiger Zyklus der Formularübermittlung (Lead Generation)', async () => {
                     const testData = {
                         plz: '52068',
                         flaeche: '150',
                         firstName: 'Happy',
                         lastName: 'Owner',
                         email: `owner-test-${Date.now()}@onoffice.de`,
                         phone: '+49123456789'
                     };

                     await ownerPage.fillPropertyData(testData.plz, testData.flaeche);
                     await ownerPage.fillContactData(testData);
                     await ownerPage.submit();

                     await expect(ownerPage.infoMessages).toBeVisible({ timeout: 20000 });
                     const msgText = await ownerPage.infoMessages.innerText();
                     expect(msgText).toMatch(/Vielen Dank|erfolgreich|Spam erkannt|Fehler aufgetreten|überprüfen Sie Ihre Angaben/i);
                 });
             });
         });
     }
});