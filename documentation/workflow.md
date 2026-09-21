# Git, CI, releases and deployment

Part of the oo-wp-plugin working instructions — index: [CLAUDE.md](../CLAUDE.md).

**This repo's workflows reach real infrastructure** — customer-facing update servers, a hosting API
that builds and restarts test boxes, and the POEditor translation project. Treat everything below
with more caution than a typical WordPress plugin repo.

## Nothing here may be triggered without being asked

Same absolute rule as the four theme repos.

| Action | What it damages if it happens unasked |
| --- | --- |
| `git push` to `release` | **`release.yml` runs semantic-release** → creates the stable GitHub release; `oo-wp-updates-release.yml` then publishes the ZIP to the **customer-facing update server** |
| `git push` to `prerelease` | same pipeline in prerelease mode → GitHub Pre-Release + deploy to the **beta channel** of the hosting API |
| `git push` to `master` | **`poeditor.yml` fires** if `languages/…-de_DE.po` changed → imports strings into POEditor and notifies the translation team on Google Chat |
| `git push` to `beta` | changes what beta testers run |
| `git push --force`, rebase, amend, `git reset --hard` | destroys history the release pipeline and the `-X theirs` merges rely on |
| creating/deleting a branch or tag | `stable-release.yml` triggers on `v*` tags; a stray tag corrupts semantic-release's version line |
| `🧪 Create prerelease` / `🚀 Create release` | merges into `prerelease`/`release` and starts the release pipeline (see above) |
| `🌱 Merge into beta` / `Merge beta into master` | overwrites `beta`'s state, or pushes a merge to `master` |
| `Deploy Test` / `Development Release` | builds and deploys to the hosting API's test/dev channel |
| `PR Preview` dispatch/cleanup | **fans out across all six sibling repos**, builds or deletes test boxes on the dev server |
| `Sync from POEditor` | pulls all locales and **commits straight to `master`** as `oo-actions-bot` |
| any mutating `gh api` call, repository/secret/branch-protection change | changes the project's rules and access |

The same rule applies to the automated review in `.github/workflows/claude-code-review.yml`: **it
reads and comments, it never changes anything.** There this is not left to the prompt — the run is
limited to reading tools plus `gh api -X GET`, and gets the job's own token, which reaches no other
repository. See [code-review.md](code-review.md) for why fork pull requests are refused.

## Branches

```
master ──(beta cut)──► beta ──Create prerelease──► prerelease ──Create release──► release
  ▲                     │        x.y.z-prerelease.N              x.y.z (stable)
  └──── backmerge ──────┘                                                │
  └───────────────── version files synced back ◄──────────────────────────┘
```

| Branch | Purpose |
| --- | --- |
| `master` | all feature work. Default branch. |
| `beta` | frozen state of a beta cycle. **Bugfixes found during a beta test go here, not to `master`.** |
| `prerelease` | target of *🧪 Create prerelease*; semantic-release creates the Pre-Release here |
| `release` | target of *🚀 Create release*; the stable release |

Three rules that cost real time when broken:

- **Never merge `prerelease` or `release` back into `master`** — they carry
  `ci(release): …-prerelease.N` commits that would put a prerelease version into `master`.
- **Backmerge `beta` → `master` after every beta bugfix**, or the fix disappears at the next beta cut
  and the bug reappears.
- **Branch a beta bugfix off `beta`**, not `master`: master already holds features for the next
  cycle that must not reach a running beta.

Branch names encode the ticket: `fix/P#172629-price-on-request`. The `P#<number>` tag is also what
ties a change to its siblings in the other five repos — see [project.md](project.md).

Full checklists and troubleshooting: [RELEASE.md](RELEASE.md).

## Commit messages and PR titles

[Conventional Commits](https://www.conventionalcommits.org/), enforced by **Lint PR**
(`semantic-pull-request.yml`) on the PR title — and on squash-merge the title *becomes* the commit
semantic-release reads.

| Type | Effect |
| --- | --- |
| `feat`, `change` | minor bump (`change` is this project's non-standard addition) |
| `fix`, `perf`, `chore` | patch bump |
| `docs`, `style`, `ci`, `refactor`, `test`, `build`, `revert` | no release |
| `feat!`, `fix!`, … | major bump |

Examples: `fix(P#172629): price on request`, `feat(P#12345): add estate list filter`,
`docs: update release documentation`.

**Never edit a version number or the `readme.txt` changelog by hand** — semantic-release owns
`plugin.php`, `readme.txt` and `package.json`.

## Workflows

| Workflow | Trigger | Note |
| --- | --- | --- |
| `unit-tests.yml` | PR to `master`/`beta`/`prerelease`/`release`, push to `master`/`beta`, weekly | **the CI gate**: phpunit `--fail-on-warning --fail-on-risky --disallow-test-output` + `check-prefixed-imports`. Don't restate its failures in a review. |
| `guard-languages.yml` | PR to `master`/`beta` | fails the PR on any `languages/…` change except `-de_DE.po`. Skipped for branches starting with `release`. |
| `semantic-pull-request.yml` | `pull_request_target` | validates the PR title |
| `claude-code-review.yml` | *Ready for review*, `@claude` comment, manual | **on request, not on every push** — see [code-review.md](code-review.md) |
| `poeditor.yml` | push to `master` touching `-de_DE.po` | uploads to POEditor + Chat notification |
| `sync-from-poeditor.yml` | daily 03:40 UTC, manual, `repository_dispatch` | commits all locales to `master` as `oo-actions-bot` |
| `create-prerelease.yml` / `create-release.yml` | manual | merge into `prerelease`/`release`; *Create release* refuses any source but `prerelease` |
| `release.yml` | push to `release`/`prerelease`, manual | semantic-release, ZIP build, beta deploy, version sync-back |
| `oo-wp-updates-release.yml` | `release: published` | **publishes to the customer update server** |
| `stable-release.yml` | `v*` tag, manual | builds and deploys the stable version |
| `merge-to-beta.yml` / `merge-from-beta.yml` / `merge-source-into-branch.yml` | manual / `workflow_call` | the branch plumbing; merges use `-X theirs` (source wins) |
| `build-release.yml` | `workflow_call` | the shared build job; checks the version in the config files |
| `deploy-test.yml` / `development-release.yml` / `development-zip.yml` | manual / PR review or label | test/dev builds; `development-zip.yml` attaches a ZIP to the PR |
| `pr-preview.yml` / `pr-preview-upload.yml` / `pr-preview-cleanup.yml` | manual / PR closed | the six-repo test-box coordinator, keyed on `P#<number>` |
| `dependabot.yml` | — | single-package PRs; `composer.lock`-only changes need CI, not a code review |

## Release build details

`make release` copies the tree, then runs `composer install --no-dev` **plus**
`scripts/prefix-dependencies.php` (not `vendor/bin/strauss`, because Strauss takes its project
directory from the CWD while being only a dev dependency here). Excluded from the shipped artifact:
`tests/`, `documentation/` (so the working instructions never ship), `scripts/`, `bin/`, dotfiles
and dot-directories, `Readme.md`, `CLAUDE.md`, `Makefile`, `phpstan*`, `phpunit.xml*`.
