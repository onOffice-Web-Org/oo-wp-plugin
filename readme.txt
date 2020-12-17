=== onOffice for WP-Websites ===
Contributors: jayay, anniken1
Tags: real estate, onoffice
Requires at least: 4.6
Tested up to: 5.6
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

= 2.7.10 =

**Fixes**

* Fix accordion boxes in the settings when using WP 5.5.
* Fix jQuery compability problem with picture checkbox in the estate list settings when using WP 5.5.
* Optimize estate types to be cached for faster page loading time.

= 2.7.9 =

**Fixes**

* Bugfix in detailview for mobile devices.

= 2.7.8 =

**Changes**

* Different titles for address fields in real-estate detail view in backend.
* Made language files complete.

= 2.7.7 =

**New**

* Fix option to reduce possible field values according to selected filter
* UI changes to automatically select estate type according to kind
* preview of how many results will be found

= 2.7.6 =

**Fixes**

* Fix adding fields in the configuration of the real estate detail view.

= 2.7.5 =

**Changes**

* Fix translations for image-type label in back-end.
* Fix sending multiple address form values for a multi select field in emails.

= 2.7.4 =

* Minor fixes

= 2.7.3 =

** Changes **

* New pdf filename.

= 2.7.2 =

**Changes**

* more legal characters for list and form names (shortcode names).

= 2.7.1 =

**Fixes**

* Fix of distinct fields.

= 2.7.0 =

**New**

* A new and faster pdf download.


More information can be found in our [changelog](changelog.txt).

== Upgrade Notice ==

= 2.7.6 =
Fix adding fields in the configuration of the real estate detail view.

= 2.7.5 =
Fix translations for image-type label in back-end and fix sending multiple address form values for a multi select field in emails.

= 2.7.4 =
Minor fixes.

= 2.7.3 =
New pdf filename.

= 2.7.2 =
More legal characters for list and form names.

= 2.7.1 =
Fix of distinct fields.

= 2.7.0 =
A new and faster pdf download.

= 2.6.2 =
Removal of illegal characters in shortcode names on saving.

= 2.6.1 =
Representation of the search criterie fields in forms compatible with onOffice enterprise.

= 2.6.0 =
New estate templates and dependencies updated.

= 2.5.5 =
Fix submission of contact form with deactivated estate id field in onOffice enterprise.

= 2.5.4 =
More data in the emails by form submit, jQuery internal reorganization.

= 2.5.3 =
jQuery internal reorganization, notice in the geo fields administration.

= 2.5.2 =
Fix errors in estate detail view configuration.


== Arbitrary section ==

= Development =

Development takes place in our [Github repository](https://github.com/onOfficeGmbH/oo-wp-plugin).

= Legal =

onOffice Terms and Conditions: https://en.onoffice.com/terms-and-conditions.xhtml