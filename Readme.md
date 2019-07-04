# onOffice plugin for WordPress

This plugin allows you to illustrate real estates from onOffice enterprise edition in a WordPress site.

In contrast to other real estate WordPress-plugins, this one does not use any file transfer. All data gets transferred using the onOffice API instead.


## Installation (for Developers)

1. Clone this repository recursively: `git clone --recursive https://github.com/onOfficeGmbH/oo-wp-plugin.git`.
2. Install the development dependencies: `composer install`.
3. Move the plugin directory into a new subdirectory inside the WordPress plugins directory (`wp-content/plugins/`)
4. Create a new plugin folder called `onoffice-personalized`.
5. Copy the folder `templates.dist` to `onoffice-personalized/templates`. This is where the newly created individual templates will go.
6. Login into your WordPress page as an administrator and go to the plugins list by navigating to `Plugins` » `Installed Plugins`. You should be able to see and activate the onOffice direct plugin. If no API token or secret have been saved so far, a notification will show up at the top. Clicking the link will bring you to the appropriate configuration page.
7. Start editing inside the new `onoffice-personalized` folder.

** IMPORTANT **: Albeit it is safe to disable the plugin, DELETING IT WILL WIPE ALL PLUGIN-RELATED DATA FROM THE DATABASE. WE DO NOT PROVIDE ANY WARRANTY FOR DATA LOSS!

### Getting API Access

Contact us by phone (+49 241 446860) or email (support@onoffice.com). 

Proceed to the next step once you've got an API token and secret.

### Configuration Basics

#### A First Example: Creating a New Estate List 
In order to create a new estate list, go to "onOffice" » "Estates" and press "Add New". **Give the new list a name**, pick the desired settings and click "Save Changes" at the bottom of the page. Going back to the estate list overview will show you the shortcode (i.e. `[oo_estate view="my new view"]`. Paste this into a new page and open the preview. The new list of estates should be embedded.

An extensive documentation will soon be provided separately.

## License

This project is licensed under both GNU AGPLv3 and GNU GPLv3:
 - the plugin itself is licensed under GNU AGPLv3. See LICENSE-agpl-3.0.txt.
 - config files and default templates are licensed under GNU GPLv3. See LICENSE-gpl.txt.


## Contact

onOffice GmbH
Charlottenburger Allee 5
52068 Aachen
Germany

[support@onoffice.com](mailto://support@onoffice.com)
+49 241 44686
