import { Page, Locator, expect } from '@playwright/test';

export class NewsletterPage {
    readonly page: Page;
    readonly form: Locator;
    readonly emailInput: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;
    readonly firstNameInput: Locator;
    readonly lastNameInput: Locator;

    constructor(page: Page, formSelector: string = '.oo-newsletter-form') { 
        this.page = page;
        this.form = page.locator('form').filter({ has: page.locator('input[name="oo_formid"][value="Newsletter"]') });
        this.firstNameInput = this.form.locator('input[name="Vorname"]');
        this.lastNameInput = this.form.locator('input[name="Name"]');
        this.emailInput = this.form.locator('input[name="Email"]');
        this.submitBtn = this.form.locator('.oo-js-submit-button');
        this.infoMessages = page.locator('.c-info-messages');
    }

    async goto() {
        await this.page.goto('test-modern-formulare/newsletterformular-test/', { waitUntil: 'networkidle' });
    }

    async acceptCookies() {
        const acceptButton = this.page.locator('button:has-text("Alles akzeptieren")');
        try {
            await acceptButton.waitFor({ state: 'visible', timeout: 10000 });
            await acceptButton.click({ force: true });
            await expect(this.page.locator('#usercentrics-root')).not.toBeVisible({ timeout: 15000 });
        } catch (e) {
            console.log('Usercentrics Banner nicht gefunden oder bereits akzeptiert.');
        }
    }

    async fillAllFields(email: string) {
        const activeForm = this.form.filter({ visible: true }).first();
        const activeEmail = activeForm.locator('input[name="Email"]');
        const activeName = activeForm.locator('input[name="Name"]');

        if (await activeName.isVisible()) {
            await activeName.fill('E2E-Tester');
            await activeName.dispatchEvent('blur');
        }
        await activeEmail.fill(email);
        await activeEmail.dispatchEvent('blur');
    }

    async acceptRequiredCheckboxes() {
        const checkboxLabels = this.form.locator('label').filter({ has: this.page.locator('input[required]') });
        const count = await checkboxLabels.count();
        
        for (let i = 0; i < count; i++) {
            await checkboxLabels.nth(i).click({ position: { x: 10, y: 10 } });
            await this.page.waitForTimeout(200);
        }
    }

    async submit() {
        const activeForm = this.form.filter({ visible: true }).first();
        const activeSubmitBtn = activeForm.locator('.oo-js-submit-button');
        
        const altcha = activeForm.locator('input[name="altcha"]');
        if (await altcha.count() > 0) {
            await expect(altcha).not.toHaveValue('', { timeout: 15000 });
        }

        await activeSubmitBtn.click();
    }
}