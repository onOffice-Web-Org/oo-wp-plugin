import { Page, Locator, expect } from '@playwright/test';

export class OwnerFormPage {
    readonly page: Page;
    readonly form: Locator;
    readonly plzInput: Locator;
    readonly areaInput: Locator;
    readonly firstNameInput: Locator;
    readonly lastNameInput: Locator;
    readonly emailInput: Locator;
    readonly phoneInput: Locator;
    readonly gdprCheckbox: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;
    readonly altchaVerified: Locator;

    constructor(page: Page) {
        this.page = page;
        this.form = page.locator('#onoffice-form-1');
        
        this.plzInput = this.form.getByPlaceholder('Plz', { exact: false });
        this.areaInput = this.form.locator('input[name*="wohnflaeche"], input[name*="Wohnfläche"]').first();
        
        this.firstNameInput = this.form.getByPlaceholder('Vorname');
        this.lastNameInput = this.form.locator('input[name="Name"]');
        this.emailInput = this.form.locator('input[name="Email"]');
        this.phoneInput = this.form.locator('input[name*="Telefon"]');
        
        this.gdprCheckbox = this.form.getByLabel(/Datenschutzbestimmungen/i);
        this.submitBtn = this.form.locator('.oo-js-submit-button');
        this.infoMessages = page.locator('.c-info-messages');
        this.altchaVerified = this.form.locator('altcha-widget [state="verified"], .altcha-verified');
    }

    async goto() {
        await this.page.goto('test-modern-formulare/eigentuemerformular-test/', { waitUntil: 'networkidle' });
    }

    async acceptCookies() {
        const acceptButton = this.page.locator('button:has-text("Alles akzeptieren")');
        try {
            await acceptButton.waitFor({ state: 'visible', timeout: 8000 });
            await acceptButton.click({ force: true });
            await expect(this.page.locator('#usercentrics-root')).not.toBeVisible();
        } catch (e) {
            console.log('Usercentrics не найден.');
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

    async fillPropertyData(plz: string, area: string) {
        await this.plzInput.click();
        await this.plzInput.pressSequentially(plz, { delay: 50 });
        await this.plzInput.dispatchEvent('blur');

        await this.areaInput.click();
        await this.areaInput.pressSequentially(area, { delay: 50 });
        await this.areaInput.dispatchEvent('blur');
    }

    async fillContactData(data: { firstName: string, lastName: string, email: string, phone: string }) {
        await this.firstNameInput.click();
        await this.firstNameInput.pressSequentially(data.firstName, { delay: 50 });
        await this.firstNameInput.dispatchEvent('input'); 
        await this.firstNameInput.dispatchEvent('blur');

        await this.lastNameInput.click();
        await this.lastNameInput.pressSequentially(data.lastName, { delay: 50 });
        await this.lastNameInput.dispatchEvent('blur');

        await this.emailInput.fill(data.email);
        await this.phoneInput.fill(data.phone);
        
        const gdprCheckbox = this.gdprCheckbox;
        if (await gdprCheckbox.isVisible()) {
        await gdprCheckbox.check({ force: true });
        }
    }

    async submit() {
        if (await this.form.locator('altcha-widget').isVisible()) {
            await expect(this.altchaVerified).toBeVisible({ timeout: 20000 });
        }
        await this.submitBtn.click();
    }
}