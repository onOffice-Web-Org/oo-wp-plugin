# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: modern-contact-form.spec.ts >> Kontaktformular: Multi-Theme Engine & Visual Regression >> Theme: onoffice-classic >> Functional Scenarios >> Submission: Happy Path
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
  - link "Zum Inhalt wechseln" [ref=e2] [cursor=pointer]:
    - /url: "#primary"
  - banner [ref=e3]:
    - generic [ref=e4]:
      - generic [ref=e6]:
        - link "Mustermann Immobilien" [ref=e8] [cursor=pointer]:
          - /url: http://localhost/
          - img "Mustermann Immobilien" [ref=e9]
        - button "Menü öffnen" [ref=e10] [cursor=pointer]:
          - img [ref=e11]
      - generic:
        - navigation "Hauptmenü" [ref=e13]:
          - menu [ref=e14]:
            - menuitem "Startseite" [ref=e15]:
              - link "Startseite" [ref=e16] [cursor=pointer]:
                - /url: http://localhost/
            - menuitem "Immobilien" [ref=e17]:
              - link "Immobilien" [ref=e18] [cursor=pointer]:
                - /url: http://localhost/immobilien/
              - menu [ref=e19]:
                - menuitem "Kauf" [ref=e20]:
                  - link "Kauf" [ref=e21] [cursor=pointer]:
                    - /url: http://localhost/immobilien/kauf/
                - menuitem "Miete" [ref=e22]:
                  - link "Miete" [ref=e23] [cursor=pointer]:
                    - /url: http://localhost/immobilien/miete/
                - menuitem "Merkliste" [ref=e24]:
                  - link "Merkliste" [ref=e25] [cursor=pointer]:
                    - /url: http://localhost/immobilien/merkliste/
            - menuitem "Für Eigentümer" [ref=e26]:
              - link "Für Eigentümer" [ref=e27] [cursor=pointer]:
                - /url: http://localhost/fuer-eigentuemer/
              - menu [ref=e28]:
                - menuitem "Immobilie verkaufen" [ref=e29]:
                  - link "Immobilie verkaufen" [ref=e30] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/immobilie-verkaufen/
                - menuitem "Immobilie vermieten" [ref=e31]:
                  - link "Immobilie vermieten" [ref=e32] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/immobilie-vermieten/
                - menuitem "Eigentümerformular" [ref=e33]:
                  - link "Eigentümerformular" [ref=e34] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/eigentuemerformular/
                - menuitem "Lead-Generator" [ref=e35]:
                  - link "Lead-Generator" [ref=e36] [cursor=pointer]:
                    - /url: http://localhost/fuer-eigentuemer/lead-generator-mit-icons/
            - menuitem "Für Interessenten" [ref=e37]:
              - link "Für Interessenten" [ref=e38] [cursor=pointer]:
                - /url: http://localhost/interessenten/
              - menu [ref=e39]:
                - menuitem "Interessentenformular" [ref=e40]:
                  - link "Interessentenformular" [ref=e41] [cursor=pointer]:
                    - /url: http://localhost/interessenten/interessentenformular/
            - menuitem "Über uns" [ref=e42]:
              - link "Über uns" [ref=e43] [cursor=pointer]:
                - /url: http://localhost/ueber-uns/
              - menu [ref=e44]:
                - menuitem "Unser Team" [ref=e45]:
                  - link "Unser Team" [ref=e46] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/ueber-uns/
                - menuitem "Referenzen" [ref=e47]:
                  - link "Referenzen" [ref=e48] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/referenzen/
                - menuitem "Bewertungen" [ref=e49]:
                  - link "Bewertungen" [ref=e50] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/bewertungen/
                - menuitem "News" [ref=e51]:
                  - link "News" [ref=e52] [cursor=pointer]:
                    - /url: http://localhost/ueber-uns/news/
            - menuitem "Kontakt" [ref=e53]:
              - link "Kontakt" [ref=e54] [cursor=pointer]:
                - /url: http://localhost/kontakt/
        - generic [ref=e57]:
          - generic [ref=e60]:
            - generic [ref=e61]:
              - term [ref=e62]: "Tel.:"
              - definition [ref=e63]:
                - link "+49 241 44686-0" [ref=e64] [cursor=pointer]:
                  - /url: tel:+4924144686-0
            - generic [ref=e65]:
              - term [ref=e66]: "Mail:"
              - definition [ref=e67]:
                - link "info@onoffice.de" [ref=e68] [cursor=pointer]:
                  - /url: mailto:info@onoffice.de
          - generic [ref=e69]:
            - list [ref=e71]:
              - listitem [ref=e72]:
                - link "Facebook" [ref=e73] [cursor=pointer]:
                  - /url: https://www.facebook.com/onOffice.Software
                  - generic [ref=e74]: Facebook
                  - img [ref=e75]
              - listitem [ref=e77]:
                - link "YouTube" [ref=e78] [cursor=pointer]:
                  - /url: https://www.youtube.com/user/onOfficeSoftware
                  - generic [ref=e79]: YouTube
                  - img [ref=e80]
              - listitem [ref=e82]:
                - link "LinkedIn" [ref=e83] [cursor=pointer]:
                  - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                  - generic [ref=e84]: LinkedIn
                  - img [ref=e85]
              - listitem [ref=e87]:
                - link "Xing" [ref=e88] [cursor=pointer]:
                  - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                  - generic [ref=e89]: Xing
                  - img [ref=e90]
            - list [ref=e94]:
              - listitem [ref=e95]:
                - link "Deutsch" [ref=e96] [cursor=pointer]:
                  - /url: "#"
  - navigation "Breadcrumb" [ref=e97]:
    - list [ref=e98]:
      - listitem [ref=e99]:
        - link "Startseite" [ref=e100] [cursor=pointer]:
          - /url: http://localhost/
        - text: ">"
      - listitem [ref=e101]:
        - link "E2E Test – Formulare" [ref=e102] [cursor=pointer]:
          - /url: http://localhost/e2e-test-formulare/
        - text: ">"
      - listitem [ref=e103]: Kontaktformular
  - main [ref=e104]:
    - generic [ref=e107]:
      - generic [ref=e108]:
        - img [ref=e110]
        - paragraph [ref=e114]: Es ist ein Fehler aufgetreten. Bitte überprüfen Sie Ihre Angaben.
      - generic [ref=e115]:
        - generic [ref=e116]:
          - generic [ref=e117]: Ihre Kontaktdaten
          - paragraph [ref=e118]: "* Pflichtfelder"
        - generic [ref=e119]:
          - generic [ref=e120]:
            - text: Anrede
            - generic [ref=e121]: Kontaktformular_E2E_1
          - combobox [ref=e122]
          - combobox "Anrede Kontaktformular_E2E_1" [ref=e126] [cursor=pointer]
        - generic [ref=e127]:
          - text: Vorname *
          - generic [ref=e128]: Kontaktformular_E2E_1
          - textbox "Vorname * Kontaktformular_E2E_1" [ref=e129]: E2E-Tester
        - generic [ref=e130]:
          - text: Nachname *
          - generic [ref=e131]: Kontaktformular_E2E_1
          - textbox "Nachname * Kontaktformular_E2E_1" [ref=e132]: Theme-onoffice-classic
        - generic [ref=e133]:
          - text: Straße
          - generic [ref=e134]: Kontaktformular_E2E_1
          - textbox "Straße Kontaktformular_E2E_1" [ref=e135]
        - generic [ref=e136]:
          - text: Plz
          - generic [ref=e137]: Kontaktformular_E2E_1
          - textbox "Plz Kontaktformular_E2E_1" [ref=e138]
        - generic [ref=e139]:
          - text: Ort
          - generic [ref=e140]: Kontaktformular_E2E_1
          - textbox "Ort Kontaktformular_E2E_1" [ref=e141]
        - generic [ref=e142]:
          - text: Telefonnummern *
          - generic [ref=e143]: Kontaktformular_E2E_1
          - textbox "Telefonnummern * Kontaktformular_E2E_1" [ref=e144]: +49 123 456789
        - generic [ref=e145]:
          - text: E-Mail *
          - generic [ref=e146]: Kontaktformular_E2E_1
          - textbox "E-Mail * Kontaktformular_E2E_1" [ref=e147]: qa-test@onoffice.de
        - generic [ref=e148]:
          - text: Nachricht *
          - generic [ref=e149]: Kontaktformular_E2E_1
          - textbox "Nachricht * Kontaktformular_E2E_1" [ref=e150]: Automated test for onoffice-classic
        - generic [ref=e151]:
          - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Kontaktformular_E2E_1"
          - generic [ref=e153]:
            - text: Ich bin mit den
            - link "Datenschutzbestimmungen" [ref=e154] [cursor=pointer]:
              - /url: /datenschutz/
            - text: einverstanden. *
            - generic [ref=e155]: Kontaktformular_E2E_1
        - button "Absenden" [ref=e157] [cursor=pointer]
  - contentinfo [ref=e158]:
    - generic [ref=e161]:
      - generic [ref=e163]:
        - generic [ref=e164]: Partner
        - generic [ref=e165]:
          - link "Siegel für partner-1" [ref=e167] [cursor=pointer]:
            - /url: https://www.immobilienscout24.de
            - img "Siegel für partner-1" [ref=e169]
          - link "Siegel für partner-2" [ref=e171] [cursor=pointer]:
            - /url: https://www.immonet.de/
            - img "Siegel für partner-2" [ref=e173]
          - link "Siegel für partner-3" [ref=e175] [cursor=pointer]:
            - /url: https://www.immowelt.de/
            - img "Siegel für partner-3" [ref=e177]
          - link "Siegel für ka_horizontal_darkgreen_rgb" [ref=e179] [cursor=pointer]:
            - /url: https://www.kleinanzeigen.de/
            - img "Siegel für ka_horizontal_darkgreen_rgb" [ref=e181]
      - generic [ref=e182]:
        - generic [ref=e183]:
          - generic [ref=e184]: Anschrift
          - generic [ref=e185]:
            - paragraph [ref=e186]:
              - text: Charlottenburger Allee 5
              - text: 52068 Aachen
              - text: Deutschland
            - generic [ref=e187]:
              - generic [ref=e188]:
                - term [ref=e189]: "Tel.:"
                - definition [ref=e190]:
                  - link "+49 241 44686-0" [ref=e191] [cursor=pointer]:
                    - /url: tel:+49241446860
              - generic [ref=e192]:
                - term [ref=e193]: "Fax:"
                - definition [ref=e194]:
                  - link "+49 44686-250" [ref=e195] [cursor=pointer]:
                    - /url: tel:+4944686250
              - generic [ref=e196]:
                - term [ref=e197]: "E-Mail:"
                - definition [ref=e198]:
                  - link "info@onoffice.de" [ref=e199] [cursor=pointer]:
                    - /url: mailto:info@onoffice.de
        - generic [ref=e200]:
          - generic [ref=e201]: Social Media
          - list [ref=e202]:
            - listitem [ref=e203]:
              - link "Facebook" [ref=e204] [cursor=pointer]:
                - /url: https://www.facebook.com/onOffice.Software
                - generic [ref=e205]: Facebook
                - img [ref=e206]
            - listitem [ref=e208]:
              - link "YouTube" [ref=e209] [cursor=pointer]:
                - /url: https://www.youtube.com/user/onOfficeSoftware
                - generic [ref=e210]: YouTube
                - img [ref=e211]
            - listitem [ref=e213]:
              - link "LinkedIn" [ref=e214] [cursor=pointer]:
                - /url: https://www.linkedin.com/company/onoffice-software-gmbh/
                - generic [ref=e215]: LinkedIn
                - img [ref=e216]
            - listitem [ref=e218]:
              - link "Xing" [ref=e219] [cursor=pointer]:
                - /url: https://www.xing.com/companies/onofficesoftwaregmbh
                - generic [ref=e220]: Xing
                - img [ref=e221]
      - generic [ref=e224]:
        - generic [ref=e225]: Abonnieren Sie unseren Newsletter
        - paragraph [ref=e227]: Melden Sie sich heute kostenlos an und werden Sie als erster über neue Updates informiert.
        - generic [ref=e230]:
          - paragraph [ref=e232]: "* Pflichtfelder"
          - generic [ref=e233]:
            - text: Vorname
            - generic [ref=e234]: Newsletter_2
            - textbox "Vorname Newsletter_2" [ref=e235]
          - generic [ref=e236]:
            - text: Nachname
            - generic [ref=e237]: Newsletter_2
            - textbox "Nachname Newsletter_2" [ref=e238]
          - generic [ref=e239]:
            - text: E-Mail *
            - generic [ref=e240]: Newsletter_2
            - textbox "E-Mail * Newsletter_2" [ref=e241]
          - generic [ref=e242]:
            - checkbox "Ich möchte mich für Ihren Newsletter anmelden * Newsletter_2"
            - generic [ref=e244]:
              - text: Ich möchte mich für Ihren Newsletter anmelden *
              - generic [ref=e245]: Newsletter_2
          - generic [ref=e246]:
            - checkbox "Ich bin mit den Datenschutzbestimmungen einverstanden. * Newsletter_2"
            - generic [ref=e248]:
              - text: Ich bin mit den
              - link "Datenschutzbestimmungen" [ref=e249] [cursor=pointer]:
                - /url: /datenschutz/
              - text: einverstanden. *
              - generic [ref=e250]: Newsletter_2
          - button "Absenden" [ref=e252] [cursor=pointer]
    - generic [ref=e255]:
      - generic [ref=e256]:
        - paragraph [ref=e257]:
          - generic [ref=e258]: © 2026 Mustermann Immobilien
        - navigation [ref=e259]:
          - list [ref=e260]:
            - listitem [ref=e261]:
              - link "Kontakt" [ref=e262] [cursor=pointer]:
                - /url: http://localhost/kontakt/
            - listitem [ref=e263]:
              - link "Impressum" [ref=e264] [cursor=pointer]:
                - /url: http://localhost/impressum/
            - listitem [ref=e265]:
              - link "Datenschutz" [ref=e266] [cursor=pointer]:
                - /url: http://localhost/datenschutz/
            - listitem [ref=e267]:
              - link "AGB" [ref=e268] [cursor=pointer]:
                - /url: http://localhost/agb/
            - listitem [ref=e269]:
              - link "Datenschutzeinstellungen" [ref=e270] [cursor=pointer]:
                - /url: "#"
      - img [ref=e273]
  - complementary [active]:
    - dialog "Privatsphäre-Einstellungen" [ref=e299]:
      - generic [ref=e301]:
        - img "Firmenlogo" [ref=e302]
        - generic [ref=e303]:
          - heading "Privatsphäre-Einstellungen" [level=2] [ref=e304]
          - generic: Diese Seite nutzt Website-Tracking-Technologien von Dritten, um ihre Dienste anzubieten, stetig zu verbessern und Werbung entsprechend den Interessen der Nutzer anzuzeigen.
        - generic [ref=e305]:
          - link "Öffnen Datenschutzerklärung" [ref=e306] [cursor=pointer]:
            - /url: http://localhost/datenschutz/
            - text: Datenschutzerklärung
          - link "Öffnen Impressum" [ref=e307] [cursor=pointer]:
            - /url: http://localhost/impressum/
            - text: Impressum
        - generic [ref=e308]:
          - generic [ref=e309]:
            - generic: Essenziell
            - switch [checked] [disabled] [ref=e310]
          - generic [ref=e311] [cursor=pointer]:
            - generic: Funktionell
            - switch "Funktionell" [ref=e312]
          - generic [ref=e313] [cursor=pointer]:
            - generic: Statistiken
            - switch "Statistiken" [ref=e314]
      - generic [ref=e315]:
        - generic [ref=e317]:
          - button "Mehr Informationen" [ref=e318] [cursor=pointer]
          - button "Einstellungen speichern" [ref=e319] [cursor=pointer]
          - button "Ablehnen" [ref=e320] [cursor=pointer]
          - button "Alles akzeptieren" [ref=e321] [cursor=pointer]
        - link "Powered by Usercentrics Consent Management" [ref=e323] [cursor=pointer]:
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