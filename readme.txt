=== onOffice for WP-Websites ===
Contributors: jayay, anniken1
Tags: real estate, onoffice
Requires at least: 4.6
Tested up to: 6.0
Requires PHP: 7.3
Stable tag: 4.3
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

= Unreleased =

**Fixed**

* The limit of 500 estates per page is now communicated in the estate list settings.

= 4.3 (2022-09-21) =

**Changed**

* We improved the controls for whether reference estates should be displayed in estate lists.

**Fixed**

* The translation of templates is no longer done in the "onoffice" text domain. All text will now be translated in the "onoffice-for-wp-websites" text domain, which enables us to use the WordPress translation platform for the included templates, too. This fixes translation problems for "Deutsch (Sie)" where some text was shown in English. The existing translation files will be kept so that existing templates continue to work as before.
* You can now select default values for the "Contact type" field in contact forms. Previously, they lead to an error.
* The custom label for the "GDPR status" field in a form can now be set and will show up in the frontend.
* Fixed some PHP messages when saving estate lists.

= 4.2 (2022-07-07) =

**Added**

* The shortcodes are now also shown in the settings for lists and we have added a button to more easily copy them.

**Changed**

* When saving a list or form, the page now fully reloads.

**Fixed**

* The form overview no longer wrongly displays that all forms use the default email address. If a form overrides the default email, that override will now show up correctly in the overview.
* The plugin no longer overwrites tables in other plugins. Specifically, the plugin is now compatible with the Rank Math SEO plugin's redirection and 404 monitor modules.

= 4.1 (2022-06-27) =

**Added**

* The detail view shortcode is now recognized when it is used in the meta fields of a page. This makes it easier to use Advanced Custom Fields (ACF).

**Fixed**

* The labels for email address, phone and fax numbers no longer contain "default", which was confusing for website visitors.
* The error causing notices about undefined properties in the estate and address list overviews have been fixed.
* When using child themes, the templates and CSS are now loading correctly.
* The credentials no longer need to be newly entered after adding the encryption constant.
* After uninstalling the plugin, there were some options left in the database. Those are now properly removed.

= 4.0 (2022-06-21) =

**Removed**

* Drop support for older PHP versions. The minimum version is now PHP 7.3.

**Added**

* You can now set a default email address that your forms can use, so that you can more easily change it.

**Changed**

* We now load a CSS file from the template folder. This allows you to more easily modify the CSS for your templates. If you have a file "onoffice-style.css" in your template folder (e.g. at wp-content/plugins/onoffice-personalized/templates/onoffice-style.css), we will load that instead of our default styles. To remain backwards compatible, if that file is not found, we continue loading our old styles.
* We removed some redundant fields for the contact person in the detail view and improved their labels in the detail view settings. This change is backwards compatible, your templates will continue to work without changes.

**Fixed**

* Detail view pages are now redirected properly. For example, if you have configured the URL to contain the title and you change the estate's title, when someone opens the old link they will be redirected to the new one.

= 3.2 (2022-05-23) =

**Added**

* In estate lists, you can now sort by any field of suitable field types, even custom fields.
* Besides movie links, other link types from onOffice enterprise can now be configured for the detail view. The included templates support these new link types.
* You can now use Google reCAPTCHA v3 keys.

**Changed**

* The controls in the estate list settings for sorting were improved.

**Fixed**

* The warning about the deactivation of the duplicate check introduced in version 2.22.2 can now be closed correctly and does not appear on new installations.
* For contact forms, the message field no longer appears twice.
* In the settings, clicking on the labels now sets the correct controls.
* We improved the explanation of how to link addresses from contact forms with the estates.

= 3.1 (2022-05-04) =

**Changed**

* The selection of the template for lists and forms has been improved.

**Fixed**

* When creating new lists or forms, the correct template will be selected by default.
* Address lists can no longer be saved without a name.
* In interest forms, search criteria fields that are displayed as a select field can now be set as required.
* When updating from version 2.21.6 or earlier, the setting for showing reference estates is now set for all estate list for backwards compatiblity.

= 3.0 (2022-04-19) =

**Added**

* In the overview for estate lists, address lists, and forms:
  * You can now search for a specific item with the search form in the top right of the overview.
  * You can now choose how many entries to show per page in the "Screen options".

**Removed**

* Drop support for older PHP versions. The minimum version is now PHP 7.2.

**Fixed**

* If there are many regions configured in onOffice enterprise, the estate list settings will now load much faster.
* The default template for the detail view now groups together the contact person's fields that belong together. For example, title, first and last name are grouped as one line.
* The "Show Estate Status" checkbox in the estate list settings is no longer shown twice.
* Some invalid fields can no longer be selected for the applicant search form.

= Previous changes =

You can view all previous changes in our [changelog.txt](https://github.com/onOfficeGmbH/oo-wp-plugin/blob/master/changelog.txt).

== Arbitrary section ==

= Development =

Development takes place in our [Github repository](https://github.com/onOfficeGmbH/oo-wp-plugin).

= Legal =

onOffice Terms and Conditions: https://en.onoffice.com/terms-and-conditions.xhtml
