# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: modern-contact-form.spec.ts >> Kontaktformular: Multi-Theme Engine & Visual Regression >> Theme: onoffice-timeless >> Functional Scenarios >> Submission: Happy Path
- Location: tests-e2e/specs/modern-contact-form.spec.ts:88:21

# Error details

```
Error: expect(received).toMatch(expected)

Expected pattern: /Vielen Dank|Spam erkannt/
Received string:  "Es ist ein Fehler aufgetreten. Bitte überprüfen Sie Ihre Angaben."
```

# Page snapshot

```yaml
- generic [ref=e1]:
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
        - navigation "Hauptmenü" [ref=e16]:
          - menu [ref=e17]:
            - menuitem "Startseite" [ref=e18]:
              - link "Startseite" [ref=e19] [cursor=pointer]:
                - /url: http://localhost/
            - menuitem "Immobilien" [ref=e20]:
              - link "Immobilien" [ref=e21] [cursor=pointer]:
                - /url: http://localhost/immobilien/
              - menu [ref=e22]:
                - menuitem "Kauf" [ref=e23]:
                  - link "Kauf" [ref=e24] [cursor=pointer]:
                    - /url: http://localhost/immobilien/kauf/
                - menuitem "Miete" [ref=e25]:
                  - link "Miete" [ref=e26] [cursor=pointer]:
                    - /url: http://localhost/immobilien/miete/
                - menuitem "Merkliste" [ref=e27]:
                  - link "Merkliste" [ref=e28] [cursor=pointer]:
                    - /url: http://localhost/immobilien/merkliste/
            - menuitem "Für Eigentümer" [ref=e29]:
              - link "Für Eigentümer" [ref=e30] [cursor=pointer]:
                - /url: http://localhost/fuer-eigentuemer/
              - menu [ref=e31]:
                - menuitem "Immobilie verkaufen" [ref=e32]:
                  - link "Immobilie verkaufen" [ref=e33] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/immobilie-verkaufen/
                - menuitem "Immobilie vermieten" [ref=e34]:
                  - link "Immobilie vermieten" [ref=e35] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/immobilie-vermieten/
                - menuitem "Eigentümerformular" [ref=e36]:
                  - link "Eigentümerformular" [ref=e37] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/eigentuemerformular/
                - menuitem "Lead-Generator" [ref=e38]:
                  - link "Lead-Generator" [ref=e39] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/lead-generator-mit-icons/
            - menuitem "Für Interessenten" [ref=e40]:
              - link "Für Interessenten" [ref=e41] [cursor=pointer]:
                - /url: http://localhost/interessenten/
              - menu [ref=e42]:
                - menuitem "Interessentenformular" [ref=e43]:
                  - link "Interessentenformular" [ref=e44] [cursor=pointer]:
                    - /url: http://localhost/interessenten/interessentenformular/
            - menuitem "Über uns" [ref=e45]:
              - link "Über uns" [ref=e46] [cursor=pointer]:
                - /url: http://localhost/ueber-uns/
              - menu [ref=e47]:
                - menuitem "Unser Team" [ref=e48]:
                  - link "Unser Team" [ref=e49] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/ueber-uns/
                - menuitem "Referenzen" [ref=e50]:
                  - link "Referenzen" [ref=e51] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/referenzen/
                - menuitem "Bewertungen" [ref=e52]:
                  - link "Bewertungen" [ref=e53] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/bewertungen/
                - menuitem "News" [ref=e54]:
                  - link "News" [ref=e55] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/news/
            - menuitem "Kontakt" [ref=e56]:
              - link "Kontakt" [ref=e57] [cursor=pointer]:
                - /url: http://localhost/kontakt/
        - generic [ref=e60]:
          - generic [ref=e63]:
            - generic [ref=e64]:
              - term [ref=e65]: "Tel.:"
              - definition [ref=e66]:
                - link "+49 241 44686-0" [ref=e67] [cursor=pointer]:
                  - /url: tel:+4924144686-0
            - generic [ref=e68]:
              - term [ref=e69]: "Mail:"
              - definition [ref=e70]:
                - link "info@onoffice.de" [ref=e71] [cursor=pointer]:
                  - /url: mailto:info@onoffice.de
          - generic [ref=e72]:
            - list [ref=e74]:
              - listitem [ref=e75]:
                - link "Facebook" [ref=e76] [cursor=pointer]:
                  - /url: https://www.facebook.com/onOffice.Software
                  - generic [ref=e77]: Facebook
                  - img [ref=e78]
              - listitem [ref=e80]:
                - link "YouTube" [ref=e81] [cursor=pointer]:
                  - /url: https://www.youtube.com/user/onOfficeSoftware
                  - generic [ref=e82]: YouTube
                  - img [ref=e83]
              - listitem [ref=e85]:
                - link "LinkedIn" [ref=e86] [cursor=pointer]:
                  - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                  - generic [ref=e87]: LinkedIn
                  - img [ref=e88]
              - listitem [ref=e90]:
                - link "Xing" [ref=e91] [cursor=pointer]:
                  - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                  - generic [ref=e92]: Xing
                  - img [ref=e93]
            - list [ref=e97]:
              - listitem [ref=e98]:
                - link "Deutsch" [ref=e99] [cursor=pointer]:
                  - /url: "#"
  - navigation "Breadcrumb" [ref=e100]:
    - list [ref=e101]:
      - listitem [ref=e102]:
        - link "Startseite" [ref=e103] [cursor=pointer]:
          - /url: http://localhost/
        - text: ">"
      - listitem [ref=e104]:
        - link "E2E Test – Formulare" [ref=e105] [cursor=pointer]:
          - /url: http://localhost/e2e-test-formulare/
        - text: ">"
      - listitem [ref=e106]: Kontaktformular
  - main [ref=e107]:
    - generic [ref=e110]:
      - generic [ref=e111]:
        - img [ref=e113]
        - paragraph [ref=e117]: Es ist ein Fehler aufgetreten. Bitte überprüfen Sie Ihre Angaben.
      - generic [ref=e118]:
        - generic [ref=e119]:
          - generic [ref=e120]: Ihre Kontaktdaten
          - paragraph [ref=e121]: "* Pflichtfelder"
        - generic [ref=e122]:
          - generic [ref=e123]:
            - generic [ref=e124]:
              - text: Anrede
              - generic [ref=e125]: Kontaktformular_E2E_1
            - combobox [ref=e126]
            - combobox "Anrede Kontaktformular_E2E_1" [ref=e130] [cursor=pointer]
          - generic [ref=e131]:
            - text: Vorname *
            - generic [ref=e132]: Kontaktformular_E2E_1
            - textbox "Vorname * Kontaktformular_E2E_1" [ref=e133]
          - generic [ref=e134]:
            - text: Nachname *
            - generic [ref=e135]: Kontaktformular_E2E_1
            - textbox "Nachname * Kontaktformular_E2E_1" [ref=e136]
          - generic [ref=e137]:
            - text: Straße
            - generic [ref=e138]: Kontaktformular_E2E_1
            - textbox "Straße Kontaktformular_E2E_1" [ref=e139]
          - generic [ref=e140]:
            - text: Plz
            - generic [ref=e141]: Kontaktformular_E2E_1
            - textbox "Plz Kontaktformular_E2E_1" [ref=e142]
          - generic [ref=e143]:
            - text: Ort
            - generic [ref=e144]: Kontaktformular_E2E_1
            - textbox "Ort Kontaktformular_E2E_1" [ref=e145]
          - generic [ref=e146]:
            - text: Telefonnummern *
            - generic [ref=e147]: Kontaktformular_E2E_1
            - textbox "Telefonnummern * Kontaktformular_E2E_1" [ref=e148]
          - generic [ref=e149]:
            - text: E-Mail *
            - generic [ref=e150]: Kontaktformular_E2E_1
            - textbox "E-Mail * Kontaktformular_E2E_1" [ref=e151]
          - generic [ref=e152]:
            - text: Nachricht *
            - generic [ref=e153]: Kontaktformular_E2E_1
            - textbox "Nachricht * Kontaktformular_E2E_1" [ref=e154]
          - generic [ref=e155]:
            - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Kontaktformular_E2E_1"
            - generic [ref=e157]:
              - text: Ich bin mit den
              - link "Datenschutzbestimmungen" [ref=e158] [cursor=pointer]:
                - /url: /datenschutz/
              - text: einverstanden. *
              - generic [ref=e159]: Kontaktformular_E2E_1
          - button "Absenden" [ref=e161] [cursor=pointer]
  - contentinfo [ref=e162]:
    - generic [ref=e165]:
      - generic [ref=e167]:
        - heading "Partner" [level=2] [ref=e168]
        - generic [ref=e169]:
          - link "Siegel für partner-1" [ref=e171] [cursor=pointer]:
            - /url: https://www.immobilienscout24.de
            - img "Siegel für partner-1" [ref=e173]
          - link "Siegel für partner-2" [ref=e175] [cursor=pointer]:
            - /url: https://www.immonet.de/
            - img "Siegel für partner-2" [ref=e177]
          - link "Siegel für partner-3" [ref=e179] [cursor=pointer]:
            - /url: https://www.immowelt.de/
            - img "Siegel für partner-3" [ref=e181]
          - link "Siegel für ka_horizontal_darkgreen_rgb" [ref=e183] [cursor=pointer]:
            - /url: https://www.kleinanzeigen.de/
            - img "Siegel für ka_horizontal_darkgreen_rgb" [ref=e185]
      - generic [ref=e187]:
        - generic [ref=e188]:
          - generic [ref=e189]:
            - heading "Anschrift" [level=2] [ref=e190]
            - generic [ref=e191]:
              - paragraph [ref=e192]:
                - text: Charlottenburger Allee 5
                - text: 52068 Aachen
                - text: Deutschland
              - generic [ref=e193]:
                - generic [ref=e194]:
                  - term [ref=e195]: "Tel.:"
                  - definition [ref=e196]:
                    - link "+49 241 44686-0" [ref=e197] [cursor=pointer]:
                      - /url: tel:+49241446860
                - generic [ref=e198]:
                  - term [ref=e199]: "Fax:"
                  - definition [ref=e200]:
                    - link "+49 44686-250" [ref=e201] [cursor=pointer]:
                      - /url: tel:+4944686250
                - generic [ref=e202]:
                  - term [ref=e203]: "E-Mail:"
                  - definition [ref=e204]:
                    - link "info@onoffice.de" [ref=e205] [cursor=pointer]:
                      - /url: mailto:info@onoffice.de
          - generic [ref=e206]:
            - heading "Social Media" [level=2] [ref=e207]
            - list [ref=e208]:
              - listitem [ref=e209]:
                - link "Facebook" [ref=e210] [cursor=pointer]:
                  - /url: https://www.facebook.com/onOffice.Software
                  - generic [ref=e211]: Facebook
                  - img [ref=e212]
              - listitem [ref=e214]:
                - link "YouTube" [ref=e215] [cursor=pointer]:
                  - /url: https://www.youtube.com/user/onOfficeSoftware
                  - generic [ref=e216]: YouTube
                  - img [ref=e217]
              - listitem [ref=e219]:
                - link "LinkedIn" [ref=e220] [cursor=pointer]:
                  - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                  - generic [ref=e221]: LinkedIn
                  - img [ref=e222]
              - listitem [ref=e224]:
                - link "Xing" [ref=e225] [cursor=pointer]:
                  - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                  - generic [ref=e226]: Xing
                  - img [ref=e227]
        - generic [ref=e230]:
          - heading "Abonnieren Sie unseren Newsletter" [level=2] [ref=e231]
          - paragraph [ref=e233]: Melden Sie sich heute kostenlos an und werden Sie als erster über neue Updates informiert.
          - generic [ref=e236]:
            - paragraph [ref=e237]: "* Pflichtfelder"
            - generic [ref=e238]:
              - generic [ref=e239]:
                - text: Vorname
                - generic [ref=e240]: Newsletter_2
                - textbox "Vorname Newsletter_2" [ref=e241]
              - generic [ref=e242]:
                - text: Nachname
                - generic [ref=e243]: Newsletter_2
                - textbox "Nachname Newsletter_2" [ref=e244]
              - generic [ref=e245]:
                - text: E-Mail *
                - generic [ref=e246]: Newsletter_2
                - textbox "E-Mail * Newsletter_2" [ref=e247]
              - generic [ref=e248]:
                - checkbox "Ich möchte mich für Ihren Newsletter anmelden * Newsletter_2"
                - generic [ref=e250]:
                  - text: Ich möchte mich für Ihren Newsletter anmelden *
                  - generic [ref=e251]: Newsletter_2
              - generic [ref=e252]:
                - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Newsletter_2"
                - generic [ref=e254]:
                  - text: Ich bin mit den
                  - link "Datenschutzbestimmungen" [ref=e255] [cursor=pointer]:
                    - /url: /datenschutz/
                  - text: einverstanden. *
                  - generic [ref=e256]: Newsletter_2
              - button "Absenden" [ref=e258] [cursor=pointer]
    - generic [ref=e261]:
      - generic [ref=e262]:
        - paragraph [ref=e263]:
          - generic [ref=e264]: © 2026 Mustermann Immobilien
        - navigation [ref=e265]:
          - list [ref=e266]:
            - listitem [ref=e267]:
              - link "Kontakt" [ref=e268] [cursor=pointer]:
                - /url: http://localhost/kontakt/
            - listitem [ref=e269]:
              - link "Impressum" [ref=e270] [cursor=pointer]:
                - /url: http://localhost/impressum/
            - listitem [ref=e271]:
              - link "Datenschutz" [ref=e272] [cursor=pointer]:
                - /url: http://localhost/datenschutz/
            - listitem [ref=e273]:
              - link "AGB" [ref=e274] [cursor=pointer]:
                - /url: http://localhost/agb/
            - listitem [ref=e275]:
              - button "Datenschutzeinstellungen" [ref=e276] [cursor=pointer]
            - listitem [ref=e277]:
              - button "Barriere gefunden? (Öffnet in neuem Tab)" [ref=e278] [cursor=pointer]: Barriere gefunden?
      - img [ref=e281]
  - complementary [active]:
    - dialog "Privatsphäre-Einstellungen" [ref=e307]:
      - generic [ref=e309]:
        - img "Firmenlogo" [ref=e310]
        - generic [ref=e311]:
          - heading "Privatsphäre-Einstellungen" [level=2] [ref=e312]
          - generic: Diese Seite nutzt Website-Tracking-Technologien von Dritten, um ihre Dienste anzubieten, stetig zu verbessern und Werbung entsprechend den Interessen der Nutzer anzuzeigen.
        - generic [ref=e313]:
          - link "Öffnen Datenschutzerklärung" [ref=e314] [cursor=pointer]:
            - /url: http://localhost/datenschutz/
            - text: Datenschutzerklärung
          - link "Öffnen Impressum" [ref=e315] [cursor=pointer]:
            - /url: http://localhost/impressum/
            - text: Impressum
        - generic [ref=e316]:
          - generic [ref=e317]:
            - generic: Essenziell
            - switch [checked] [disabled] [ref=e318]
          - generic [ref=e319] [cursor=pointer]:
            - generic: Funktionell
            - switch "Funktionell" [ref=e320]
          - generic [ref=e321] [cursor=pointer]:
            - generic: Statistiken
            - switch "Statistiken" [ref=e322]
      - generic [ref=e323]:
        - generic [ref=e325]:
          - button "Mehr Informationen" [ref=e326] [cursor=pointer]
          - button "Einstellungen speichern" [ref=e327] [cursor=pointer]
          - button "Ablehnen" [ref=e328] [cursor=pointer]
          - button "Alles akzeptieren" [ref=e329] [cursor=pointer]
        - link "Powered by Usercentrics Consent Management" [ref=e331] [cursor=pointer]:
          - /url: https://usercentrics.com/de/consent-management-platform-powered-by-usercentrics/?utm_source=banner_uc&utm_medium=referral&utm_content=v3
```

# Test source

```ts
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
> 108 |                     expect(msgText).toMatch(/Vielen Dank|Spam erkannt/);
      |                                     ^ Error: expect(received).toMatch(expected)
  109 |                 });
  110 | 
  111 |                 test('Validation: Required Fields', async () => {
  112 |                     await contactPage.submit();
  113 |                     
  114 |                     // Überprüfung des Füllfehlertextes für Pflichtfelder
  115 |                     const fieldError = contactPage.page.getByText(/Bitte füllen Sie das Pflichtfeld aus/i).first();
  116 |                     await expect(fieldError).toBeVisible();
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