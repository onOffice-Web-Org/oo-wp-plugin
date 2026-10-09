# onOffice plugin for WordPress

> **Maintenance mode:** this repository only receives bug fixes. It has no GitHub Actions
> workflows anymore, so nothing is built, tested, reviewed or released automatically — run the
> test suite locally with `make test-docker`.

Integrate real estates, contact forms and contact persons from the onOffice Software into your WordPress website.

## Set up for development

In [./documentation/Building.md](./documentation/Building.md) you can find instructions for how to set up your development environment. There are also instructions for making a .zip file that you can upload to WordPress.

Releases used to run automatically over the branches `master` → `beta` → `prerelease` →
`release`. That pipeline was removed together with the workflows; bug fix releases for
wordpress.org are prepared manually. [./documentation/RELEASE.md](./documentation/RELEASE.md)
still describes the former process and the
[Conventional Commits](https://www.conventionalcommits.org/) conventions.

[./CLAUDE.md](./CLAUDE.md) is the index of the working instructions for Claude and other AI agents:
repository structure, architecture, coding conventions, database migrations, testing, and the rules
for the automated pull request review. The detailed documents live in
[./documentation/](./documentation/), together with the long-form documents on building, releasing
and translating. See [Working with Claude](#working-with-claude) below.

## Working with Claude

This repository is set up for [Claude Code](https://docs.claude.com/en/docs/claude-code) for local
use. **Setup, configuration and best practices for all
web repositories are documented centrally on the Google Site:
[Development › Claude Code](https://sites.google.com/onoffice.com/web-intern/development/claude-code).**
This section only covers what is specific to this repository.

| File | Purpose |
| --- | --- |
| [CLAUDE.md](CLAUDE.md) | Working instructions for Claude, loaded automatically at the start of every session — and the index of `documentation/` |
| [documentation/code-review.md](documentation/code-review.md) | The pull request review rules |

**Locally:** run `claude` in the repository root. Claude picks up `CLAUDE.md` on its own and
finishes with this repository's QA (`make test-docker`). It does not commit, push, open pull requests or
trigger workflows without asking first.

**Pull request review:** there is no automated review anymore. Review bug fix pull requests by
hand, or locally with Claude, using the rules in
[documentation/code-review.md](documentation/code-review.md).

**Changing the rules:** `CLAUDE.md` and `documentation/` are edited through a pull request like
any other code — keep `CLAUDE.md` short and put details into `documentation/`. Personal
preferences and permissions belong in `~/.claude/CLAUDE.md` or `.claude/settings.local.json`
(not committed); see the Google Site for what goes where.

## Getting Started

1. Move the plugin directory into a new subdirectory inside the WordPress plugins directory (`wp-content/plugins/`)
2. Create a new plugin folder called `onoffice-personalized` or create a new folder inside your theme called `onoffice-theme`.
3. Copy the folder `templates.dist` to `onoffice-personalized/templates` or `onoffice-theme/templates`. This is where the newly created individual templates will go.
4. Login into your WordPress page as an administrator and go to the plugins list by navigating to `Plugins` » `Installed Plugins`. You should be able to see and activate the onOffice for WP-Websites plugin. If no API token or secret have been saved so far, a notification will show up at the top. Clicking the link will bring you to the appropriate configuration page.
5. Start editing inside the new `onoffice-personalized` or `onoffice-theme` folder.

**IMPORTANT**: Although it is safe to disable the plugin, DELETING IT WILL WIPE ALL PLUGIN-RELATED DATA FROM THE DATABASE. WE DO NOT PROVIDE ANY WARRANTY FOR DATA LOSS!

### Getting API Access

Request your own [onOffice trial version](https://onoffice.com/)

Contact us by phone (+49 241 446860) or email (support@onoffice.com) for questions concerning onOffice enterprise edition.
Please fill out this [form](https://wpplugindoc.onoffice.de/support-request/?lang=en) in regards to questions about our API or plugin development workflow.

Proceed to the next step once you have got an API token and secret.

### Configuration Basics

In comparison to other real estate WordPress-plugins, this one does not use any file transfer via FTP but from the onOffice API.
This also means, you need to enter your API credentials before configuring anything else.

#### A First Example: Creating a New Estate List
In order to create a new estate list, go to "onOffice" » "Estates" and press "Add New". **Give the new list a name**, pick the desired settings and click "Save Changes" at the bottom of the page. Going back to the estate list overview will show you the shortcode (i.e. `[oo_estate view="my new view"]`. Paste this into a new page and open the preview. The new list of estates should be embedded.

An extensive documentation can be found at [wp-plugin.onoffice.com](https://wp-plugin.onoffice.com).

## License

This project is licensed under both GNU AGPLv3 and GNU GPLv3:
 - the plugin itself is licensed under GNU AGPLv3. See LICENSE-agpl-3.0.txt.
 - config files and default templates are licensed under GNU GPLv3. See LICENSE-gpl.txt.

## Contact

onOffice GmbH\
Charlottenburger Allee 5\
52068 Aachen\
Germany

[support@onoffice.com](mailto://support@onoffice.com)\
+49 241 446860
