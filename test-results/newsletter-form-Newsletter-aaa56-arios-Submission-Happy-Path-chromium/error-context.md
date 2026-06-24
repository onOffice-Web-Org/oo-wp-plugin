# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: newsletter-form.spec.ts >> Newsletter Formular: Multi-Theme Engine & Visual Regression >> Theme: onoffice-modern >> Functional Scenarios >> Submission: Happy Path
- Location: tests-e2e/specs/newsletter-form.spec.ts:81:21

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('form').filter({ has: locator('input[name="oo_formid"][value="Newsletter"]') }).locator('input[type="email"]').first()
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for locator('form').filter({ has: locator('input[name="oo_formid"][value="Newsletter"]') }).locator('input[type="email"]').first()
    - waiting for" http://localhost/e2e-test-formulare/newsletterformular/?force_theme=onoffice-modern&e2e_key=qa_rocks&t=1782309653302" navigation to finish...
    - navigated to "http://localhost/e2e-test-formulare/newsletterformular/?force_theme=onoffice-modern&e2e_key=qa_rocks&t=1782309653302"

```

```yaml
- link "Zur Navigation wechseln":
  - /url: "#menu-hauptmenue"
- link "Zum Inhalt wechseln":
  - /url: "#primary"
- link "Zum Footer wechseln":
  - /url: "#footer"
- banner:
  - link "Mustermann Immobilien":
    - /url: http://localhost/
    - img "Mustermann Immobilien"
  - button "Menü öffnen"
  - navigation "Hauptmenü":
    - menu:
      - menuitem "Startseite":
        - link "Startseite":
          - /url: http://localhost/
      - menuitem "Immobilien":
        - link "Immobilien":
          - /url: http://localhost/immobilien/
        - menu:
          - menuitem "Kauf":
            - link "Kauf":
              - /url: http://localhost/immobilien/kauf/
          - menuitem "Miete":
            - link "Miete":
              - /url: http://localhost/immobilien/miete/
          - menuitem "Merkliste":
            - link "Merkliste":
              - /url: http://localhost/immobilien/merkliste/
      - menuitem "Für Eigentümer":
        - link "Für Eigentümer":
          - /url: http://localhost/fuer-eigentuemer/
        - menu:
          - menuitem "Immobilie verkaufen":
            - link "Immobilie verkaufen":
              - /url: http://localhost/fuer-eigentuemer/immobilie-verkaufen/
          - menuitem "Immobilie vermieten":
            - link "Immobilie vermieten":
              - /url: http://localhost/fuer-eigentuemer/immobilie-vermieten/
          - menuitem "Eigentümerformular":
            - link "Eigentümerformular":
              - /url: http://localhost/fuer-eigentuemer/eigentuemerformular/
          - menuitem "Lead-Generator":
            - link "Lead-Generator":
              - /url: http://localhost/fuer-eigentuemer/lead-generator-mit-icons/
      - menuitem "Für Interessenten":
        - link "Für Interessenten":
          - /url: http://localhost/interessenten/
        - menu:
          - menuitem "Interessentenformular":
            - link "Interessentenformular":
              - /url: http://localhost/interessenten/interessentenformular/
      - menuitem "Über uns":
        - link "Über uns":
          - /url: http://localhost/ueber-uns/
        - menu:
          - menuitem "Unser Team":
            - link "Unser Team":
              - /url: http://localhost/ueber-uns/ueber-uns/
          - menuitem "Referenzen":
            - link "Referenzen":
              - /url: http://localhost/ueber-uns/referenzen/
          - menuitem "Bewertungen":
            - link "Bewertungen":
              - /url: http://localhost/ueber-uns/bewertungen/
          - menuitem "News":
            - link "News":
              - /url: http://localhost/ueber-uns/news/
      - menuitem "Kontakt":
        - link "Kontakt":
          - /url: http://localhost/kontakt/
  - term
  - definition:
    - link "+49 241 44686-0":
      - /url: tel:+4924144686-0
  - term
  - definition:
    - link "info@onoffice.de":
      - /url: mailto:info@onoffice.de
  - list:
    - listitem:
      - link "Facebook":
        - /url: https://www.facebook.com/onOffice.Software
    - listitem:
      - link "YouTube":
        - /url: https://www.youtube.com/user/onOfficeSoftware
    - listitem:
      - link "LinkedIn":
        - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
    - listitem:
      - link "Xing":
        - /url: https://www.xing.com/companies/onofficesoftwaregmbh
  - list:
    - listitem:
      - link "Deutsch":
        - /url: "#"
- navigation "Breadcrumb":
  - list:
    - listitem:
      - link "Startseite":
        - /url: http://localhost/
      - text: ">"
    - listitem:
      - link "E2E Test – Formulare":
        - /url: http://localhost/e2e-test-formulare/
      - text: ">"
    - listitem: Newsletterformular
