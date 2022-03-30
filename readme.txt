=== onOffice for WP-Websites ===
Contributors: jayay, anniken1
Tags: real estate, onoffice
Requires at least: 4.6
Tested up to: 5.9
Requires PHP: 7.0
License: AGPL 3.0
License URI: https://www.gnu.org/licenses/agpl-3.0.html

Integrate real estates, contact forms and contact persons from the onOffice Software into your WordPress website.

== Description ==

Integrate real estates, contact forms and contact persons from the onOffice Software into your website. Thanks to shortcodes, the plugin is compatible with every WordPress theme.

The plugin includes three modules:

* real estates
* addresses
* forms

Using a short code, you bring real estates, addresses or forms to your website - you are as flexible in design as you are used to from onOffice.

The user-friendly plugin enables a quick link between onOffice and your WordPress page: Present real estate and your team on the website and generate leads via forms. You stay in control and are 100% flexible.

You can find tutorials, documentation and support on our [documentation website](https://wpplugindoc.onoffice.de/?lang=en).

= Real estate =
Create lists, design the real estate presentation and offer synopsis for downloading with a few easy steps.

* **Publication**: One click in the software is enough to publish the property on your WordPress website.
* **List view**: Present your properties in clear lists. It is entirely up to you which properties are represented. The lists can be inserted anywhere on the website using short codes.
* **Detailed view**: Comfortably structure the detail view with checkboxes and drag & drop and easily determine which information is displayed.

= Addresses =
The website is your business card on the internet. Create trust with a professional self-presentation.

* **Team presentation**: The address module accesses the data of the employees. The address display is ideal for presenting the team.

= Forms =
Simplify data maintenance: The information from forms is automatically transmitted to onOffice by the plugin.

* **Contact**: Classic contact form in which the user enters his message and contact information.
* **Interested parties**: Proactively serve prospective customers! The prospective customer states the contact data and their search desire. Address and search criteria are created directly in onOffice and provided with suitable offers.
* **Owner**: Acquire new orders with your website! In addition to the contact details, the owner provides information about the property. Address and property can be processed immediately in onOffice.
* **Search for interested parties**: Convince potential sellers! Show that you have suitable prospects in your inventory. The interested parties are displayed together with the search criteria (but without personal data).

= Further features =
The plugin offers further practical functions with which you can further professionalize your web presence.

* User-friendly watch list or favorites function
* Two map types: OpenStreetMap or Google Maps
* Show all linked media per address / property


== Installation ==

= Automatic =
Install the plugin from the plugins back-end page of your WordPress website.

= Manual =
Go to our page on the [WordPress Plugin Directory](https://wordpress.org/plugins/onoffice-for-wp-websites/) and [download the zip](https://downloads.wordpress.org/plugin/onoffice-for-wp-websites.zip). Upload the new zip to your WordPress website.

= Create the directory for individual templates =
[Download the zip](https://downloads.wordpress.org/plugin/onoffice-for-wp-websites.zip) and copy the contents of the `templates.dist` directory to a subfolder `templates` of a new plugin folder named `onoffice-personalized`.

Start editing inside the `onoffice-personalized` folder.


**IMPORTANT**: Although it is safe to disable the plugin, DELETING IT WILL WIPE ALL PLUGIN-RELATED DATA FROM THE DATABASE. WE DO NOT PROVIDE ANY WARRANTY FOR DATA LOSS!

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 2.22.5 =

**Fixes**

* When there are no estates that show up on the map, there is no longer a gap in the estate list where the map would be.

= 2.22.4 =

**Fixes**

* Fixes detail view being inaccessible for reference estates.

**Changes**

* Adds a checkbox to control whether the detail pages of reference estates are accessible or should be restricted with 404 Not Found.

= 2.22.3 =

**Changes**

* The list views in forms, addresses and estates got enriched with additional information. 

= 2.22.2 =

**Changes**

* Deactivates the duplicate check for addresses created with forms.

= 2.22.1 =

**Changes**

* For the form types contact form, prospect form, owner form an additional setting that defines the contact type is introduced. Before that it was not possible to select the contact type the address is created with.

= 2.22.0 =

**Changes**

* After installing the WP-Plugin there is no longer a default form generated. This is because previously it was impossible to enter an e-mail-address in the form by default.

* Compatibility with WordPress 5.9 has been tested.

= 2.21.8 =

**Changes**

* In enterprise forms where the HTML attribute step is used the value is now set to 1 instead of 0,1 for example for all values that are floats. Users can still input decimal numbers, but is more natural for most fields.

= 2.21.7 =

**Changes**

* Users can decide in the plugin whether the references are output or not, without needing to set up a filter in onOffice.

= 2.21.6 =

**Fixes**

* Reverts the changes from release v2.21.5 due to bugs.

= 2.21.5 =

**Changes**

* The Id in the URL is now also shown like  [url]/1234-sonnige-waermende-10qm-wohnung-m/ and makes a redirect to [url]/1234-sonnige-waermende-10qm-wohnung-mit/

= 2.21.4 =

**Changes**

* All settings are bundled into a single page called "Settings" (effectively, remove the "Modules" page) with a changed order.

= 2.21.3 =

**Fixes**

* The menu entry now behaves the same way as the other default menu entries.

= 2.21.2 =

**Fixes**

* MonsterInsights plugin can now be used together with our WP plugin.

= 2.21.1 =

**Changes**

* When creating new contact forms, the option "Create address" is now checked.

= 2.21.0 =

**Changes**

* Display all of a contact person's data on the estate detail page

= 2.20.12 =

**Fixes**

* The property status is now displayed in the detail view below the property title and no longer in the fields as marketing status.

* Separator line, black hint, red hint, file and user are no longer selectable in the configuration for any list or form in the backend. (also not for addresses and estates).

= 2.20.11 =

**Changes**

* When you submit the form, the mail that is sent does now also contains  information about the  newsletter activation fields in case they were not selected in the form.

= 2.20.10 =

**Changes**

* The explanation of the option "Show title in URL" can now be translated

* In local host the error message "Please configure your API credentials first" is no longer displayed and similar estates are now shown as expected.

= 2.20.9 =

**Fixes**

* Wherever fields are used (real estate, addresses, forms) the ID of the field, which appears after the field tile is expanded, can now be selected and copied.

= 2.20.8 =

**Fixes**

* The field "vermarktungsstatus" is no longer added to the detail view when the plugin is installed.

* The saving message now also appears in case a numeric field is added to the configuration.

= 2.20.7 =

**Fixes**

* The fallback e-mail description layout has been fixed.

* The default value for radius is now displayed in the frontend porperty list.

= 2.20.6 =

**Fixes**

* Errors occured when creating or editing any form and also in the frontend on pages where the forms' shortcodes are used. The cause of the errors has been found and the errors were fixed.

* Fixed minor bugs.

= 2.20.5 =

**Changes**

* The option for indexing PDF brochures has now an understandable label which tells the user what he/ she can expact when selecting it.

* In the default_detail.php template, free texts are now displayed with line breaks.

= 2.20.4 =

**Changes**

* The plugin no longer overwrites the theme folder when updating the theme.

= 2.20.3 =

**Changes**

* The plugin is now represented by the onOffice logo.

= 2.20.2 =

**Changes**

* Contact, interest, and owner forms now all have a checkbox that configures whether an address is created. The dublicate checkbox gets a new label Check for Duplicates (override existing address if the email is the same)

= 2.20.1 =

**Changes**

* The URL now also contains id and title.

= 2.20.0 =

**Changes**

* Allow duplicating of all lists.

= 2.19.7 =

**Changes**

* When indexing for a PDF-Exposé is turned of, that's now applied to all search engines.

= 2.19.6 =

**Changes**

* Adjusted the hint text, label and position of the email-address field.

= 2.19.5 =

**Changes**

* Configuration of fallback email address is now required in the settings for contact forms.

= 2.19.4 =

**Changes**

* Removal of the field options "Filterable", "Hidden" and "Reduce values accordings to selected filter" in unit list settings.

= 2.19.3 =

* Removal of contact person's data (Ansprechpartner der Einheit) in unit list.

= 2.19.2 =

**Changes**

* Add missing code from release 2.19.1

= 2.19.1 =

**Changes**

* The estate status is now also displayed in the detail view.

= 2.19.0 =

**Changes**

* WP-Plugin - communication with the onOffice API only takes place in the relevant cases

= 2.18.2 =

**Fixes**

* Fix number fields' default values allowing text

= 2.18.1 =

**Fixes**

* Fix for the radius search of similar properties

= 2.18.0 =

**New**

* The templates and translations can now be located in a new folder called `onoffice-theme/languages` and `onoffice-theme/templates` inside the WP theme, respectively.

= 2.17.1 =

**Fixes**

* The information on whether the newsletter checkbox was checked is now represented in the email.

= 2.17.0 =

**New**

* Pagination can now be handled by the theme or the plugin.

= 2.16.0 =

**Changes**

* Both contact form as well as interested party form lead to an e-mail with an OpenImmo Feedback XML attachment file.

= 2.15.0 =

**New**

* Form inputs can now have individual captions. Those can be set in the form-settings in the back-end.

= 2.14.1 =

**Fixes**

* minor fixes

= 2.14.0 =

**New**

* Add an option to duplicate listViews.

= 2.13.3 =

**Fixes**

* No e-mail when select newsletter-option *

= 2.13.2 =

**Changes**

* Hide configuration option "Systembenutzer (Adresse ist mit Benutzer verknüpft)" in contact type configuration *

= 2.13.1 =

**Changes**

* Avoid display of Faktura fields in WP-Plugin *

= 2.13.0 =

**New**

* API-credentials can now be stored in an encrypted manner.

= 2.12.1 =

**Changes**

* Remove field `krit_bemerkung_oeffentlich` from applicant search forms, since it can't be used as intended.
* Increase "Tested up to" in readme.txt to WP 5.7.

= 2.12.0 =

**New**

* Automatically creates a default contact form during plugin setup.

= 2.11.0 =

**New**

* PDF brochures can be prevented from being indexed by Google bot. In order to configure this, a new checkbox was added in the settings page. Exclusion is being achieved through the HTTP header "X-Robots-Tag: noindex".

= 2.10.3 =

**Fixes**

* Reverts the changes from release v2.10.2 due to backwards-compability concerns.

= 2.10.2 =

**Fixes**

* The visitor can no longer visit the detail page of a reference estate.
* The default list views and favorites list views exclude reference estates.

= 2.10.1 =

**Fixes**

* Adds changes to composer.lock so that the new dependency (select2) is acutally included in the plugin.
* Fixes faulty HTML <option> tag generated by fields.php.

= 2.10.0 =

**Changes**

* select2 is being used for select fields in the front-end. A current copy of the shipped fields.php needs to be copied into the templates directory for this change to take effect.


= 2.9.0 =

**New**

* The similar estates view has its own tab in the back-end. The fields to be shown in the similar estates view can be configured and are no longer hard-coded.

= 2.8.3 =

**Changes**

* Reference estates, reserved and sold ones are not being shown in the similar estates view anymore

= 2.8.2 =

**Fixes**

* Fix reflection problem in di and php 7.0

**Changes**

* Update development and deployment tools

= 2.8.1 =

**Fixes**

* Fix translations for forgotten lazy translated strings

= 2.8.0 =

**Changes**

* Changes of the text domain

= 2.7.18 =

**Fixes**

* Fix for similar estates in foreign language content

= 2.7.17 =

**Fixes**

* Fixes for WordPress 5.6

= 2.7.16 =

**Fixes**

* Fix of incorrect value for empty real estate fields

= 2.7.15 =

**Fixes**

* Fix of pagination in static pages

= 2.7.14 =

**Fixes**

* Fix of user defined sort in the real-estates list configuration

= 2.7.13 =

**Fixes**

* Fix of missing contact photo in the detail estate view setting.

= 2.7.12 =

**Fixes**

* Fix pagination problem when using WP 5.5

= 2.7.11 =

**Fixes**

* Fix WPML-Language selector in the real-estate-detail view.


== Arbitrary section ==

= Development =

Development takes place in our [Github repository](https://github.com/onOfficeGmbH/oo-wp-plugin).

= Legal =

onOffice Terms and Conditions: https://en.onoffice.com/terms-and-conditions.xhtml
