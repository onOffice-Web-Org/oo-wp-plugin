# Release Process

Releases are created by semantic-release. It runs automatically whenever something is pushed to the
`prerelease` or `release` branch (`.github/workflows/release.yml`).

## Branches

| Branch | Purpose |
| --- | --- |
| `master` | All feature work lands here. Default branch. |
| `beta` | Frozen state of a beta cycle. Bugfixes found during beta testing go here, not to `master`. |
| `prerelease` | Target of **🧪 Create prerelease**. semantic-release creates the GitHub Pre-Release here. |
| `release` | Target of **🚀 Create release**. semantic-release creates the final release here. |

```
master ──(beta cut)──► beta ──Create prerelease──► prerelease ──Create release──► release
  ▲                     │        x.y.z-prerelease.N              x.y.z (stable)
  └──── backmerge ──────┘                                                │
  └───────────────── version files synced back ◄──────────────────────────┘
```

## Normal release cycle

### 1. Feature work

Merge into `master` via PR. The PR title must follow [Conventional Commits](#commit-messages) — the
**Lint PR** workflow enforces it, and on squash-merge the title becomes the commit message that
semantic-release later reads.

### 2. Beta cut

Merge `master` into `beta`. As long as `beta` carries no commits of its own, this is a fast-forward.
Everything that lands in `master` afterwards belongs to the *next* beta cycle — that is the whole
point of the branch.

### 3. Create the prerelease

Run **🧪 Create prerelease** in GitHub Actions.

- `source-branch` defaults to `beta`. Leave it unless you deliberately want a different source.
- `Beta-Image nach Deploy automatisch bauen?` adds an `Auto-Build: true` git trailer that
  `release.yml` reads and forwards to the beta deploy, so the hosting builds a new beta image.

The workflow merges the source branch into `prerelease` and pushes. That push triggers
`release.yml`, which runs semantic-release, creates a GitHub Pre-Release (`x.y.z-prerelease.N`),
attaches `onoffice-for-wp-websites.zip` and deploys it to the beta channel of the hosting API.

### 4. Test the prerelease

Install the ZIP or use the beta image. If everything is fine, continue with step 6.

### 5. Bugfix during the beta test → see [Shipping a bugfix](#shipping-a-bugfix-during-a-beta-test)

### 6. Create the final release

Run **🚀 Create release**. The source is hard-wired to `prerelease`; the workflow additionally
refuses any other source. semantic-release then creates the stable release on `release`, and the
`onOffice WP-Updates Release` workflow distributes the ZIP to the update server.

## Shipping a bugfix during a beta test

This is the case the `beta` branch exists for. **Do not fix it on `master`** — master already
contains features for the next cycle that must not reach the running beta.

1. Branch off `beta`, not `master`:
   ```bash
   git fetch origin
   git checkout -b fix/P#12345-something origin/beta
   ```
2. Commit the fix and open a PR **with `beta` as the base branch**. Unit tests and the language
   guard run against `beta` PRs.
3. Merge the PR into `beta`.
4. Run **🧪 Create prerelease** again. `beta` is merged into `prerelease` a second time, only the new
   fix comes along, and semantic-release produces the next `x.y.z-prerelease.N`.
5. Merge `beta` back into `master`, otherwise the fix is lost with the next beta cut:
   ```bash
   git checkout master && git pull
   git merge origin/beta
   git push
   ```
   If `master` is protected, do this via a PR instead.
6. Repeat until the beta is clean, then run **🚀 Create release**.

### Hotfix on an already released version

The same route: fix on `beta`, prerelease, verify, release. `beta` at that moment still holds the
released state plus the fix, so no unfinished feature from `master` ships with it.

## What happens automatically

- Version is bumped in `plugin.php`, `readme.txt` (Stable tag) and `package.json`
- On final releases only: the `== Changelog ==` section in `readme.txt` is regenerated from the
  release notes
- Translations are pulled from POEditor before the build
- The plugin ZIP is built and attached to the GitHub Release
- On final releases the version fields are synced back to `master` by the
  `Sync version files to default branch` job
- On prereleases the ZIP is deployed to the beta channel of the hosting API

## Things to watch out for

**Never merge `prerelease` or `release` back into `master`.** Each prerelease run makes
semantic-release commit `ci(release): x.y.z-prerelease.N [skip ci]` to `prerelease` and merge
`release` into it with `-s ours`. Those commits would put a prerelease version number into `master`.
`beta` is the branch that goes back — it stays free of them.

**Backmerge `beta` → `master` after every beta bugfix.** Skipping it means the fix disappears at the
next beta cut, and the same bug reappears in the next cycle.

**The merge uses `-X theirs`.** On conflicts the source branch wins. For the version files this is
harmless: semantic-release computes the next version from the git tags, not from the files, and
bumps them again right after.

**Do not clear the `source-branch` field** in the dispatch form. It is a required input; an empty
value would fall back to `beta` via the workflow, but a typo points the release at a branch that
does not exist and the job fails at checkout.

**The branch you start the action from only selects the workflow definition**, not the source of the
merge. Start both actions from `master`.

## Troubleshooting

| Symptom | Cause / fix |
| --- | --- |
| `Stable releases must be created from prerelease` | **🚀 Create release** was started with another source. Only `prerelease` is allowed. |
| No release was created after the merge | semantic-release found no releasable commits. `docs:`, `style:`, `ci:`, `refactor:`, `test:`, `build:` do not trigger a release. |
| `couldn't find remote ref` in the merge step | The branch in `source-branch` does not exist — check for typos. |
| Version files in `master` look outdated | They are only synced after a **final** release, not after a prerelease. |
| Beta image was not rebuilt | The `Beta-Image nach Deploy automatisch bauen?` checkbox was not set when the prerelease was triggered. |

## Version Bumping Rules

- `feat:` commits → minor version bump (e.g., 6.15.0 → 6.16.0)
- `fix:` and `perf:` commits → patch version bump (e.g., 6.15.0 → 6.15.1)
- Commits with a `!` inside the prefix, e.g. `feat!: ...` → major version bump (e.g., 6.15.0 → 7.0.0)
- `change:` commits → minor version bump
- `chore:` commits → patch version bump
- Commits like `docs:`, `style:`, `ci:`, `refactor:`, `test:`, `build:` don't trigger releases

**Changelog:** Do not edit the `readme.txt` changelog manually. Write clear conventional commit
messages — they become both the GitHub release notes and the WordPress changelog. Prereleases do not
update the `readme.txt` changelog; only final releases do.

## Commit Messages

This project uses [Conventional Commits](https://www.conventionalcommits.org/). This enables
automatic semantic versioning and changelog generation.

PR titles are validated by the **Lint PR** workflow — when squash-merging, the PR title becomes the
commit message and must follow this format.

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: A new feature (triggers minor version bump)
- `change`: Something existing changed (not a new feature, not a bugfix)
- `fix`: A bug fix (triggers patch version bump)
- `perf`: A performance improvement (triggers patch version bump)
- `docs`: Documentation only changes
- `style`: Code style changes (formatting, missing semi colons, etc.)
- `refactor`: Code refactoring without bug fixes or features
- `test`: Adding or updating tests
- `chore`: Maintenance tasks (triggers patch version bump)
- `build`: Build system or dependency changes
- `ci`: CI configuration changes
- `revert`: Reverts a previous commit

**Examples:**
```
feat(P#12345): add estate list filter
change(P#11111): rename admin menu label
fix(forms): resolve GDPR checkbox validation
perf(estates): reduce API calls on list view
docs: update release documentation
```
