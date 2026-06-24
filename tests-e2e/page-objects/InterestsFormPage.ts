import { Page, Locator } from '@playwright/test';

export class InterestsFormPage {
    readonly page: Page;
    readonly form: Locator;
    readonly submitBtn: Locator;
    readonly infoMessages: Locator;

    constructor(page: Page) {
        this.page = page;
        // Главный контейнер формы
        this.form = page.locator('#onoffice-form-1').first();
        
        // Кнопка отправки формы (используем роль для гибкости между темами)
        this.submitBtn = this.form.getByRole('button', { name: /Absenden/i }).first();
        
        // Локатор для баннеров успешной отправки или ошибок бэкенда
        this.infoMessages = page.locator('.onoffice-form-alerts, .status-message, div[class*="message"], div[class*="alert"]').first();
    }

    /**
     * Открытие страницы формы интересанта с ключом защиты
     */
    async goto(): Promise<void> {
        await this.page.goto('e2e-test-formulare/interessentenformular/?e2e_key=qa_rocks');
    }

    /**
     * Принятие кук / согласий, если они перекрывают экран
     */
    async acceptCookies(): Promise<void> {
        const cookieButton = this.page.getByRole('button', { name: /accept|alle akzeptieren|einverstanden/i }).first();
        if (await cookieButton.isVisible({ timeout: 2000 })) {
            await cookieButton.click();
        }
    }

    /**
     * Скрытие панели WordPress администратора перед скриншотами
     */
    async hideOverlays(): Promise<void> {
        const adminBar = this.page.locator('#wpadminbar');
        if (await adminBar.isVisible()) {
            await adminBar.evaluate((el) => el.style.display = 'none');
        }
    }

    /**
     * Проставление всех обязательных чекбоксов (например, DSGVO/Datenschutz)
     */
    async checkAllRequiredCheckboxes(): Promise<void> {
        const checkboxes = this.form.getByRole('checkbox');
        const count = await checkboxes.count();
        for (let i = 0; i < count; i++) {
            await checkboxes.nth(i).check({ force: true });
        }
    }

    /**
     * Заполнение критериев поиска (например, комнаты и бюджет)
     */
    async fillSearchCriteria(rooms: string, price: string): Promise<void> {
        // Ищем инпуты по именам, id или типам (умный фоллбек)
        const roomsInput = this.form.locator('input[name*="zimmer"], input[id*="zimmer"], spinbutton').first();
        const priceInput = this.form.locator('input[name*="preis"], input[id*="kaufpreis"], input[id*="preis"]').first();

        if (await roomsInput.isVisible()) await roomsInput.fill(rooms);
        if (await priceInput.isVisible()) await priceInput.fill(price);
    }

    /**
     * Заполнение контактных данных пользователя
     */
    async fillContactInfo(data: { firstName: string; lastName: string; email: string; phone: string; message: string }): Promise<void> {
        // Используем комбинацию поиска по тексту/лейблу и по атрибутам name для стабильности на разных темах
        await this.form.locator('input[placeholder*="Vorname"], input[name*="vorname"]').first().fill(data.firstName);
        await this.form.locator('input[placeholder*="Nachname"], input[placeholder*="Name"], input[name*="nachname"]').first().fill(data.lastName);
        await this.form.locator('input[placeholder*="E-Mail"], input[type="email"]').first().fill(data.email);
        await this.form.locator('input[placeholder*="Telefon"], input[type="tel"]').first().fill(data.phone);
        
        const messageField = this.form.locator('textarea, input[name*="nachricht"]').first();
        if (await messageField.isVisible()) {
            await messageField.fill(data.message);
        }
    }

    /**
     * Отправка формы
     */
    async submit(): Promise<void> {
        await this.submitBtn.click({ force: true });
    }
}