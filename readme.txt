=== onOffice direct ===
Contributors: jayay, anniken1
Tags: real estate, onoffice
Requires at least: 4.6
Tested up to: 5.3
Stable tag: 2.0
Requires PHP: 7.1
License: AGPL 3.0
License URI: https://www.gnu.org/licenses/agpl-3.0.html

Allows you to illustrate real estates from onOffice enterprise edition in a WordPress site.

== Description ==

This plugin allows your WordPress page to interact with your onOffice enterprise CRM.
Not only can you create pages to show off your real estates for sale or rent, this plugin allows you
to create different kinds forms whose data is going to be saved in your version of onOffice enterprise.

This plugin does not deal with any kind of manual FTP filetransfer. Rather than that it will tranfer
all the data it needs through the onOffice API.

In detail the plugin can show

* lists of addresses and estates
** estates with a predefined filter such as rent, sale or marked as a reference (or any other filter)
** entities of estate complexes
* A detail view for any published estate
* lists of published addresses, such as your company's team

The supported kinds of forms are

* Contact forms: allows a visitor to contact you
* Owner form: Gives an owner the ability to contact you for the sale of his/her estate
* Prospective buyers can leave information on what kind of estate they are looking for
* Search for prospective buyers

== Installation ==

1. Clone the repository recursively: `git clone --recursive https://github.com/onOfficeGmbH/oo-wp-plugin.git`.
2. Install the development dependencies: `composer install`.
3. Move the plugin directory into a new subdirectory inside the WordPress `plugins` directory (`wp-content/plugins/`)
4. Create a new plugin folder called `onoffice-personalized`.
5. Copy the folder `templates.dist` to `onoffice-personalized/templates`. This is where the newly created individual templates will go.
6. Login into your WordPress page as an administrator and go to the plugins list by navigating to Plugins » Installed Plugins. You should be able to see and activate the onOffice direct plugin. If no API token or secret have been saved so far, a notification will show up at the top. Clicking the link will bring you to the appropriate configuration page.
7. Start editing inside the new onoffice-personalized folder.


**IMPORTANT**: Albeit it is safe to disable the plugin, DELETING IT WILL WIPE ALL PLUGIN-RELATED DATA FROM THE DATABASE. WE DO NOT PROVIDE ANY WARRANTY FOR DATA LOSS!

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 2.0 =
* Stable version of the plugin, including a backend GUI
* New templates with centralized output function per field-type
* Removed "free forms".
* Uses WordPress options and DB to save settings

= 1.0 =
* First version of the plugin without a GUI

== Upgrade Notice ==

= 2.0 =
This is the recommended version. Version 1 is not supported anymore.

== Arbitrary section ==
onOffice Terms and Conditions: https://en.onoffice.com/terms-and-conditions.xhtml