# Building

This is how you can generate a .zip to upload to a WordPress instance.

1. Run `composer install`.
    - If it complains about missing PHP extensions, you need to install them. For example, the PHP extension `simplexml` can be installed by running `sudo apt install php-simplexml`.
2. Run `PREFIX=/tmp/release make test-zip`.
    - The `PREFIX` needs to be an absolute path. If you use a relative path, the script might not behave correctly and get into an infinite loop.
    - After the script has run, you can upload `/tmp/release/onoffice-<version>.zip` to your WordPress.