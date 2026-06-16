const { execFileSync } = require('child_process');
const { readmeHasChangelogEntry, updateReadmeChangelog } = require('./update-readme-changelog.cjs');

async function prepare(pluginConfig, context) {
	const { logger, nextRelease } = context;
	const isPrerelease = nextRelease.version.includes('-');

	if (isPrerelease) {
		logger.log('Skipping readme.txt changelog update for prerelease %s', nextRelease.version);
	} else if (nextRelease.notes) {
		const updated = updateReadmeChangelog({
			version: nextRelease.version,
			notes: nextRelease.notes,
		});

		if (!readmeHasChangelogEntry({ version: nextRelease.version })) {
			throw new Error(`readme.txt changelog entry was not generated for ${nextRelease.version}`);
		}

		logger.log(
			updated
				? 'Updated readme.txt changelog for %s'
				: 'readme.txt changelog already up to date for %s',
			nextRelease.version,
		);
	}

	execFileSync('bash', ['bin/prepare-release.sh', nextRelease.version], {
		stdio: 'inherit',
	});
}

module.exports = {
	prepare,
};
