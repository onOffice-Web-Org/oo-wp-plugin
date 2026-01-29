=== onOffice for WP-Websites ===
Contributors: jayay, anniken1
Tags: real estate, onoffice
Requires at least: 6.1
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 6.10.2
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

= 6.10.2 (2026-01-29) =

**Fixed**
* language translation bug

= 6.10.1 (2026-01-27) =

**Added**
* oo-updater

= 6.10 (2026-01-26) =

**Fixed**
* WordPress Plugin Directory requirements

**Changed**
* Commission display in the total price calculator

= 6.9.1 (2026-01-16) =

**Fixed**
* Form submission issues
* API error handling
* Property search language handling
* Large property list pagination

= 6.9 (2025-12-19) =

**Fixed**
* Security improvements
* Translation of geo fields
* Fatal error on the address detail page

**Added**
* Google reCAPTCHA Enterprise support

**Changed**
* Translation workflow

= 6.8 (2025-11-27) =

**Fixed**
* General Security issues
* Security issue related to query handling

**Changed**
* Select inputs now display all available options

= 6.7 (2025-10-30) =

**Fixed**

* Ensure BFSG-compliant accessibility
* Transfer of apostrophes to Enterprise
* Display of the property detail page on mobile devices
* Error in the applicant contact form
* Configuration errors in forms
* Raw values for an address list
* Display of all energy certificate fields

= 6.6 (2025-09-30) =

**Fixed**

* Critical error on detail page settings.
* Critical error in the Total Price Calculator

= 6.5.2 (2025-09-15) =

**Added**

* Support for custom min/max values in slider fields for applicant forms

**Fixed**

* Property list sorting functionality

= 6.5.1 (2025-09-11) =

**Fixed**

* Title and description tags not loading correctly on multilingual sites when using Yoast tags on both language versions
* Critical error (UnknownFieldException) when using currency field on property detail pages
* Browser share functionality on mobile devices (iPhone and Android)
* Duplicate HTML IDs in backend for "Select All" checkboxes

= 6.5 (2025-08-28) =

**Added**

* Key Facts feature for property lists and detail pages - highlight important fields like price, plot size, or number of rooms to draw visitors attention
* Multi-field management - select and remove multiple fields at once in field lists for improved efficiency
* Custom naming for individual pages in multi-page leadgenerator forms for better navigation
* Info text for total price calculator indicating availability only for Germany due to country-specific requirements
* Updated accessible-slick slider for improved accessibility including keyboard navigation and ARIA compatibility

**Changed**

* Energy certificate scale values adjusted with unified scale for consumption and demand certificates in residential buildings
* Improved database migration process for better performance
* Enhanced test coverage and workflow automation
* Updated Building.md documentation for development setup
* Code refactoring for better maintainability, quality and performance across multiple components
* Improved leadgenerator form with optional API dependencies

**Fixed**

* Forms showing error message despite successful submission
* Sorting of properties with "price on request" - these are now correctly placed at the end of the list
* Critical error when "Contact Category Origin" field is deactivated in onOffice enterprise
* Property status sorting now correctly reflects the configured order in backend settings
* Added missing property status values to sorting logic

= 6.4 (2025-07-24) =

**Added**

* Ability to reorder entire pages via drag & drop in multi-page forms, allowing for easier reorganization of content

= 6.3.3 (2025-07-17) =

**Added**

* Language support for Dutch

**Changed**

* 'Commission-free', internal commission, and external commission are now displayed in relation to each other

**Fixed**

* SQL Injection Vulnerabilities

= 6.3.2 (2025-07-02) =

**Fixed**

* Fixed submission for honeypot forms
* Allow nullable default language for Plugin Cache Function
* Fixed missing Status Properties for estate list and detail page
* Fixed sendContactForm for AddressDetail Page

= 6.3.1 (2025-07-01) =

**Fixed**

* reCAPTCHA for forms (missing minified JS)

= 6.3 (2025-06-27) =

**Fixed**

* reCAPTCHA for forms

= 6.2 (2025-06-16) = 

**Added**

* Prepared media data for properties to display media information in the property detail page 

**Fixed**

* Load contact person on detail page if its broker and not public
* Fixed the search parameter for location

= 6.1 (2025-05-28) = 

**Added**

* Info window on maps to property details
* Language support for Italian
* Language support for French

**Changed**

* Improve HTML structure of unit lists
* Improve the Plugins consistency - Ensuring database cleanup on Plugin uninstallation 
* Permanently Marked Required Fields and Correct Local Area Search in Applicant Forms

**Fixed**

* Bugs for new caching system
* Persistence of the property list with missing or same name

