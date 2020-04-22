=== onOffice for WP-Websites ===
Contributors: jayay, anniken1
Tags: real estate, onoffice
Requires at least: 4.6
Tested up to: 5.4
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

= 2.5.4 =

**Changes**

* jQuery internal reorganization.
* More data in the emails by form submit.

= 2.5.3 =

**Fixes**

* Notice in the geo fields administration.
* jQuery internal reorganization.

= 2.5.2 =

**Fixes**

* Fix detail view configuration of fields, if field category name exists in both address and estate module

= 2.5.1 =

**Fixes**

* fix issues with geo fields on estate list

= 2.5.0 =

**Changes**

* internal reorganization

= 2.4.2 =

**Fixes**

* Fix rendering of regional addition in forms.
* Fix of multiselect fields with  multiple values in forms.
* Fix handling of boolean values after form submits.

= 2.4.1 =

**Fixes**

* Fix for newsletter checkbox (only for applicant form).

= 2.4.0 =

**New**

* Ability to set preset values per input in forms.

= 2.3.1 =

**Fixes**

* Fix handling of deactivated fields in back-end estate list configuration.
* Fix handling of search parameters of address list pagination.

= 2.3.0 =

**Changes**

* Selection of default sort direction in the estate list configuration for the user sorting in the front-end.

**Fixes**

* Fix handling of deactivated fields in back-end list configuration.

= 2.2.2 =

**Fixes**

* Remove contact form from default estate list template.
* Fix variable name collision in estate detail and map template.
* Add missing languages to the language mapping.

**Changes**

* The Regional Addition can now be used as both single select or multi select field.

= 2.2.1 =

**Fixes**

* Migration of database changes introduced in version 2.2.0.

= 2.2.0 =

**New**

* Option to choose whether the estate list should be pre-sorted or sortable by a condition selected by the user in the front-end.

= 2.1.2 =

**Fixes**

* Prevent WordPress from throwing an error in both front-end and back-end if a field has an empty label.

= 2.1.1 =

**Fixes**

* Prevent WordPress from throwing an error on the onOffice >> Modules page if no correct API account data was given.

= 2.1.0 =

**New**

* Splitting of compound fields into their particular components, i.e. "Anrede-Titel" into "Anrede" and "Titel" for search forms

= 2.0.0 =

Stable version of the plugin, including a backend GUI

**Changes**

* All settings available in version 1.0 of the plugin were integrated into the new UI.
* Translations for UI texts were added
* Settings are saved using WordPress options and DB
* New templates with centralized output function per field-type
* SEO: amount of estate detail views was limited to one to avoid duplicate content
* Better title for the estate detail view, depending on what information about the estate is given
* The proximity search can be added to a form
* The plugin has been tested on PHP 7.2

**New**

* Estate lists can be filtered by a filter set up in onOffice.
* Ability to mark fields as filterable for use in the search form of a list
* Type of list for addresses including a search for addresses
* Shortcode [oo_basicdata] to output information about the customer. Can be used on pages and in the text widget.
* View that shows similar estates
* Favorites list for real estates
* Movie-links which were set in onOffice can be displayed as a player or clickable link
* OpenStreetMap map provider
* Double opt-in for newsletter activation for the interested party form

**Removed**

* Custom forms

= 1.0 =
* First version of the plugin without a GUI
* Create estate lists + views + detail views
* Create forms of these kinds: contact form, owner form, interest form, applicant search form or a free form
* Added optional Google Maps overview of all estates for every estate view
* Ability to output estate images has been added
* Added ability to create expose PDFs
* Show information or expose PDFs about sub-estates of a property complex.

== Upgrade Notice ==

= 2.5.4 =
More data in the emails by form submit, jQuery internal reorganization.

= 2.5.3 =
jQuery internal reorganization, notice in the geo fields administration.

= 2.5.2 =
Fix errors in estate detail view configuration.

= 2.5.1 =
Fix issue with geo fields

= 2.4.1 =
Fix for newsletter checkbox (only for applicant form).

= 2.4.0 =
Ability to set preset values per input in forms.

= 2.3.1 =
Fix handling of address list pagination and deactivated fields in estate list.

= 2.3.0 =
Adds default sort direction for the user-defined sorting.

= 2.2.2 =
Various bug fixes.

= 2.2.1 =
Fixes the migration of the database changes introduced in version 2.2.0.

= 2.2.0 =
Estate views can be sorted by the user in the front-end.

= 2.1.2 =
Supports fields with empty labels.

= 2.1.1 =
Fixes the "Modules" page if no valid API credentials are given.

= 2.1.0 =
Support for compound fields (such as "Anrede-Titel") in forms was added in this version.

= 2.0.0 =
This is the recommended version. Version 1 is not supported anymore.

== Arbitrary section ==

= Development =

Development takes place in our [Github repository](https://github.com/onOfficeGmbH/oo-wp-plugin).

= Legal =

onOffice Terms and Conditions: https://en.onoffice.com/terms-and-conditions.xhtml