# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: newsletter-form.spec.ts >> Newsletter Formular: Multi-Theme Engine & Visual Regression >> Theme: onoffice-modern >> Visual: Snapshot on Desktop
- Location: tests-e2e/specs/newsletter-form.spec.ts:61:21

# Error details

```
Error: expect(locator).toHaveScreenshot(expected) failed

Locator: locator('form').filter({ has: locator('input[name="oo_formid"][value="Newsletter"]') })
Timeout: 5000ms
  Timeout 5000ms exceeded.

  Snapshot: newsletter-form-onoffice-modern-Desktop.png

Call log:
  - Expect "toHaveScreenshot(newsletter-form-onoffice-modern-Desktop.png)" with timeout 5000ms
    - generating new stable screenshot expectation
  - waiting for locator('form').filter({ has: locator('input[name="oo_formid"][value="Newsletter"]') })
    - waiting for" http://localhost/e2e-test-formulare/newsletterformular/?force_theme=onoffice-modern&e2e_key=qa_rocks&t=1782309627872" navigation to finish...
    - navigated to "http://localhost/e2e-test-formulare/newsletterformular/?force_theme=onoffice-modern&e2e_key=qa_rocks&t=1782309627872"
  - Timeout 5000ms exceeded.

```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - link "Zur Navigation wechseln" [ref=e2] [cursor=pointer]:
    - /url: "#menu-hauptmenue"
  - link "Zum Inhalt wechseln" [ref=e3] [cursor=pointer]:
    - /url: "#primary"
  - link "Zum Footer wechseln" [ref=e4] [cursor=pointer]:
    - /url: "#footer"
  - banner [ref=e5]:
    - generic [ref=e6]:
      - generic [ref=e10]:
        - generic [ref=e13]:
          - generic [ref=e14]:
            - term [ref=e15]:
              - img [ref=e16]
            - definition [ref=e18]:
              - link "+49 241 44686-0" [ref=e19] [cursor=pointer]:
                - /url: tel:+4924144686-0
          - generic [ref=e20]:
            - term [ref=e21]:
              - img [ref=e22]
            - definition [ref=e25]:
              - link "info@onoffice.de" [ref=e26] [cursor=pointer]:
                - /url: mailto:info@onoffice.de
        - generic [ref=e27]:
          - list [ref=e29]:
            - listitem [ref=e30]:
              - link "Facebook" [ref=e31] [cursor=pointer]:
                - /url: https://www.facebook.com/onOffice.Software
                - generic [ref=e32]: Facebook
                - img [ref=e33]
            - listitem [ref=e36]:
              - link "YouTube" [ref=e37] [cursor=pointer]:
                - /url: https://www.youtube.com/user/onOfficeSoftware
                - generic [ref=e38]: YouTube
                - img [ref=e39]
            - listitem [ref=e41]:
              - link "LinkedIn" [ref=e42] [cursor=pointer]:
                - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                - generic [ref=e43]: LinkedIn
                - img [ref=e44]
            - listitem [ref=e46]:
              - link "Xing" [ref=e47] [cursor=pointer]:
                - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                - generic [ref=e48]: Xing
                - img [ref=e49]
          - list [ref=e53]:
            - listitem [ref=e54]:
              - link "Deutsch" [ref=e55] [cursor=pointer]:
                - /url: "#"
      - link "Mustermann Immobilien" [ref=e59] [cursor=pointer]:
        - /url: http://localhost/
        - img "Mustermann Immobilien" [ref=e60]
      - navigation "Hauptmenü" [ref=e63]:
        - menu [ref=e64]:
          - menuitem "Startseite" [ref=e65]:
            - link "Startseite" [ref=e66] [cursor=pointer]:
              - /url: http://localhost/
          - menuitem "Immobilien" [ref=e67]:
            - link "Immobilien" [ref=e68] [cursor=pointer]:
              - /url: http://localhost/immobilien/
          - menuitem "Für Eigentümer" [ref=e69]:
            - link "Für Eigentümer" [ref=e70] [cursor=pointer]:
              - /url: http://localhost/fuer-eigentuemer/
          - menuitem "Für Interessenten" [ref=e71]:
            - link "Für Interessenten" [ref=e72] [cursor=pointer]:
              - /url: http://localhost/interessenten/
          - menuitem "Über uns" [ref=e73]:
            - link "Über uns" [ref=e74] [cursor=pointer]:
              - /url: http://localhost/ueber-uns/
          - menuitem "Kontakt" [ref=e75]:
            - link "Kontakt" [ref=e76] [cursor=pointer]:
              - /url: http://localhost/kontakt/
  - navigation "Breadcrumb" [ref=e77]:
    - list [ref=e78]:
      - listitem [ref=e79]:
        - link "Startseite" [ref=e80] [cursor=pointer]:
          - /url: http://localhost/
        - text: ">"
      - listitem [ref=e81]:
        - link "E2E Test – Formulare" [ref=e82] [cursor=pointer]:
          - /url: http://localhost/e2e-test-formulare/
        - text: ">"
      - listitem [ref=e83]: Newsletterformular
  - main [ref=e84]:
    - generic [ref=e88]:
      - paragraph [ref=e90]: "* Pflichtfelder"
      - generic [ref=e91]:
        - textbox "Vorname Newsletter_E2E_1" [ref=e92]:
          - /placeholder: Vorname
        - generic:
          - text: Vorname
          - generic: Newsletter_E2E_1
      - generic [ref=e93]:
        - textbox "Nachname Newsletter_E2E_1" [ref=e94]:
          - /placeholder: Nachname
        - generic:
          - text: Nachname
          - generic: Newsletter_E2E_1
      - generic [ref=e95]:
        - textbox "E-Mail * Newsletter_E2E_1" [ref=e96]:
          - /placeholder: E-Mail
        - generic:
          - text: E-Mail *
          - generic: Newsletter_E2E_1
      - generic [ref=e97]:
        - checkbox "Ich möchte mich für Ihren Newsletter anmelden * Newsletter_E2E_1"
        - generic [ref=e99]:
          - text: Ich möchte mich für Ihren Newsletter anmelden *
          - generic [ref=e100]: Newsletter_E2E_1
      - generic [ref=e101]:
        - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Newsletter_E2E_1"
        - generic [ref=e103]:
          - text: Ich bin mit den
          - link "Datenschutzbestimmungen" [ref=e104] [cursor=pointer]:
            - /url: /datenschutz/
          - text: einverstanden. *
          - generic [ref=e105]: Newsletter_E2E_1
      - generic [ref=e106]:
        - generic [ref=e108]:
          - generic [ref=e109]:
            - generic [ref=e110]:
              - generic [ref=e111]:
                - generic [ref=e112] [cursor=pointer]:
                  - checkbox "I'm not a robot" [ref=e113]
                  - img [ref=e114]
                - generic [ref=e116]: I'm not a robot
              - link [ref=e118] [cursor=pointer]:
                - /url: https://altcha.org
                - img [ref=e119]
            - generic [ref=e124]:
              - text: Protected by
              - link "Visit Altcha.org" [ref=e125] [cursor=pointer]:
                - /url: https://altcha.org/
                - text: ALTCHA
          - alert [ref=e126]:
            - 'generic "TypeError: Failed to fetch" [ref=e129]': Verification failed. Try again later.
        - button "Absenden" [ref=e130] [cursor=pointer]:
          - generic [ref=e131]: Absenden
          - img [ref=e133]
  - contentinfo [ref=e135]:
    - button "Zurück zum Anfang" [ref=e136] [cursor=pointer]:
      - img [ref=e137]
    - generic [ref=e141]:
      - generic [ref=e143]:
        - heading "Partner" [level=2] [ref=e144]
        - generic [ref=e145]:
          - link "Link zu https://www.immobilienscout24.de" [ref=e147] [cursor=pointer]:
            - /url: https://www.immobilienscout24.de
          - link "Link zu https://www.immonet.de/" [ref=e150] [cursor=pointer]:
            - /url: https://www.immonet.de/
          - link "Link zu https://www.immowelt.de/" [ref=e153] [cursor=pointer]:
            - /url: https://www.immowelt.de/
          - link "Link zu https://www.kleinanzeigen.de/" [ref=e156] [cursor=pointer]:
            - /url: https://www.kleinanzeigen.de/
      - generic [ref=e158]:
        - generic [ref=e159]:
          - heading "Anschrift" [level=2] [ref=e160]
          - generic [ref=e161]:
            - paragraph [ref=e162]:
              - text: Charlottenburger Allee 5
              - text: 52068 Aachen
              - text: Deutschland
            - generic [ref=e163]:
              - link "Telefonnummer +49 241 44686-0 anrufen" [ref=e164] [cursor=pointer]:
                - /url: tel:+49241446860
                - generic [ref=e165]: +49 241 44686-0
                - img [ref=e167]
              - link "Fax an +49 44686-250 senden" [ref=e169] [cursor=pointer]:
                - /url: tel:+4944686250
                - generic [ref=e170]: +49 44686-250
                - img [ref=e172]
              - link "E-Mail senden an info@onoffice.de" [ref=e180] [cursor=pointer]:
                - /url: mailto:info@onoffice.de
                - generic [ref=e181]: info@onoffice.de
                - img [ref=e183]
        - generic [ref=e186]:
          - heading "Social Media" [level=2] [ref=e187]
          - list [ref=e188]:
            - listitem [ref=e189]:
              - link "Facebook" [ref=e190] [cursor=pointer]:
                - /url: https://www.facebook.com/onOffice.Software
                - generic [ref=e191]: Facebook
                - img [ref=e192]
            - listitem [ref=e195]:
              - link "YouTube" [ref=e196] [cursor=pointer]:
                - /url: https://www.youtube.com/user/onOfficeSoftware
                - generic [ref=e197]: YouTube
                - img [ref=e198]
            - listitem [ref=e200]:
              - link "LinkedIn" [ref=e201] [cursor=pointer]:
                - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                - generic [ref=e202]: LinkedIn
                - img [ref=e203]
            - listitem [ref=e205]:
              - link "Xing" [ref=e206] [cursor=pointer]:
                - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                - generic [ref=e207]: Xing
                - img [ref=e208]
      - generic [ref=e211]:
        - heading "Abonnieren Sie unseren Newsletter" [level=2] [ref=e212]
        - paragraph [ref=e214]: Melden Sie sich heute kostenlos an und werden Sie als erster über neue Updates informiert.
        - generic [ref=e215]: "[oo_form form=\"Newsletter\"]"
    - generic [ref=e218]:
      - generic [ref=e219]:
        - paragraph [ref=e220]:
          - generic [ref=e221]: © 2026
          - generic [ref=e222]: Mustermann Immobilien
        - navigation [ref=e223]:
          - list [ref=e224]:
            - listitem [ref=e225]:
              - link "Kontakt" [ref=e226] [cursor=pointer]:
                - /url: http://localhost/kontakt/
            - listitem [ref=e227]:
              - link "Impressum" [ref=e228] [cursor=pointer]:
                - /url: http://localhost/impressum/
            - listitem [ref=e229]:
              - link "Datenschutz" [ref=e230] [cursor=pointer]:
                - /url: http://localhost/datenschutz/
            - listitem [ref=e231]:
              - link "AGB" [ref=e232] [cursor=pointer]:
                - /url: http://localhost/agb/
            - listitem [ref=e233]:
              - button "Datenschutzeinstellungen" [ref=e234] [cursor=pointer]
            - listitem [ref=e235]:
              - button "Barriere gefunden? (Öffnet in neuem Tab)" [ref=e236] [cursor=pointer]: Barriere gefunden?
      - img [ref=e239]
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
> 68  |                     await expect(newsletterPage.form).toHaveScreenshot(`newsletter-form-${theme}-${vp.name}.png`, {
      |                                                       ^ Error: expect(locator).toHaveScreenshot(expected) failed
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
  85  |                     await expect(emailInput).toBeVisible();
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