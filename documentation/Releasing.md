# Releasing

## Update the changelog
The changelog is in the `readme.txt` under the heading `== Changelog ==`.

You have to follow the format for WordPress to display it correctly. It's easiest to copy a section and then edit it.

In order to know what has changed, you can view the [commits](https://github.com/onOffice-Web-Org/oo-wp-plugin/commits/master) since the last release. Check the corresponding PRs (there should be a link in the commit message) to learn more about what changed.

For general guidance on writing a changelog, see [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## Development release

1. Go to https://github.com/onOfficeGmbH/oo-wp-plugin/actions/workflows/development-release.yml.
2. To the right, choose "Run workflow" and run it on `master`.
    - The deployment uses secrets from the "WordPress SVN" [environment](#environment) and needs to be approved.
3. If the run fails because the versions are not set correctly, fix them as specified in the error message and start another development release.
4. If the run succeeds, you can download the "Development Version" from https://wordpress.org/plugins/onoffice-for-wp-websites/advanced/. (Or [download it directly](https://downloads.wordpress.org/plugin/onoffice-for-wp-websites.zip).)
5. Note that the translations take about 15 minutes or more before you can edit them at https://translate.wordpress.org/locale/de/default/wp-plugins/onoffice-for-wp-websites/.

## Stable release

1. Ensure you have prepared the release:
    - Tested it.
    - Translateed it. (This requires a development release so that the translations show up.)
    - Documented the changes.
    - [Updated the changelog](#update-the-changelog).
2. Update the version number in `readme.txt` and `plugin.php`.
    - To check if e.g. version `3.2` is configured correctly, you can run the [Deno](https://deno.land/) script `deno run --allow-read ./scripts/check-version-in-config-files.ts 3.2`. It will guide you through all the places that need to be updated.
    - Otherwise, the version needs to be set in the following places:
      - In the comment of the `readme.txt` as the `Stable tag`.
      - As the newest [changelog](#update-the-changelog) item in `readme.txt`.
      - In the comment of the `plugin.php` as the `Version`.
3. Commit the new version with e.g. message "Update to version v3.2".
4. To trigger the release, you create a new tag. This is easiest to do by creating a release on GitHub:
    1. Go to https://github.com/onOfficeGmbH/oo-wp-plugin/releases.
    2. In the top right, click on "Draft new release".
    3. For "Choose a tag" enter the new version and create a new tag.
    4. To the right, click on "Auto-generate release notes".
    5. In the description, remove all project and ticket numbers. These are usually at the beginning of the list items. For example, the entry `*  P#60599 Space for map is taken even when the map is not shown by @tung-le-esg in https://github.com/onOfficeGmbH/oo-wp-plugin/pull/154` should be edited to `* Space for map is taken even when the map is not shown by @tung-le-esg in https://github.com/onOfficeGmbH/oo-wp-plugin/pull/154`.
5. The workflow run will start automatically.
    - The deployment uses secrets from the "WordPress SVN" [environment](#environment) and needs to be approved.
6. If something went wrong, you will need to reset the tag. In the following example, the tag is `v3.2`.
    1. In your console, delete the tag with `git tag -d v3.2`.
    2. Remove the tag from GitHub with `git push origin :v3.2`. (Only users with admin access to the repository can do this.)
    3. Re-add a tag by redoing step 3.

## Environment

To push to the WordPress SVN you need to authenticate as the WordPress user @onofficeweb. We use a GitHub [environment](https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment) to store the credentials as secrets.

The "WordPress SVN" environment is protected so that Actions that use those secrets need to be approved. If you are a [required reviewer](https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment#required-reviewers), you can [approve the workflow run](https://docs.github.com/en/actions/managing-workflow-runs/reviewing-deployments).
