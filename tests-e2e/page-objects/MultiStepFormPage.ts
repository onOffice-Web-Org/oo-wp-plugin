import { Page, Locator, expect } from '@playwright/test';

export class MultiStepFormPage {
    readonly page: Page;
    readonly form: Locator;
    readonly nextBtn: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;
    nameInput: any;

    constructor(page: Page) {
        this.page = page;
        this.form = page.locator('#onoffice-form-1');
        this.nextBtn = this.form.locator('button.leadform-forward');
        this.submitBtn = this.form.locator('button:has-text("Absenden")');
        this.infoMessages = page.locator('.c-info-messages');
    }

    async goto() {
        await this.page.goto('test-modern-formulare/eigentuemerformular-mit-steps/', { waitUntil: 'networkidle' });
    }

    async acceptCookies() {
        const acceptButton = this.page.locator('button:has-text("Alles akzeptieren")');
        try {
            await acceptButton.waitFor({ state: 'visible', timeout: 5000 });
            await acceptButton.click({ force: true });
        } catch (e) {
            console.log('Usercentrics nicht gefunden.');
        }
    }

    async hideOverlays() {
        await this.page.addStyleTag({
            content: `
                #usercentrics-root, .uc-container, [id^="usercentrics"],
                iframe[title*="chat"], .superchat-widget, .c-header.--fixed { 
                    display: none !important; 
                }
            `
        });
        await this.page.waitForTimeout(500);
    }

    async fillStep1Contact(name: string, email: string) {
        await this.form.waitFor({ state: 'visible' });

        const nameField = this.form.locator('input[name="Name"]').filter({ visible: true });
        const emailField = this.form.locator('input[name="Email"]').filter({ visible: true });
        const nextBtn = this.form.locator('button.leadform-forward').filter({ visible: true });
        const step1 = this.form.locator('.c-form__step').first();

        await nameField.click();
        await nameField.pressSequentially('T', { delay: 100 });
        await this.page.waitForTimeout(1000); 

        await nameField.click();
        await nameField.press('Control+A');
        await nameField.press('Backspace');
        await nameField.fill(name);
        await nameField.press('Tab'); 
        await nameField.dispatchEvent('change');

        await emailField.click();
        await emailField.fill(email);
        await emailField.press('Tab');
        await emailField.dispatchEvent('change');

        await this.page.waitForTimeout(500); 
        await nextBtn.click();

        await expect(step1).toBeHidden({ timeout: 10000 });
    }

    async fillStep2Interests(data: { area: string, rooms: string, plz: string, city: string }) {
        const stepHeader = this.form.getByText(/2\s*\|\s*Ihre Interessen/i);
        await stepHeader.waitFor({ state: 'visible', timeout: 10000 });

        await this.page.waitForTimeout(1000);

        const plzField = this.form.locator('input[name*="plz" i]').filter({ visible: true }).first();
        await plzField.waitFor({ state: 'visible' });
        await plzField.click({ force: true });
        await plzField.pressSequentially('1', { delay: 100 });
        await this.page.waitForTimeout(1000);

        const houseBtn = this.form.locator('button, .o-button-group__item').filter({ hasText: /Haus/i }).filter({ visible: true }).first();
        await houseBtn.evaluate(node => (node as HTMLElement).click());
        
        await this.page.waitForTimeout(1000);

        const fillAndFix = async (locator: Locator, value: string) => {
            const el = locator.filter({ visible: true }).first();
            await el.click();
            await el.fill(''); 
            await el.fill(value);
            await el.press('Tab');
            await el.dispatchEvent('change');
        };

        await fillAndFix(this.form.getByLabel(/Wohnfläche/i), data.area);
        await fillAndFix(this.form.getByLabel(/Anzahl Zimmer/i), data.rooms);
        
        await plzField.click({ force: true });
        await plzField.press('Control+A');
        await plzField.press('Backspace');
        await fillAndFix(plzField, data.plz);
        
        await fillAndFix(this.form.locator('input[name*="ort" i]'), data.city);

        const vermarktung = this.form.getByLabel(/Vermarktungsart/i).filter({ visible: true });
        await vermarktung.click();
        const option = this.page.locator('.ts-dropdown .option, .option').filter({ hasText: /^Kauf$/ });
        await option.waitFor({ state: 'visible' });
        await option.click();

        await this.page.waitForTimeout(500);
        await this.form.locator('button.leadform-forward').filter({ visible: true }).click();
        
        await expect(this.form.getByText(/3\s*\|\s*Nachricht/i)).toBeVisible();
    }

    async fillStep3Message(message: string) {
        await this.form.getByPlaceholder('Nachricht').fill(message);
        await this.nextBtn.click();
        await expect(this.form.getByText('4 | Compliance')).toBeVisible();
    }

    async fillStep4Compliance() {
        await this.form.locator('label').filter({ hasText: /Datenschutzerklärung/i }).click();
        await this.form.locator('label').filter({ hasText: /Widerrufsbelehrung/i }).click();
    }

    async submit() {
        const submitBtn = this.form.locator('button:has-text("Absenden")');
        await submitBtn.scrollIntoViewIfNeeded();
        await submitBtn.click({ force: true });
    }
}