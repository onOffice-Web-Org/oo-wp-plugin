=== onOffice for WP-Websites ===
Contributors: jayay, anniken1
Tags: real estate, onoffice
Requires at least: 4.6
Tested up to: 5.3
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
* **Detailed view**: Comfortably structure the detail view with checkboxes and drag&drop and easily determine which information is displayed.

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
* Two card types: OpenStreetMap or Google Maps
* Show all linked media per address / property


== Installation ==

1. Clone the repository recursively: `git clone --recursive https://github.com/onOfficeGmbH/oo-wp-plugin.git`.
2. Install the dependencies: `composer install --no-dev`.
3. Move the plugin directory into a new subdirectory inside the WordPress `plugins` directory (`wp-content/plugins/`)
4. Create a new plugin folder called `onoffice-personalized`.
5. Copy the folder `templates.dist` to `onoffice-personalized/templates`. This is where the newly created individual templates will go.
6. Login into your WordPress page as an administrator and go to the plugins list by navigating to Plugins » Installed Plugins. You should be able to see and activate the onOffice for WP-Websites plugin. If no API token or secret have been saved so far, a notification will show up at the top. Clicking the link will bring you to the appropriate configuration page.
7. Start editing inside the new onoffice-personalized folder.


**IMPORTANT**: Although it is safe to disable the plugin, DELETING IT WILL WIPE ALL PLUGIN-RELATED DATA FROM THE DATABASE. WE DO NOT PROVIDE ANY WARRANTY FOR DATA LOSS!

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

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

= 2.0 =
This is the recommended version. Version 1 is not supported anymore.

== Arbitrary section ==

= Development =

Development takes place in our [Github repository](https://github.com/onOfficeGmbH/oo-wp-plugin).

= Legal =

onOffice Terms and Conditions: https://en.onoffice.com/terms-and-conditions.xhtml