**Removed**

* "Dreizeiler" field from datatable on estates view

= 6.0 (2025-04-23) =

**Changed**

* Redesigned and significantly more performant caching system.

= 5.8 (2025-04-29) =

**Changed**

* Styling Unit lists on the property detail page
* Title image, if marked as such, is always displayed as the first image of the image slider

**Fixed**

* Total price calculator can also be displayed when the percentage sign is not entered in the external commission in onOffice enterprise

= 5.7 (2025-03-27) =

**Changed**

* Restriction of the form selection on the property detail page
* Improved display of parking options (multiParkingLot) for properties

**Fixed**

* Missing fields of address
* Missing austrian locale
* Translation on property detail

= 5.6 (2025-02-27) =

**Added**

* Total price calculator chart in property details
* ImmoNr (objektnr_extern) variable to subject line in forms

**Changed**

* Display link fields as actual links
* Naming of estateID to Datensatznr variable in subject line in forms

**Fixed**

* Special character encoding in parameters
* Loading regions without id
* Styling in backend settings

= 5.5 (2025-01-31) =

**Added**

* Customization of standard email subjects for forms

**Fixed**

* Order in energy certificate scale

* Deprecated notices on PHP 8.3

* Error causes by wrong type

**Removed**

* Pagination from detail page template

= 5.4.1 (2024-12-20) =

**Added**

* Info text when an address detail page is not found

* Option to display ownerleadgeneratorform embedded on the website

* Extension to order fields in multipage forms like leadgenerator

**Fixed**

* Fatal error when creating applicant search form

* Error when regionaler_zusatz is deactivated

* Incorrect email adresses containing whitespaces

* Box "Layout & Design" position in backend

= 5.3 (2024-12-05) =

**Added**

* Graphical implementation of energy certificate

* Filtering in similar properties

* Option to display all or only the main contact person on property detail pages

* Map view with address locations to address list

* Linking to a contact from the property detail page

**Changed**

* Error handling in similar properties

* Order of "Activities" and "Tasks" boxes in form settings

* Improved display of image type selection in property list settings

**Fixed**

* Using forms with reCaptcha in footer section

* Empty value for "Radius" in similar properties

* Wrong redirects caused by umlauts and special characters in URL for multilingual websites

= 5.2.1 (2024-11-05) =

**Fixed**

* Language switch for WPML

= 5.2 (2024-10-30) =

**Added**

* Forms can trigger writing of tasks in onOffice enterprise

* Forms can trigger writing of activities in onOffice enterprise

* SEO friendly URL for address detail page

* Image with better resolution for addresses (bildWebseite)

**Changed**

* Default template for address details

**Fixed**

* Maps on pages with multiple property lists

* Displaying price on request for calculated prices

* Social Meta Data in Settings

= 5.1.4 (2024-10-09) =

**Added**

* Customizable labels for addresses
* Count of all addresses
* Count of properties by address

= 5.1.3 (2024-09-30) =

**Added**

* Showing cities as filterable selection field in address search

**Fixed**

* Parking lot price
* Meta key

= 5.1.2 (2024-09-24) =

**Added**

* Counter for properties in address lists

**Changed**

* Default template of address detail

= 5.1.1 (2024-09-19) =

**Fixed**

* Displaying reference property detail pages

= 5.1 (2024-09-09) =

**Added**

* Order properties by tags

**Changed**

* Multiple option to set contact types in forms

**Fixed**

* Showing similar properties with price on request

= 5.0 (2024-08-07) =

**Added**

* Hidden fields in forms -! To use this feature properly: personalized form templates and fields.php have to be updated !-
* Indication in form overview whether reCAPTCHA has been activated or not
* Thousand separator in form inputs -! To use this properly: personalized templates have to be updated !-
* Automatic integration of the energy certificate fields for a newly created detail page
* Setting for caching duration

**Changed**

* Order of Geo-Position field in property lists, applicant search form and interest form
* Update leaflet.js version to 1.9.4
* Update onOffice logo

**Fixed**

* Unnecessary redirects for multilingual pages with user-friendly URLs
* Redirection if url contains parameters
* Loading third party JS liberaries on property detail page of an unit
* Security issue
* HTML structure of property list shortcode

**Removed**

* PDF document download for property lists

= 4.20 (2024-06-26) =

**Added**

* OpenGraph support
* Adaptive image resolution in property list, detail view of property and similar estates
* Notification of unsaved changes

**Changed**

* Filter option in property list overview and form overview
* Some text changes

**Fixed**

* Sorting of multiple property lists on same page
* Pagination of multiple property lists on same page
* Loading forms with activated reCAPTCHA in Elementor

