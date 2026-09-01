import { Page, Locator, expect } from '@playwright/test';

export class OwnerFormPage {
    readonly page: Page;
    readonly form: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;
    readonly altchaVerified: Locator;

    constructor(page: Page) {
        this.page = page;
        this.form = page.locator('form, #onoffice-form-1').filter({ has: page.locator('input[name="oo_formid"]') }).first();
        
        this.submitBtn = this.form.getByRole('button', { name: /Absenden/i }).or(this.form.locator('.oo-js-submit-button, button[type="submit"]'));
        this.infoMessages = page.locator('.onoffice-form-alerts, .status-message, div[class*="message"], div[class*="alert"], .c-info-messages').first();
        
        this.altchaVerified = this.form.locator('altcha-widget [state="verified"], .altcha-verified');
    }

    private async typeSafe(locator: Locator, value: string) {
        const target = locator.filter({ visible: true }).first();
        await target.waitFor({ state: 'visible', timeout: 5000 });
        await target.focus();
        await target.press('Control+A');
        await target.press('Backspace');
        await target.pressSequentially(value, { delay: 30 });
        
        await target.dispatchEvent('input');
        await target.dispatchEvent('change');
        await target.dispatchEvent('blur');
    }

    async goto() {
        await this.page.goto('/e2e-test-formulare/eigentuemerformular/', { waitUntil: 'networkidle' });
    }

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
                };
                resetStyles(document.documentElement);
                resetStyles(document.body);
            };
            cleanUp();
            const interval = setInterval(cleanUp, 500);
            setTimeout(() => clearInterval(interval), 2000);
        });
    }

    async hideOverlays() {
        await this.page.addStyleTag({
            content: `
                #wpadminbar, #usercentrics-root, .uc-container, [id^="usercentrics"],
                iframe[title*="chat"], .superchat-widget, .c-header.--fixed { 
                    display: none !important; 
                    visibility: hidden !important; 
                }
            `
        });
        await this.page.waitForTimeout(300);
    }

    async fillPropertyData(plz: string, area: string) {
        const activeForm = this.form.filter({ visible: true }).first();
    
        const plzField = activeForm.locator('input[name*="plz" i]')
            .or(activeForm.getByPlaceholder(/plz/i))
            .or(activeForm.getByLabel(/plz/i));
            
        const areaField = activeForm.locator('input[name*="flaeche" i], input[name*="fläche" i]')
            .or(activeForm.getByPlaceholder(/wohnfläche|flaeche/i))
            .or(activeForm.getByLabel(/wohnfläche|flaeche/i));

        await this.typeSafe(plzField, plz);
        await this.typeSafe(areaField, area);
    }

    async fillContactData(data: { firstName: string, lastName: string, email: string, phone: string }) {
        const activeForm = this.form.filter({ visible: true }).first();

        const firstNameInput = activeForm.getByLabel(/^Vorname/i).first(); // Строгое начало слова, без учета регистра
        const lastNameInput = activeForm.getByLabel(/^Nachname/i).first();
        const emailInput = activeForm.getByLabel(/^E-Mail/i).first();
        const phoneInput = activeForm.getByLabel(/^Telefon/i).first();   

        await this.typeSafe(firstNameInput, data.firstName);
        await this.typeSafe(lastNameInput, data.lastName);
        await this.typeSafe(emailInput, data.email);
        await this.typeSafe(phoneInput, data.phone);

        const checkboxes = activeForm.locator('input[type="checkbox"]');
        const count = await checkboxes.count();
        for (let i = 0; i < count; i++) {
            const cb = checkboxes.nth(i);
            await cb.check({ force: true });
            await cb.dispatchEvent('input');
            await cb.dispatchEvent('change');
        }
        await this.page.waitForTimeout(1500);
    }

    async submit() {
        const activeForm = this.form.filter({ visible: true }).first();
        
        if (await activeForm.locator('altcha-widget').isVisible()) {
            await expect(this.altchaVerified).toBeVisible({ timeout: 20000 });
        }
        
        const activeSubmitBtn = activeForm.locator('button:has-text("Absenden"), button[type="submit"]').or(this.submitBtn).filter({ visible: true }).first();
        await activeSubmitBtn.scrollIntoViewIfNeeded();
        await activeSubmitBtn.click({ force: true });
    }
}