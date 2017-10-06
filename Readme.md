onOffice plugin for WordPress
=============================

This plugin allows a website owner to illustrate estate data from onOffice enterprise
edition in a Wordpress site.

Data is being transfered from the onOffice API.

Please note this is an early development version and there's neither an installer, nor a
graphical administration interface for this.


Installation
------------

1. move the plugin directory to wordpress plugin directory
2. create a new plugin folder called "onoffice-personalized"
3. copy the folder "templates.dist" to "onoffice-personalized/templates"
4. copy "config.php" to "onoffice-personalized/"

step by step, as shell commands:

```
cp onoffice-wp-plugin <wordpress-dir>/wp-content/plugins/onoffice # onoffice-wp-plugin is this directory
mkdir <wordpress-dir>/wp-content/plugins/onoffice-personalized
cd <wordpress-dir>/wp-content/plugins/
cp -R onoffice/templates.dist/ onoffice-personalized/templates
cp onoffice/config.php onoffice-personalized/
```


Getting API Access
------------------

Contact us: +49 241 44686 0 or support@onoffice.com


Configuration Basics
--------------------

- API token and secret, as well as the cache configuration are located in api-config.php
- the `$config` array is split into these layers:

```
- estate
-- unit
--- filter
--- documents
--- views
---- view
----- data (estate fields to fetch from API)
----- contactdata (address fields to fetch from API)
----- pictures
----- language
----- pageid (representing the view's content)
----- template
----- formname
- forms
-- <form name>
--- inputs
--- formtype
--- language
--- recipient (optional, but recommended)
--- createaddress (optional boolean value whether to create an address)
--- required (array of required fields)
```

Templates are being put into the module directory (currently estate or form) inside the `templates`
directory. The filename is always `<templatename>.php`

License
-------

This project is licensed under both GNU AGPLv3 and GNU GPLv3:
 - the plugin itself is licensed under GNU AGPLv3. See LICENSE-agpl-3.0.txt.
 - config files and default templates are licensed under GNU GPLv3. See LICENSE-gpl.txt.


Contact
-------

onOffice Software AG
Charlottenburger Allee 5
52068 Aachen
Germany

support@onoffice.com