**Removed**

* Unused JS library: chosen

= 4.19 (2024-04-18) =

**Added**

* Showing cities as filterable selection field in search
* Default section for AreaButler in detail page template
* Custom styling classes for empty detail page

**Changed**

* Automatic overwriting of address duplicates has been removed, an e-mail notification for manual management of duplicates will be sent
* Google reCAPTCHA secret and key can be displayed and emptied

**Fixed**

* Error with ScriptLoader
* Showing price on request
* Empty price field while searching
* Missing plugin menu item while editing pages
* Hiding columns in the overview of real estate lists and forms

= 4.18.1 (2024-02-28) =

**Fixed**

* Styling of slick slider

= 4.18 (2024-02-28) =

**Added**

* Search for fields in all property lists, detailed views and forms in admin settings

**Changed**

* Improve performance while using editor of ACF plugin
* Improve position of save button in all property lists, detailed views and forms in admin settings
* Mandatory fields for geo range search

**Fixed**

* Dequeing of scripts and styles by onOffices Plugin
* Custom labeling for Land field

= 4.17 (2024-02-07) =

**Changed**

* Fields of type date are displayed with datepicker
* Fields of type boolean are displayed with radio buttons
* Fields of type user are displayed as selection

**Fixed**

* Readable additional geographical information in e-mails
* Filtering of properties with "price on request"
* Sending forms with multiple selections

= 4.16 (2023-12-13) =

**Changed**

* Improvment for timum hooks and redirections
* Improvment for XML Http Request
* Styling of admin backend

**Fixed**

* Naming conflicts with ACF
* Cache clearing for multisites
* Renaming of labels on detail page
* Enabling of send button in forms with recaptcha

= 4.15.1 (2023-11-13) =

**Changed**

* reCAPTCHA is only loaded on form pages

**Fixed**

* Bugs that prevented forms from being sent

= 4.15 (2023-10-30) =

**Changed**

* Some changes have been made to improve performance: only loading of required scripts, compressing of scrips, improvment of sending queries 

= 4.14 (2023-09-11) =

**Added**

* Property lists in the plugin backend are now sortable by name, template and type.

**Changed**

* Title tags and description tags that are too long will be shortened.
* New address records of interested parties of a property are created with the same supervisor as the property.

**Fixed**

* The sending of e-mails when the send button has been pressed for several times.
* Bug with invalid estate Ids.
* Bug with onoffice-style.css when using a child theme.

= 4.13 (2023-08-15) =

**Added**

* All types of images for similar properties.

**Changed**

* Hide coordinates of property if "Geo range search" is on.

**Fixed**

* Display of "Price on request" in custom templates.
* Displaying the fax number in the contact details.
* Sending property data by email using the owner form.
* Text of the agreed privacy policy in emails.

= 4.12.1 (2023-08-03) =

**Fixed**

* Error if "Preis auf Anfrage" is deactivated in onOffice enterprise

= 4.12 (2023-08-02) =

**Added**

* An option to toggle visibility of the map for each estate list.
* 'Price on request' option to hide prices in lists and detail view.

**Changed**

* Renaming a notification if name of estate list, form, etc. is empty while saving.
* Renaming the options to add fields to the search and filter.

**Fixed**

* The honeypot javascript is always being loaded even when disabled.
* Customizing labels of message field in forms doesn't work.

**Removed**

* Faulty options for the admistration view.

= 4.11.1 (2023-05-24) =

**Fixed**

* Performance error while loading estate list

= 4.11 (2023-05-15) =

**Added**

* An info message appears when clearing cache.

**Changed**

* Link to documentation opens in a new tab.

**Fixed**

* Error in forms with message field filled with default value.
* Error in forms with range fields marked as required.
* Missing initial height of google map.

= 4.10.1 (2023-04-17) =

**Fixed**

* Critical errors if fields are deactivated in enterprise

= 4.10 (2023-04-11) =

**Changed**

* Honeypot is disabled by default now to avoid errors with custom stylesheets. It now has to be enabled manually.
* Order of serveral fields in forms.

**Fixed**

* Error if a field in enterprise is missing for an estate.
* Error while loading Honeypot JS.
* Number of search results if references are hidden.
* Initial saving of GDPR checkbox and custom labels.

**Removed**

* A deprecated option for forms.

= 4.9 (2023-02-23) =

**Added**

* Option to add a honeypot field to forms to combat spam.

**Changed**

* Introduction page was made translatable and simplified.

**Fixed**

* Detect detail view shortcode in ACF blocks' data.

= 4.8 (2023-02-09) =

**Changed**

