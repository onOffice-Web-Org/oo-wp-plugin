import { Page, Locator, expect } from '@playwright/test';

export class MultiStepFormPage {
    readonly page: Page;
    readonly form: Locator;
    readonly nextBtn: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;

    constructor(page: Page) {
        this.page = page;
        this.form = page.locator('form, #onoffice-form-1').filter({ has: page.locator('input[name="oo_formid"]') }).first();
        this.nextBtn = this.form.getByRole('button', { name: /Weiter/i }).or(this.form.locator('.leadform-forward'));
        this.submitBtn = this.form.getByRole('button', { name: /Absenden/i });
        this.infoMessages = page.locator('.onoffice-form-alerts, .status-message, div[class*="message"], div[class*="alert"], .c-info-messages').first();
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
                #usercentrics-root, .uc-container, [id^="usercentrics"],
                iframe[title*="chat"], .superchat-widget, .c-header.--fixed { 
                    display: none !important; 
                    visibility: hidden !important; 
                }
            `
        });
    }

    async fillStep1Contact(name: string, email: string) {
        const activeForm = this.form.filter({ visible: true }).first();
        await activeForm.waitFor({ state: 'visible' });

        const nameField = activeForm.locator('input[name="Name"]').first();
        const emailField = activeForm.locator('input[name="Email"], input[type="email"]').first();
        const next = activeForm.locator('button.leadform-forward').filter({ visible: true }).first();
        const step1Marker = activeForm.locator('.c-form__step').first();

        await this.typeSafe(nameField, name);
        await this.typeSafe(emailField, email);

        await this.page.waitForTimeout(300); 
        await this.nextBtn.filter({ visible: true }).first().click({ force: true });

        if (await step1Marker.count() > 0) {
            await expect(step1Marker).toBeHidden({ timeout: 10000 });
        }
    }

    async fillStep2Interests(data: { area: string, rooms: string, plz: string, city: string }) {
        const activeForm = this.form.filter({ visible: true }).first();
        
        const stepHeader = activeForm.getByText(/Ihre Interessen/i);
        await stepHeader.waitFor({ state: 'visible', timeout: 10000 });

        const plzField = activeForm.locator('input[name*="plz" i]')
            .or(activeForm.getByPlaceholder(/plz/i))
            .or(activeForm.getByLabel(/plz/i));
        
        const houseBtn = activeForm.locator('button, .o-button-group__item, label').filter({ hasText: /Haus/i }).filter({ visible: true }).first();
        await houseBtn.click({ force: true });
        await this.page.waitForTimeout(300);

        await this.typeSafe(activeForm.getByLabel(/Wohnfläche/i).or(activeForm.locator('input[name*="flaeche" i]')), data.area);
        await this.typeSafe(activeForm.getByLabel(/Anzahl Zimmer/i).or(activeForm.locator('input[name*="zimmer" i]')), data.rooms);
        await this.typeSafe(plzField, data.plz);
        await this.typeSafe(activeForm.locator('input[name*="ort" i]'), data.city);

        await this.page.waitForTimeout(300);
        
        await this.nextBtn.filter({ visible: true }).first().click({ force: true });
    }

    async fillStep3Message(message: string) {
        const activeForm = this.form.filter({ visible: true }).first();
        const textarea = activeForm.locator('textarea, [placeholder*="Nachricht"]').first();
        await this.typeSafe(textarea, message);
        
        await this.nextBtn.filter({ visible: true }).first().click({ force: true });
    }

    async fillStep4Compliance() {
        const activeForm = this.form.filter({ visible: true }).first();
        
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
        const activeSubmitBtn = this.submitBtn.filter({ visible: true }).first();
        await activeSubmitBtn.scrollIntoViewIfNeeded();
        await activeSubmitBtn.click({ force: true });
    }
}