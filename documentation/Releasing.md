# Releasing

## Merging changes

1. When a PR is ready, squash and merge it into `master`.
2. Add a changelog entry for the next version to `readme.txt`.
    - If this is the first entry for the next version, make a new heading.
    - Otherwise, just add the entry to the section for the new version.

## Development release

1. Go to https://github.com/onOfficeGmbH/oo-wp-plugin/actions/workflows/development-release.yml.
2. To the right, choose "Run workflow" and run it on `master`.
    - The deployment uses secrets from the "WordPress SVN" [environment](#environment) and needs to be approved.
3. If the run fails because the versions are not set correctly, fix them as specified in the error message and start another development release.
4. If the run succeeds, you can download the "Development Version" from https://wordpress.org/plugins/onoffice-for-wp-websites/advanced/. (Or [download it directly](https://downloads.wordpress.org/plugin/onoffice-for-wp-websites.zip).)
5. Note that the translations take about 15 minutes or more before you can edit them at https://translate.wordpress.org/locale/de/default/wp-plugins/onoffice-for-wp-websites/.

## Stable release

1. Ensure you have prepared the release:
    - Test it.
    - Translate it. (This requires a development release so that the translations show up.)
    - Document it.
2. To trigger the release, you create a new tag. This is easiest to do by creating a release on GitHub:
    1. Go to https://github.com/onOfficeGmbH/oo-wp-plugin/releases.
    2. In the top right, click on "Draft new release".
    3. For "Choose a tag" enter the new version and create a new tag.
    4. To the right, click on "Auto-generate release notes".
    5. In the description, remove all project and ticket numbers. These are usually at the beginning of the list items. For example, the entry `*  P#60599 Space for map is taken even when the map is not shown by @tung-le-esg in https://github.com/onOfficeGmbH/oo-wp-plugin/pull/154` should be edited to `* Space for map is taken even when the map is not shown by @tung-le-esg in https://github.com/onOfficeGmbH/oo-wp-plugin/pull/154`.
3. The workflow run will start automatically.
    - The deployment uses secrets from the "WordPress SVN" [environment](#environment) and needs to be approved.

## Environment

To push to the WordPress SVN you need to authenticate as the WordPress user @onofficeweb. We use a GitHub [environment](https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment) to store the credentials as secrets.

The "WordPress SVN" environment is protected so that Actions that use those secrets need to be approved. If you are a [required reviewer](https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment#required-reviewers), you can [approve the workflow run](https://docs.github.com/en/actions/managing-workflow-runs/reviewing-deployments).