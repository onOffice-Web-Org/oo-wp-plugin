# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: modern-contact-form.spec.ts >> Kontaktformular: Multi-Theme Engine & Visual Regression >> Theme: onoffice-classic >> Functional Scenarios >> Validation: Required Fields
- Location: tests-e2e/specs/modern-contact-form.spec.ts:111:21

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByText(/Bitte füllen Sie das Pflichtfeld aus/i).first()
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for getByText(/Bitte füllen Sie das Pflichtfeld aus/i).first()

```

# Page snapshot

```yaml
- generic [ref=e1]:
  - link "Zum Inhalt wechseln" [ref=e2] [cursor=pointer]:
    - /url: "#primary"
  - navigation "Breadcrumb" [ref=e3]:
    - list [ref=e4]:
      - listitem [ref=e5]:
        - link "Startseite" [ref=e6] [cursor=pointer]:
          - /url: http://localhost/
        - text: ">"
      - listitem [ref=e7]:
        - link "E2E Test – Formulare" [ref=e8] [cursor=pointer]:
          - /url: http://localhost/e2e-test-formulare/
        - text: ">"
      - listitem [ref=e9]: Kontaktformular
  - main [ref=e10]:
    - generic [ref=e14]:
      - generic [ref=e15]:
        - generic [ref=e16]: Ihre Kontaktdaten
        - paragraph [ref=e17]: "* Pflichtfelder"
      - generic [ref=e18]:
        - generic [ref=e19]:
          - text: Anrede
          - generic [ref=e20]: Kontaktformular_E2E_1
        - combobox [ref=e21]
        - combobox "Anrede Kontaktformular_E2E_1" [ref=e25] [cursor=pointer]
      - generic [ref=e26]:
        - text: Vorname *
        - generic [ref=e27]: Kontaktformular_E2E_1
        - textbox "Vorname * Kontaktformular_E2E_1" [active] [ref=e28]
      - generic [ref=e29]:
        - text: Nachname *
        - generic [ref=e30]: Kontaktformular_E2E_1
        - textbox "Nachname * Kontaktformular_E2E_1" [ref=e31]
      - generic [ref=e32]:
        - text: Straße
        - generic [ref=e33]: Kontaktformular_E2E_1
        - textbox "Straße Kontaktformular_E2E_1" [ref=e34]
      - generic [ref=e35]:
        - text: Plz
        - generic [ref=e36]: Kontaktformular_E2E_1
        - textbox "Plz Kontaktformular_E2E_1" [ref=e37]
      - generic [ref=e38]:
        - text: Ort
        - generic [ref=e39]: Kontaktformular_E2E_1
        - textbox "Ort Kontaktformular_E2E_1" [ref=e40]
      - generic [ref=e41]:
        - text: Telefonnummern *
        - generic [ref=e42]: Kontaktformular_E2E_1
        - textbox "Telefonnummern * Kontaktformular_E2E_1" [ref=e43]
      - generic [ref=e44]:
        - text: E-Mail *
        - generic [ref=e45]: Kontaktformular_E2E_1
        - textbox "E-Mail * Kontaktformular_E2E_1" [ref=e46]
      - generic [ref=e47]:
        - text: Nachricht *
        - generic [ref=e48]: Kontaktformular_E2E_1
        - textbox "Nachricht * Kontaktformular_E2E_1" [ref=e49]
      - generic [ref=e50]:
        - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Kontaktformular_E2E_1"
        - generic [ref=e52]:
          - text: Ich bin mit den
          - link "Datenschutzbestimmungen" [ref=e53] [cursor=pointer]:
            - /url: /datenschutz/
          - text: einverstanden. *
          - generic [ref=e54]: Kontaktformular_E2E_1
      - button "Absenden" [ref=e56] [cursor=pointer]
  - contentinfo [ref=e57]:
    - generic [ref=e60]:
      - generic [ref=e62]:
        - generic [ref=e63]: Partner
        - generic [ref=e64]:
          - link "Siegel für partner-1" [ref=e66] [cursor=pointer]:
            - /url: https://www.immobilienscout24.de
            - img "Siegel für partner-1" [ref=e68]
          - link "Siegel für partner-2" [ref=e70] [cursor=pointer]:
            - /url: https://www.immonet.de/
            - img "Siegel für partner-2" [ref=e72]
          - link "Siegel für partner-3" [ref=e74] [cursor=pointer]:
            - /url: https://www.immowelt.de/
            - img "Siegel für partner-3" [ref=e76]
          - link "Siegel für ka_horizontal_darkgreen_rgb" [ref=e78] [cursor=pointer]:
            - /url: https://www.kleinanzeigen.de/
            - img "Siegel für ka_horizontal_darkgreen_rgb" [ref=e80]
      - generic [ref=e81]:
        - generic [ref=e82]:
          - generic [ref=e83]: Anschrift
          - generic [ref=e84]:
            - paragraph [ref=e85]:
              - text: Charlottenburger Allee 5
              - text: 52068 Aachen
              - text: Deutschland
            - generic [ref=e86]:
              - generic [ref=e87]:
                - term [ref=e88]: "Tel.:"
                - definition [ref=e89]:
                  - link "+49 241 44686-0" [ref=e90] [cursor=pointer]:
                    - /url: tel:+49241446860
              - generic [ref=e91]:
                - term [ref=e92]: "Fax:"
                - definition [ref=e93]:
                  - link "+49 44686-250" [ref=e94] [cursor=pointer]:
                    - /url: tel:+4944686250
              - generic [ref=e95]:
                - term [ref=e96]: "E-Mail:"
                - definition [ref=e97]:
                  - link "info@onoffice.de" [ref=e98] [cursor=pointer]:
                    - /url: mailto:info@onoffice.de
        - generic [ref=e99]:
          - generic [ref=e100]: Social Media
          - list [ref=e101]:
            - listitem [ref=e102]:
              - link "Facebook" [ref=e103] [cursor=pointer]:
                - /url: https://www.facebook.com/onOffice.Software
                - generic [ref=e104]: Facebook
                - img [ref=e105]
            - listitem [ref=e107]:
              - link "YouTube" [ref=e108] [cursor=pointer]:
                - /url: https://www.youtube.com/user/onOfficeSoftware
                - generic [ref=e109]: YouTube
                - img [ref=e110]
            - listitem [ref=e112]:
              - link "LinkedIn" [ref=e113] [cursor=pointer]:
                - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                - generic [ref=e114]: LinkedIn
                - img [ref=e115]
            - listitem [ref=e117]:
              - link "Xing" [ref=e118] [cursor=pointer]:
                - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                - generic [ref=e119]: Xing
                - img [ref=e120]
      - generic [ref=e123]:
        - generic [ref=e124]: Abonnieren Sie unseren Newsletter
        - paragraph [ref=e126]: Melden Sie sich heute kostenlos an und werden Sie als erster über neue Updates informiert.
        - generic [ref=e129]:
          - paragraph [ref=e131]: "* Pflichtfelder"
          - generic [ref=e132]:
            - text: Vorname
            - generic [ref=e133]: Newsletter_2
            - textbox "Vorname Newsletter_2" [ref=e134]
          - generic [ref=e135]:
            - text: Nachname
            - generic [ref=e136]: Newsletter_2
            - textbox "Nachname Newsletter_2" [ref=e137]
          - generic [ref=e138]:
            - text: E-Mail *
            - generic [ref=e139]: Newsletter_2
            - textbox "E-Mail * Newsletter_2" [ref=e140]
          - generic [ref=e141]:
            - checkbox "Ich möchte mich für Ihren Newsletter anmelden * Newsletter_2"
            - generic [ref=e143]:
              - text: Ich möchte mich für Ihren Newsletter anmelden *
              - generic [ref=e144]: Newsletter_2
          - generic [ref=e145]:
            - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Newsletter_2"
            - generic [ref=e147]:
              - text: Ich bin mit den
              - link "Datenschutzbestimmungen" [ref=e148] [cursor=pointer]:
                - /url: /datenschutz/
              - text: einverstanden. *
              - generic [ref=e149]: Newsletter_2
          - button "Absenden" [ref=e151] [cursor=pointer]
    - generic [ref=e154]:
      - generic [ref=e155]:
        - paragraph [ref=e156]:
          - generic [ref=e157]: © 2026 Mustermann Immobilien
        - navigation [ref=e158]:
          - list [ref=e159]:
            - listitem [ref=e160]:
              - link "Kontakt" [ref=e161] [cursor=pointer]:
                - /url: http://localhost/kontakt/
            - listitem [ref=e162]:
              - link "Impressum" [ref=e163] [cursor=pointer]:
                - /url: http://localhost/impressum/
            - listitem [ref=e164]:
              - link "Datenschutz" [ref=e165] [cursor=pointer]:
                - /url: http://localhost/datenschutz/
            - listitem [ref=e166]:
              - link "AGB" [ref=e167] [cursor=pointer]:
                - /url: http://localhost/agb/
            - listitem [ref=e168]:
              - link "Datenschutzeinstellungen" [ref=e169] [cursor=pointer]:
                - /url: "#"
      - img [ref=e172]
