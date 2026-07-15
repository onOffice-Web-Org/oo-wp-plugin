import { Page, Locator, expect } from '@playwright/test';

export class InterestsFormPage {
    readonly page: Page;
    readonly form: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;

    constructor(page: Page) {
        this.page = page;
        this.form = page.locator('form, #onoffice-form-1').filter({ has: page.locator('input[name="oo_formid"]') }).first();
        
        this.submitBtn = this.form.getByRole('button', { name: /Absenden/i }).or(this.form.locator('button[type="submit"]'));
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

    async goto(): Promise<void> {
        await this.page.goto('/e2e-test-formulare/interessentenformular/', { waitUntil: 'networkidle' });
    }

    async acceptCookies(): Promise<void> {
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

    async hideOverlays(): Promise<void> {
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

    async checkAllRequiredCheckboxes(): Promise<void> {
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

    async fillSearchCriteria(rooms: string, price: string): Promise<void> {
        const activeForm = this.form.filter({ visible: true }).first();
        
        const roomsInput = activeForm.locator('input[name*="zimmer" i], input[id*="zimmer" i]')
            .or(activeForm.getByPlaceholder(/zimmer/i))
            .or(activeForm.getByLabel(/zimmer/i));
            
        const priceInput = activeForm.locator('input[name*="preis" i], input[id*="preis" i], input[name*="kaufpreis" i]')
            .or(activeForm.getByPlaceholder(/preis/i))
            .or(activeForm.getByLabel(/preis/i));

        await this.typeSafe(roomsInput, rooms);
        await this.typeSafe(priceInput, price);
    }

    async fillContactInfo(data: { firstName: string; lastName: string; email: string; phone: string; message: string }): Promise<void> {
        const activeForm = this.form.filter({ visible: true }).first();

        const firstNameInput = activeForm.getByLabel(/^Vorname/i).first(); 
        const lastNameInput = activeForm.getByLabel(/^Nachname/i).first();
        const emailInput = activeForm.getByLabel(/^E-Mail/i).first();
        const phoneInput = activeForm.getByLabel(/^Telefon/i).first();
        const messageInput = activeForm.getByLabel(/Nachricht/i).first();

        await this.typeSafe(firstNameInput, data.firstName);
        await this.typeSafe(lastNameInput, data.lastName);
        
        await this.typeSafe(emailInput, data.email);
        await this.typeSafe(phoneInput, data.phone);

        if (await messageInput.filter({ visible: true }).count() > 0) {
            await this.typeSafe(messageInput, data.message);
        }
    }

    async submit(): Promise<void> {
        const activeForm = this.form.filter({ visible: true }).first();
        const activeSubmitBtn = activeForm.locator('button:has-text("Absenden"), button[type="submit"]').or(this.submitBtn).filter({ visible: true }).first();
        
        await activeSubmitBtn.scrollIntoViewIfNeeded();
        await activeSubmitBtn.click({ force: true });
    }
}