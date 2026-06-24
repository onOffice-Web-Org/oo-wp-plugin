# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: newsletter-form.spec.ts >> Newsletter Formular: Multi-Theme Engine & Visual Regression >> Theme: onoffice-modern >> Visual: Snapshot on Tablet
- Location: tests-e2e/specs/newsletter-form.spec.ts:61:21

# Error details

```
Error: expect(locator).toHaveScreenshot(expected) failed

Locator: locator('form').filter({ has: locator('input[name="oo_formid"][value="Newsletter"]') })
Timeout: 5000ms
  Timeout 5000ms exceeded.

  Snapshot: newsletter-form-onoffice-modern-Tablet.png

Call log:
  - Expect "toHaveScreenshot(newsletter-form-onoffice-modern-Tablet.png)" with timeout 5000ms
    - generating new stable screenshot expectation
  - waiting for locator('form').filter({ has: locator('input[name="oo_formid"][value="Newsletter"]') })
    - waiting for" http://localhost/e2e-test-formulare/newsletterformular/?force_theme=onoffice-modern&e2e_key=qa_rocks&t=1782309636246" navigation to finish...
    - navigated to "http://localhost/e2e-test-formulare/newsletterformular/?force_theme=onoffice-modern&e2e_key=qa_rocks&t=1782309636246"
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
      - generic [ref=e8]:
        - link "Mustermann Immobilien" [ref=e10] [cursor=pointer]:
          - /url: http://localhost/
          - img "Mustermann Immobilien" [ref=e11]
        - button "Menü öffnen" [ref=e12] [cursor=pointer]:
          - img [ref=e13]
      - generic:
        - navigation "Hauptmenü" [ref=e15]:
          - menu [ref=e16]:
            - menuitem "Startseite" [ref=e17]:
              - link "Startseite" [ref=e18] [cursor=pointer]:
                - /url: http://localhost/
            - menuitem "Immobilien" [ref=e19]:
              - link "Immobilien" [ref=e20] [cursor=pointer]:
                - /url: http://localhost/immobilien/
              - menu [ref=e21]:
                - menuitem "Kauf" [ref=e22]:
                  - link "Kauf" [ref=e23] [cursor=pointer]:
                    - /url: http://localhost/immobilien/kauf/
                - menuitem "Miete" [ref=e24]:
                  - link "Miete" [ref=e25] [cursor=pointer]:
                    - /url: http://localhost/immobilien/miete/
                - menuitem "Merkliste" [ref=e26]:
                  - link "Merkliste" [ref=e27] [cursor=pointer]:
                    - /url: http://localhost/immobilien/merkliste/
            - menuitem "Für Eigentümer" [ref=e28]:
              - link "Für Eigentümer" [ref=e29] [cursor=pointer]:
                - /url: http://localhost/fuer-eigentuemer/
              - menu [ref=e30]:
                - menuitem "Immobilie verkaufen" [ref=e31]:
                  - link "Immobilie verkaufen" [ref=e32] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/immobilie-verkaufen/
                - menuitem "Immobilie vermieten" [ref=e33]:
                  - link "Immobilie vermieten" [ref=e34] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/immobilie-vermieten/
                - menuitem "Eigentümerformular" [ref=e35]:
                  - link "Eigentümerformular" [ref=e36] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/eigentuemerformular/
                - menuitem "Lead-Generator" [ref=e37]:
                  - link "Lead-Generator" [ref=e38] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/lead-generator-mit-icons/
            - menuitem "Für Interessenten" [ref=e39]:
              - link "Für Interessenten" [ref=e40] [cursor=pointer]:
                - /url: http://localhost/interessenten/
              - menu [ref=e41]:
                - menuitem "Interessentenformular" [ref=e42]:
                  - link "Interessentenformular" [ref=e43] [cursor=pointer]:
                    - /url: http://localhost/interessenten/interessentenformular/
            - menuitem "Über uns" [ref=e44]:
              - link "Über uns" [ref=e45] [cursor=pointer]:
                - /url: http://localhost/ueber-uns/
              - menu [ref=e46]:
                - menuitem "Unser Team" [ref=e47]:
                  - link "Unser Team" [ref=e48] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/ueber-uns/
                - menuitem "Referenzen" [ref=e49]:
                  - link "Referenzen" [ref=e50] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/referenzen/
                - menuitem "Bewertungen" [ref=e51]:
                  - link "Bewertungen" [ref=e52] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/bewertungen/
                - menuitem "News" [ref=e53]:
                  - link "News" [ref=e54] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/news/
            - menuitem "Kontakt" [ref=e55]:
              - link "Kontakt" [ref=e56] [cursor=pointer]:
                - /url: http://localhost/kontakt/
        - generic [ref=e59]:
          - generic [ref=e62]:
            - generic [ref=e63]:
              - term [ref=e64]:
                - img [ref=e65]
              - definition [ref=e67]:
                - link "+49 241 44686-0" [ref=e68] [cursor=pointer]:
                  - /url: tel:+4924144686-0
            - generic [ref=e69]:
              - term [ref=e70]:
                - img [ref=e71]
              - definition [ref=e74]:
                - link "info@onoffice.de" [ref=e75] [cursor=pointer]:
                  - /url: mailto:info@onoffice.de
          - generic [ref=e76]:
            - list [ref=e78]:
              - listitem [ref=e79]:
                - link "Facebook" [ref=e80] [cursor=pointer]:
                  - /url: https://www.facebook.com/onOffice.Software
                  - generic [ref=e81]: Facebook
                  - img [ref=e82]
              - listitem [ref=e85]:
                - link "YouTube" [ref=e86] [cursor=pointer]:
                  - /url: https://www.youtube.com/user/onOfficeSoftware
                  - generic [ref=e87]: YouTube
                  - img [ref=e88]
              - listitem [ref=e90]:
                - link "LinkedIn" [ref=e91] [cursor=pointer]:
                  - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                  - generic [ref=e92]: LinkedIn
                  - img [ref=e93]
              - listitem [ref=e95]:
                - link "Xing" [ref=e96] [cursor=pointer]:
                  - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                  - generic [ref=e97]: Xing
                  - img [ref=e98]
            - list [ref=e102]:
              - listitem [ref=e103]:
                - link "Deutsch" [ref=e104] [cursor=pointer]:
                  - /url: "#"
  - navigation "Breadcrumb" [ref=e105]:
    - list [ref=e106]:
      - listitem [ref=e107]:
        - link "Startseite" [ref=e108] [cursor=pointer]:
          - /url: http://localhost/
        - text: ">"
      - listitem [ref=e109]:
        - link "E2E Test – Formulare" [ref=e110] [cursor=pointer]:
          - /url: http://localhost/e2e-test-formulare/
        - text: ">"
      - listitem [ref=e111]: Newsletterformular
  - main [ref=e112]:
    - generic [ref=e116]:
      - paragraph [ref=e118]: "* Pflichtfelder"
      - generic [ref=e119]:
        - textbox "Vorname Newsletter_E2E_1" [ref=e120]:
          - /placeholder: Vorname
        - generic:
          - text: Vorname
          - generic: Newsletter_E2E_1
      - generic [ref=e121]:
        - textbox "Nachname Newsletter_E2E_1" [ref=e122]:
          - /placeholder: Nachname
        - generic:
          - text: Nachname
          - generic: Newsletter_E2E_1
      - generic [ref=e123]:
        - textbox "E-Mail * Newsletter_E2E_1" [ref=e124]:
          - /placeholder: E-Mail
        - generic:
          - text: E-Mail *
          - generic: Newsletter_E2E_1
      - generic [ref=e125]:
        - checkbox "Ich möchte mich für Ihren Newsletter anmelden * Newsletter_E2E_1"
        - generic [ref=e127]:
          - text: Ich möchte mich für Ihren Newsletter anmelden *
          - generic [ref=e128]: Newsletter_E2E_1
      - generic [ref=e129]:
        - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Newsletter_E2E_1"
        - generic [ref=e131]:
          - text: Ich bin mit den
          - link "Datenschutzbestimmungen" [ref=e132] [cursor=pointer]:
            - /url: /datenschutz/
          - text: einverstanden. *
          - generic [ref=e133]: Newsletter_E2E_1
      - generic [ref=e134]:
        - generic [ref=e136]:
          - generic [ref=e137]:
            - generic [ref=e138]:
              - generic [ref=e139]:
                - generic [ref=e140] [cursor=pointer]:
                  - checkbox "I'm not a robot" [ref=e141]
                  - img [ref=e142]
                - generic [ref=e144]: I'm not a robot
              - link [ref=e146] [cursor=pointer]:
                - /url: https://altcha.org
                - img [ref=e147]
            - generic [ref=e152]:
              - text: Protected by
              - link "Visit Altcha.org" [ref=e153] [cursor=pointer]:
                - /url: https://altcha.org/
                - text: ALTCHA
          - alert [ref=e154]:
            - 'generic "TypeError: Failed to fetch" [ref=e157]': Verification failed. Try again later.
        - button "Absenden" [ref=e158] [cursor=pointer]:
          - generic [ref=e159]: Absenden
          - img [ref=e161]
  - contentinfo [ref=e163]:
    - button "Zurück zum Anfang" [ref=e164] [cursor=pointer]:
      - img [ref=e165]
    - generic [ref=e169]:
      - generic [ref=e171]:
        - heading "Partner" [level=2] [ref=e172]
        - generic [ref=e173]:
          - link "Link zu https://www.immobilienscout24.de" [ref=e175] [cursor=pointer]:
            - /url: https://www.immobilienscout24.de
          - link "Link zu https://www.immonet.de/" [ref=e178] [cursor=pointer]:
            - /url: https://www.immonet.de/
          - link "Link zu https://www.immowelt.de/" [ref=e181] [cursor=pointer]:
            - /url: https://www.immowelt.de/
          - link "Link zu https://www.kleinanzeigen.de/" [ref=e184] [cursor=pointer]:
            - /url: https://www.kleinanzeigen.de/
      - generic [ref=e186]:
        - generic [ref=e187]:
          - heading "Anschrift" [level=2] [ref=e188]
          - generic [ref=e189]:
            - paragraph [ref=e190]:
              - text: Charlottenburger Allee 5
              - text: 52068 Aachen
              - text: Deutschland
            - generic [ref=e191]:
              - link "Telefonnummer +49 241 44686-0 anrufen" [ref=e192] [cursor=pointer]:
                - /url: tel:+49241446860
                - generic [ref=e193]: +49 241 44686-0
                - img [ref=e195]
              - link "Fax an +49 44686-250 senden" [ref=e197] [cursor=pointer]:
                - /url: tel:+4944686250
                - generic [ref=e198]: +49 44686-250
                - img [ref=e200]
              - link "E-Mail senden an info@onoffice.de" [ref=e208] [cursor=pointer]:
                - /url: mailto:info@onoffice.de
                - generic [ref=e209]: info@onoffice.de
                - img [ref=e211]
        - generic [ref=e214]:
          - heading "Social Media" [level=2] [ref=e215]
          - list [ref=e216]:
            - listitem [ref=e217]:
              - link "Facebook" [ref=e218] [cursor=pointer]:
                - /url: https://www.facebook.com/onOffice.Software
                - generic [ref=e219]: Facebook
                - img [ref=e220]
            - listitem [ref=e223]:
              - link "YouTube" [ref=e224] [cursor=pointer]:
                - /url: https://www.youtube.com/user/onOfficeSoftware
                - generic [ref=e225]: YouTube
                - img [ref=e226]
            - listitem [ref=e228]:
              - link "LinkedIn" [ref=e229] [cursor=pointer]:
                - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                - generic [ref=e230]: LinkedIn
                - img [ref=e231]
            - listitem [ref=e233]:
              - link "Xing" [ref=e234] [cursor=pointer]:
                - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                - generic [ref=e235]: Xing
                - img [ref=e236]
      - generic [ref=e239]:
        - heading "Abonnieren Sie unseren Newsletter" [level=2] [ref=e240]
        - paragraph [ref=e242]: Melden Sie sich heute kostenlos an und werden Sie als erster über neue Updates informiert.
        - generic [ref=e243]: "[oo_form form=\"Newsletter\"]"
    - generic [ref=e246]:
      - generic [ref=e247]:
        - paragraph [ref=e248]: © 2026 Mustermann Immobilien
        - navigation [ref=e249]:
          - list [ref=e250]:
            - listitem [ref=e251]:
              - link "Kontakt" [ref=e252] [cursor=pointer]:
                - /url: http://localhost/kontakt/
            - listitem [ref=e253]:
              - link "Impressum" [ref=e254] [cursor=pointer]:
                - /url: http://localhost/impressum/
            - listitem [ref=e255]:
              - link "Datenschutz" [ref=e256] [cursor=pointer]:
                - /url: http://localhost/datenschutz/
            - listitem [ref=e257]:
              - link "AGB" [ref=e258] [cursor=pointer]:
                - /url: http://localhost/agb/
            - listitem [ref=e259]:
              - button "Datenschutzeinstellungen" [ref=e260] [cursor=pointer]
            - listitem [ref=e261]:
              - button "Barriere gefunden? (Öffnet in neuem Tab)" [ref=e262] [cursor=pointer]: Barriere gefunden?
      - img [ref=e265]
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