```

# Test source

```ts
  16  |     { name: 'Tablet', width: 768, height: 1024 },
  17  |     { name: 'Mobile', width: 375, height: 667 },
  18  | ];
  19  | 
  20  | const E2E_SECRET = 'qa_rocks';
  21  | const BASE_PATH = '/e2e-test-formulare/kontaktformular/';
  22  | 
  23  | 
  24  | test.describe('Kontaktformular: Multi-Theme Engine & Visual Regression', () => {
  25  | 
  26  |     for (const theme of THEMES) {
  27  |         test.describe(`Theme: ${theme}`, () => {
  28  |             let contactPage: ContactFormPage;
  29  | 
  30  |             test.beforeEach(async ({ page }) => {
  31  |                 // Wenn die Theme "Pure" ist — kennzeichnen wir sie als "fixme", da dort die Gestaltung/Abhängigkeiten defekt sind
  32  |                 /*if (theme === 'onoffice-pure' || theme === 'onoffice-timeless') {
  33  |                     test.fixme(true, 'Die Theme "Pure" hat bekannte Probleme mit der Formulargestaltung, die vor dem Test behoben werden müssen.');
  34  |                 }
  35  |                 */
  36  |                
  37  |                 contactPage = new ContactFormPage(page);
  38  |                 
  39  |                 // URLs mit Theme-Wechsel- und Cache-Umgehungs-Flags generieren
  40  |                 const url = `${BASE_PATH}?force_theme=${theme}&e2e_key=${E2E_SECRET}&t=${Date.now()}`;
  41  |                 
  42  |                 await page.goto(url, { waitUntil: 'networkidle' });
  43  |                 
  44  |                 // Vorbereitung der Seite: Bereinigung der Banner und Overlays
  45  |                 await contactPage.acceptCookies();
  46  |                 await contactPage.hideOverlays();
  47  |             });
  48  | 
  49  |             /**
  50  |              * 1. TECHNISCHE PRÜFUNG
  51  |              * Wir überprüfen, ob unser PHP-Filter die Klasse im Body tatsächlich ersetzt hat.
  52  |              */
  53  |             test('Backend: Verify Theme Injection', async ({ page }) => {
  54  |                 const themeSlug = theme.replace('onoffice-', '');
  55  |                 
  56  |                 await expect.poll(async () => {
  57  |                     return await page.locator('body').getAttribute('class');
  58  |                 }, {
  59  |                     message: `Theme slug "${themeSlug}" not found in body class`,
  60  |                     timeout: 10000,
  61  |                 }).toContain(themeSlug);
  62  |             });
  63  | 
  64  |             /**
  65  |              * 2. VISUELLE PRÜFUNG
  66  |              * Wir vergleichen die Form mit dem Standard für jede Auflösung.
  67  |              */
  68  |             for (const vp of VIEWPORTS) {
  69  |                 test(`Visual: Snapshot on ${vp.name}`, async ({ page }) => {
  70  |                     await page.setViewportSize({ width: vp.width, height: vp.height });
  71  |                     
  72  |                     await page.waitForTimeout(300);
  73  |                     await contactPage.hideOverlays();
  74  | 
  75  |                     await expect(contactPage.form).toHaveScreenshot(`form-${theme}-${vp.name}.png`, {
  76  |                         maxDiffPixelRatio: 0.05,
  77  |                         animations: 'disabled',
  78  |                     });
  79  |                 });
  80  |             }
  81  | 
  82  |             /**
  83  |              * 3. FUNKTIONALE PRÜFUNG
  84  |              * Prüfung der Logik beim Ausfüllen und Absenden
  85  |              */
  86  |             test.describe('Functional Scenarios', () => {
  87  |                 
  88  |                 test('Submission: Happy Path', async () => {
  89  |                     // Nonce-Prüfung (Formularschutz)
  90  |                     const nonce = contactPage.form.locator('input[name="onoffice_nonce"]');
  91  |                     if (await nonce.count() > 0) {
  92  |                         await expect(nonce).toBeAttached();
  93  |                     }
  94  | 
  95  |                     await contactPage.fillForm({
  96  |                         firstName: 'E2E-Tester',
  97  |                         lastName: `Theme-${theme}`,
  98  |                         email: 'qa-test@onoffice.de',
  99  |                         phone: '+49 123 456789',
  100 |                         message: `Automated test for ${theme}`
  101 |                     });
  102 | 
  103 |                     await contactPage.submit();
  104 | 
  105 |                     // Überprüfen Sie die Erfolgsmeldung (unter Berücksichtigung eines möglichen lokalen Spamfilters)
  106 |                     await expect(contactPage.infoMessages).toBeVisible({ timeout: 20000 });
  107 |                     const msgText = await contactPage.infoMessages.innerText();
  108 |                     expect(msgText).toMatch(/Vielen Dank|Spam erkannt/);
  109 |                 });
  110 | 
  111 |                 test('Validation: Required Fields', async () => {
  112 |                     await contactPage.submit();
  113 |                     
  114 |                     // Überprüfung des Füllfehlertextes für Pflichtfelder
  115 |                     const fieldError = contactPage.page.getByText(/Bitte füllen Sie das Pflichtfeld aus/i).first();
> 116 |                     await expect(fieldError).toBeVisible();
      |                                              ^ Error: expect(locator).toBeVisible() failed
  117 | 
  118 |                     // Überprüfung der GDPR-Fehlermeldung
  119 |                     const gdprError = contactPage.page.getByText(/Bitte unterzeichnen Sie|Datenschutz/i).first();
  120 |                     await expect(gdprError).toBeVisible();
  121 |                 });
  122 |             }); 
  123 |         }); 
  124 |     } 
  125 | });
```