import { Page, Locator, expect } from '@playwright/test';

export class NewsletterPage {
    readonly page: Page;
    readonly form: Locator;
    readonly emailInput: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;
    readonly firstNameInput: Locator;
    readonly lastNameInput: Locator;

    constructor(page: Page) { 
        this.page = page;
        
        // Находим базовый локатор для формы новостей
        this.form = page.locator('form').filter({ has: page.locator('input[name="oo_formid"][value*="Newsletter"]') }).first();
        
        this.firstNameInput = this.form.locator('input[name="Vorname"]');
        this.lastNameInput = this.form.locator('input[name="Name"]');
        this.emailInput = this.form.locator('input[name="Email"], input[type="email"]').first();
        this.submitBtn = this.form.locator('.oo-js-submit-button, button[type="submit"]');
        this.infoMessages = page.locator('.onoffice-form-alerts, .status-message, div[class*="message"], div[class*="alert"], .c-info-messages').first();
    }

    /**
     * Cookie-Banner und Entsperrung der Benutzeroberfläche
     */
    async acceptCookies() {
        await this.page.evaluate(() => {
            (window as any).UC_UI_SUPPRESS_CMP_DISPLAY = true;
            const cleanUp = () => {
                const blockers = document.querySelectorAll('#usercentrics-root, [id^="usercentrics"], .uc-overlay, .uc-container');
                blockers.forEach(el => el.remove());

                const resetStyles = (el: HTMLElement) => {
                    if (!el) return;
                    el.style.setProperty('overflow', 'auto', 'important');
                    el.style.setProperty('pointer-events', 'auto', 'important');
                    el.style.setProperty('position', 'static', 'important');
                    el.style.setProperty('height', 'auto', 'important');
                };
                
                resetStyles(document.documentElement);
                resetStyles(document.body);
            };
            cleanUp();
            const interval = setInterval(cleanUp, 500);
            setTimeout(() => clearInterval(interval), 3000);
        });
    }

    /**
     * Ausblenden unnötiger Elemente (Chat, fixierte Header) für übersichtlichere Screenshots
     */
    async hideOverlays() {
        await this.page.addStyleTag({ 
            content: `
                #usercentrics-root, .uc-container, [id^="usercentrics"],
                iframe[title*="chat"], .superchat-widget, .c-header.--fixed { 
                    display: none !important; 
                    visibility: hidden !important; 
                }
            ` 
        });
    }

    private getActiveForm() {
        return this.form.filter({ visible: true }).first();
    }

    async fillAllFields(email: string) {
        const activeForm = this.getActiveForm();
        
        const typeSafe = async (locator: Locator, value: string) => {
            await locator.waitFor({ state: 'visible', timeout: 5000 });
            await locator.focus();
            await locator.press('Control+A');
            await locator.press('Backspace');
            await locator.pressSequentially(value, { delay: 40 }); 
            
            await locator.dispatchEvent('input');
            await locator.dispatchEvent('change');
            await locator.dispatchEvent('blur');
        };

        const activeEmail = activeForm.locator('input[name="Email"], input[type="email"]').first();
        const activeFirstName = activeForm.locator('input[name="Vorname"]').first();
        const activeLastName = activeForm.locator('input[name="Name"]').first();

        if (await activeFirstName.count() > 0 && await activeFirstName.isVisible()) {
            await typeSafe(activeFirstName, 'E2E');
        }
        if (await activeLastName.count() > 0 && await activeLastName.isVisible()) {
            await typeSafe(activeLastName, 'Tester');
        }
        
        await typeSafe(activeEmail, email);
    }

    async acceptRequiredCheckboxes() {
        const activeForm = this.getActiveForm();
        
        const newsletterLabel = activeForm.locator('label').filter({ hasText: 'Newsletter anmelden' }).first();
        const privacyLabel = activeForm.locator('label').filter({ hasText: 'Datenschutzbestimmungen' }).first();
        
        if (await newsletterLabel.count() > 0) {
            await newsletterLabel.click({ force: true });
            await this.page.waitForTimeout(200); // Даем микропаузу теме отобразить галочку
        }
        
        if (await privacyLabel.count() > 0) {
            await privacyLabel.click({ force: true });
            await this.page.waitForTimeout(200);
        }

        const checkboxes = activeForm.locator('input[type="checkbox"]');
        const count = await checkboxes.count();
        for (let i = 0; i < count; i++) {
            const cb = checkboxes.nth(i);
            await cb.dispatchEvent('input');
            await cb.dispatchEvent('change');
        }
        
        await this.page.waitForTimeout(500);
    }

    async submit() {
        const activeForm = this.getActiveForm();
        const activeSubmitBtn = activeForm.locator('.oo-js-submit-button, button[type="submit"]').first();
        
        await this.page.waitForTimeout(1500);
        await activeSubmitBtn.click({ force: true });
    }
}