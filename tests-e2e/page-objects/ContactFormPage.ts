import { Page, Locator, expect } from '@playwright/test';

export class ContactFormPage {
    readonly page: Page;
    readonly form: Locator;
    readonly firstNameInput: Locator;
    readonly lastNameInput: Locator;
    readonly emailInput: Locator;
    readonly phoneInput: Locator;
    readonly messageInput: Locator;
    readonly gdprCheckbox: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;

    constructor(page: Page) {
        this.page = page;
        
        // Formularsuche: Finden das Formular, das unsere ID enthält.
        this.form = page.locator('form').filter({ has: page.locator('input[name="oo_formid"]') }).first();
        
        // Eingabefelder
        this.firstNameInput = this.form.locator('input[name="Vorname"]');
        this.lastNameInput = this.form.locator('input[name="Name"]'); 
        this.emailInput = this.form.locator('input[name="Email"]');
        this.phoneInput = this.form.locator('input[name*="Telefon"]');
        this.messageInput = this.form.locator('textarea[name*="tmpField"], textarea[name*="Message"]');
        this.gdprCheckbox = this.form.locator('input[name*="gdprcheckbox"]');
        
        // Buttons und Nachrichten
        this.submitBtn = this.form.locator('button.oo-js-submit-button, button:has-text("Absenden")').first();
        this.infoMessages = page.locator('.c-info-messages');
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

    /**
     * Sicherer Formularbelegung mit Simulierung des Tippen
     */
    async fillForm(data: { firstName: string, lastName: string, email: string, phone: string, message: string }) {
        await this.form.waitFor({ state: 'visible', timeout: 10000 });

        const typeSafe = async (locator: Locator, value: string) => {
            await locator.waitFor({ state: 'visible', timeout: 5000 });
            await locator.focus();
            await locator.press('Control+A');
            await locator.press('Backspace');
            await locator.pressSequentially(value, { delay: 30 }); 
            
            await locator.dispatchEvent('change');
            await locator.dispatchEvent('blur');
        };
        
        await typeSafe(this.firstNameInput, data.firstName);
        await typeSafe(this.lastNameInput, data.lastName);
        await typeSafe(this.emailInput, data.email);
        await typeSafe(this.phoneInput, data.phone);
        await typeSafe(this.messageInput, data.message);

        const gdprCheckbox = this.form.locator('input[type="checkbox"][name*="gdpr"]').first();
        await gdprCheckbox.check({ force: true });
        await gdprCheckbox.dispatchEvent('change'); 
        await expect(gdprCheckbox).toBeChecked();
        await this.page.waitForTimeout(500);
    }

    async submit() {
        await this.submitBtn.waitFor({ state: 'visible' });
        await Promise.all([
        this.page.waitForLoadState('networkidle'),
        this.submitBtn.click({ force: true })
    ]);

    }
}