* The onOffice menu in the admin bar now appears in the backend, too.
* In an estate list or the detail page, when a field contains only an empty array (for example the parking lots field), it will no longer be displayed with a blank value, but removed from the list. If you have personalized your templates, you need to add this check for an empty array to get the same behavior.

**Fixed**

* Parking lots previously would display as "Array, Array, Array". They are now shown correctly, in a format similar to the one in onOffice enterprise.
* When saving a custom label for an estate or form field that contained quotes, like ", they would be escaped with \". The plugin now preserves the original characters when saving.
* When a template from the theme folder was selected and then a child theme was activated, the frontend showed an error about the template path being invalid. This was fixed so that it loads the template correctly.
* Custom labels for estate list fields are now used in the frontend in the dropdown where the sorting of the list can be selected.

= 4.7.1 (2023-01-23) =

**Fixed**

* If an estate showed similar estates on its detail page, there was a bug that would cause a fatal error. We fixed it so that similar estates can be shown normally again.
* When an estate field was renamed, the new name would previously not be used in the search form. We fixed it and now a field's custom label is used everywhere.
* When no estates were published, the detail page preview without an estate ID would crash. We fixed it and now refer to the documentation on how to publish estates.

= 4.7 (2023-01-11) =

**Added**

* Previously, only form fields could have custom labels. Now you can give fields from estate lists and the detail view custom labels, too.

**Changed**

* When the API causes an error, previously only the error code was given. Now we output the error message, too.

**Fixed**

* Fields that were added to a field list but then deactivated in onOffice enterprise could not be removed due to a bug. This was fixed, so that now deactivated fields can be removed from the field list.
* In forms, the plugin adds a special field called "Newsletter". Previously when sending a form that contained this field, it would fail. We now have fixed the bug, so that forms with this field can be sent as expected.

= 4.6 (2022-12-19) =

**Added**

* Allow Markdown in labels for form fields. This allows for example privacy policy checkboxes to link to a privacy policy.
* Add a special form field that displays the GDPR consent as a checkbox instead of a dropdown.

**Changed**

* When previewing the detail page without an estate's ID in the URL, we explain more clearly why no estate can be shown. If the user is logged in, we link to a random estate so that they can easily preview the detail page.

= 4.5 (2022-11-16) =

**Added**

* The title and description of detail pages are now set with the estate's data. You can deactivate this to use an SEO plugin and utilize custom fields instead. On upgrade, if we detect a popular SEO plugin, our plugin will automatically leave the title and description alone.
* In the form overview, you can now add a new form without first needing to filter for a specific form type.

**Changed**

* Adding and removing fields from field lists has been made easier.

**Fixed**

* In version 4.0 we introduced a new CSS file that was tied to your templates. With this changed, we renamed the style handle, which was not backwards compatible. Now, the new CSS file is enqueued with the new style handle, but the legacy CSS file is enqueued with the legacy style handle. This restores backwards compatibility.
* When the onOffice API returned an unexpected error, the plugin could cause a fatal error which made the backend unusable. We now handle these API errors so that you can keep using the backend even when the API breaks.

= 4.4.1 (2022-10-21) =

**Fixed**

* When using PHP 7 with a version of WordPress older than 5.9.0, the plugin would cause a critical error because we used a function that is only available with PHP 8 or WordPress 5.9.0 or newer. We now replaced the function with one that works on older versions as well.

= 4.4 (2022-10-19) =

**Added**

* Editors can now modify and save the plugin settings.
* When viewing the website, a menu has been added to the admin bar that makes it easy to reset the plugin's cache or go directly to addresses, estate lists, forms, or the settings.

**Changed**

* The default settings for lists and forms have been updated to better match common configurations.

**Fixed**

* When visitors were using the wrong URL for the detail page and had to be redirect, the trailing slash was not always consistent which could lead to an extra redirect. Now, it redirects directly to the correct URL.
* The limit of 500 estates per page is now communicated in the estate list settings.
* In the estate search, the button shows a preview of how many estates match the current selection. Previously, reference estates were not counted. This is now fixed and the preview matches the number of results after pressing the button.
* The label for the PDF exposé was renamed to better communicate that it is a direct download.

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

Note that we cannot guarantee compatibility with all other plugins and themes.

While it works well with most, if you encounter issues that we cannot reproduce, feel free to open a PR in our repository. If we understand the change and can test the functionality, we will be happy to include your fix.

= Development =

Development takes place in our [Github repository](https://github.com/onOfficeGmbH/oo-wp-plugin).

= Legal =

onOffice Terms and Conditions: https://en.onoffice.com/terms-and-conditions.xhtml