- main:
  - paragraph: "* Pflichtfelder"
  - textbox "Vorname Newsletter_E2E_1":
    - /placeholder: Vorname
  - text: Vorname Newsletter_E2E_1
  - textbox "Nachname Newsletter_E2E_1":
    - /placeholder: Nachname
  - text: Nachname Newsletter_E2E_1
  - textbox "E-Mail * Newsletter_E2E_1":
    - /placeholder: E-Mail
  - text: E-Mail * Newsletter_E2E_1
  - checkbox "Ich möchte mich für Ihren Newsletter anmelden * Newsletter_E2E_1"
  - text: Ich möchte mich für Ihren Newsletter anmelden * Newsletter_E2E_1
  - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Newsletter_E2E_1"
  - text: Ich bin mit den
  - link "Datenschutzbestimmungen":
    - /url: /datenschutz/
  - text: einverstanden. * Newsletter_E2E_1
  - checkbox "I'm not a robot"
  - img
  - text: I'm not a robot Protected by
  - link "Visit Altcha.org":
    - /url: https://altcha.org/
    - text: ALTCHA
  - button "Absenden"
- contentinfo:
  - button "Zurück zum Anfang"
  - heading "Partner" [level=2]
  - link "Link zu https://www.immobilienscout24.de":
    - /url: https://www.immobilienscout24.de
  - link "Link zu https://www.immonet.de/":
    - /url: https://www.immonet.de/
  - link "Link zu https://www.immowelt.de/":
    - /url: https://www.immowelt.de/
  - link "Link zu https://www.kleinanzeigen.de/":
    - /url: https://www.kleinanzeigen.de/
  - heading "Anschrift" [level=2]
  - paragraph: Charlottenburger Allee 5 52068 Aachen Deutschland
  - link "Telefonnummer +49 241 44686-0 anrufen":
    - /url: tel:+49241446860
  - link "Fax an +49 44686-250 senden":
    - /url: tel:+4944686250
  - link "E-Mail senden an info@onoffice.de":
    - /url: mailto:info@onoffice.de
  - heading "Social Media" [level=2]
  - list:
    - listitem:
      - link "Facebook":
        - /url: https://www.facebook.com/onOffice.Software
    - listitem:
      - link "YouTube":
        - /url: https://www.youtube.com/user/onOfficeSoftware
    - listitem:
      - link "LinkedIn":
        - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
    - listitem:
      - link "Xing":
        - /url: https://www.xing.com/companies/onofficesoftwaregmbh
  - heading "Abonnieren Sie unseren Newsletter" [level=2]
  - paragraph: Melden Sie sich heute kostenlos an und werden Sie als erster über neue Updates informiert.
  - text: "[oo_form form=\"Newsletter\"]"
  - paragraph: © 2026 Mustermann Immobilien
  - navigation:
    - list:
      - listitem:
        - link "Kontakt":
          - /url: http://localhost/kontakt/
      - listitem:
        - link "Impressum":
          - /url: http://localhost/impressum/
      - listitem:
        - link "Datenschutz":
          - /url: http://localhost/datenschutz/
      - listitem:
        - link "AGB":
          - /url: http://localhost/agb/
      - listitem:
        - button "Datenschutzeinstellungen"
      - listitem:
        - button "Barriere gefunden? (Öffnet in neuem Tab)": Barriere gefunden?
  - img
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | import { NewsletterPage } from '../page-objects/NewsletterPage';
  3   | 
  4   | /**
  5   |  * Globale Testeinstellungen für das Umschalten von Themes und Viewports
  6   |  */
  7   | const THEMES = [
  8   |     'onoffice-modern',
  9   |     'onoffice-classic',
  10  |     'onoffice-pure',
  11  |     'onoffice-timeless'
  12  | ];
  13  | 
  14  | const VIEWPORTS = [
  15  |     { name: 'Desktop', width: 1920, height: 1080 },
  16  |     { name: 'Tablet', width: 768, height: 1024 },
  17  |     { name: 'Mobile', width: 375, height: 667 },
  18  | ];
  19  | 
  20  | const E2E_SECRET = 'qa_rocks';
  21  | const BASE_PATH = '/e2e-test-formulare/newsletterformular/';
  22  | 
  23  | test.describe('Newsletter Formular: Multi-Theme Engine & Visual Regression', () => {
  24  | 
  25  |     for (const theme of THEMES) {
  26  |         test.describe(`Theme: ${theme}`, () => {
  27  |             let newsletterPage: NewsletterPage;
  28  | 
  29  |             test.beforeEach(async ({ page }) => {
  30  |                 newsletterPage = new NewsletterPage(page);
  31  |                 
  32  |                 // URL-Generierung mit schnellem Themenwechsel und controllerlosem Sicherheitsschlüssel
  33  |                 const url = `${BASE_PATH}?force_theme=${theme}&e2e_key=${E2E_SECRET}&t=${Date.now()}`;
  34  |                 await page.goto(url, { waitUntil: 'networkidle' });
  35  |                 
  36  |                 // Vorbereitung der Seite über das Page Object
  37  |                 await newsletterPage.acceptCookies();
  38  |                 await newsletterPage.hideOverlays();
  39  |             });
  40  | 
  41  |             /**
  42  |              * 1. TECHNISCHE PRÜFUNG
  43  |              * Wir überprüfen, ob unser PHP-Filter die Theme-Klasse im Body-Tag erfolgreich ersetzt hat.
  44  |              */
  45  |             test('Backend: Verify Theme Injection', async ({ page }) => {
  46  |                 const themeSlug = theme.replace('onoffice-', '');
  47  |                 
  48  |                 await expect.poll(async () => {
  49  |                     return await page.locator('body').getAttribute('class');
  50  |                 }, {
  51  |                     message: `Theme slug "${themeSlug}" not found in body class`,
  52  |                     timeout: 10000,
  53  |                 }).toContain(themeSlug);
  54  |             });
  55  | 
  56  |             /**
  57  |              * 2. VISUELLE PRÜFUNG
  58  |              * Wir vergleichen die Gestaltung des Newsletter-Formulars mit Referenz-Screenshots.
  59  |              */
  60  |             for (const vp of VIEWPORTS) {
  61  |                 test(`Visual: Snapshot on ${vp.name}`, async ({ page }) => {
  62  |                     await page.setViewportSize({ width: vp.width, height: vp.height });
  63  |                     
  64  |                     await page.waitForTimeout(300);
  65  |                     await newsletterPage.hideOverlays();
  66  | 
  67  |                     // Wir erstellen einen Screenshot des Formulars mithilfe eines Elements aus dem Seitenobjekt.
  68  |                     await expect(newsletterPage.form).toHaveScreenshot(`newsletter-form-${theme}-${vp.name}.png`, {
  69  |                         maxDiffPixelRatio: 0.02,
  70  |                         animations: 'disabled',
  71  |                     });
  72  |                 });
  73  |             }
  74  | 
  75  |             /**
  76  |              * 3. FUNKTIONALE PRÜFUNG
  77  |              * Wir überprüfen die Logik der Felder und die Übermittlung des Newsletter-Formulars.
  78  |              */
  79  |             test.describe('Functional Scenarios', () => {
  80  |                 
  81  |                 test('Submission: Happy Path', async () => {
  82  |                     const emailInput = newsletterPage.form.locator('input[type="email"]').first();
  83  |                     
  84  |                     // Wir überprüfen, ob der Email-Input sichtbar ist
> 85  |                     await expect(emailInput).toBeVisible();
      |                                              ^ Error: expect(locator).toBeVisible() failed
  86  |                     await emailInput.fill(`newsletter-test-${Date.now()}@onoffice.de`);
  87  |                     
  88  |                     // Wir suchen die Absende-Taste (universell für verschiedene Themes)
  89  |                     const submitBtn = newsletterPage.form.getByRole('button', { name: /Absenden|Eintragen|Submit/i })
  90  |                         .or(newsletterPage.form.locator('button[type="submit"]'))
  91  |                         .first();
  92  |                         
  93  |                     await submitBtn.click({ force: true });
  94  |                     
  95  |                     // Oder wir erwarten einen erfolgreichen Antworttext
  96  |                     await expect(newsletterPage.page.getByText(/Vielen Dank|erfolgreich/i)).toBeVisible({ timeout: 10000 });
  97  |                 });
  98  | 
  99  |                 test('Newsletter: Email Validation', async () => {
  100 |                     const emailInput = newsletterPage.form.locator('input[type="email"]').first();
  101 |                     
  102 |                     await expect(emailInput).toBeVisible();
  103 |                     await emailInput.fill('invalid-email');
  104 |                     
  105 |                     const submitBtn = newsletterPage.form.getByRole('button', { name: /Absenden|Eintragen|Submit/i })
  106 |                         .or(newsletterPage.form.locator('button[type="submit"]'))
  107 |                         .first();
  108 |                         
  109 |                     await submitBtn.click({ force: true });
  110 |                     
  111 |                     // Der Validierungsfehler sollte angezeigt werden
  112 |                     await expect(newsletterPage.page.getByText(/gültig|invalid/i)).toBeVisible({ timeout: 5000 });
  113 |                 });
  114 |             });
  115 |         });
  116 |     }
  117 | });
```