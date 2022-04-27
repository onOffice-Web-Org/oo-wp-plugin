# Building

## Set up

1. Run `git clone --recursive https://github.com/onOfficeGmbH/oo-wp-plugin.git` to clone this repository and its submodules.
2. Install [composer](https://getcomposer.org/).
3. Run `composer check-platform-reqs` to ensure you have installed the required PHP extensions.
    - Example: The command tells you that the extensions `ext-mbstring` and `ext-simplexml` are missing. On Ubuntu, you can install these by running `sudo apt install php-mbstring php-simplexml`.
4. Run `composer install` to install the dependencies.

## Make a release .zip

This is how you can generate a .zip to upload to a WordPress instance.

1. Run `PREFIX=/tmp/release/onoffice-for-wp-websites make release`.
    - The `PREFIX` needs to be an absolute path. If you use a relative path, the script might not behave correctly and get into an infinite loop.
2. Run `sed -i "s/Version: .*$/Version: $(git describe --tags)/" /tmp/release/onoffice-for-wp-websites/plugin.php` to overwrite the version so that you can distinguish it from the stable release.
3. Create a zip file that you can upload to WordPress by running:
    1. `cd /tmp/release` (This is needed so that the zip has the correct folder hierarchy.)
    2. `zip -r onoffice-for-wp-websites.zip ./onoffice-for-wp-websites`
4. Upload `/tmp/release/onoffice-for-wp-websites.zip` to WordPress.