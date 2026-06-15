const fs = require('fs');

const DEFAULT_README_PATH = 'readme.txt';

const SECTION_MAP = new Map([
	['Features', 'Added'],
	['Bug Fixes', 'Fixed'],
	['Changes', 'Changed'],
	['Performance Improvements', 'Changed'],
	['Maintenance', 'Maintenance'],
	['Documentation', 'Documentation'],
	['Styles', 'Changed'],
	['Code Refactoring', 'Changed'],
	['Tests', 'Tests'],
]);

function escapeRegExp(value) {
	return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function extractReleaseDate(notes) {
	const match = notes.match(/^##\s+.+\((\d{4}-\d{2}-\d{2})\)\s*$/m);
	return match ? match[1] : new Date().toISOString().slice(0, 10);
}

function cleanListItem(item) {
	return item
		.replace(/\s+\(\[[0-9a-f]{7,40}\]\([^)]+\)\)$/i, '')
		.trim();
}

function notesToReadmeEntry(version, notes) {
	const sections = [];
	const sectionsByName = new Map();
	let currentSection = null;

	for (const rawLine of notes.split(/\r?\n/)) {
		const line = rawLine.trim();

		if (!line || line.startsWith('## ')) {
			continue;
		}

		const heading = line.match(/^###\s+(.+)$/);
		if (heading) {
			const sectionName = SECTION_MAP.get(heading[1]) || heading[1];
			currentSection = sectionsByName.get(sectionName);

			if (!currentSection) {
				currentSection = { name: sectionName, items: [] };
				sectionsByName.set(sectionName, currentSection);
				sections.push(currentSection);
			}

			continue;
		}

		const item = line.match(/^[*-]\s+(.+)$/);
		if (item && currentSection) {
			currentSection.items.push(cleanListItem(item[1]));
		}
	}

	const nonEmptySections = sections.filter((section) => section.items.length > 0);
	if (nonEmptySections.length === 0) {
		return '';
	}

	const entry = [`= ${version} (${extractReleaseDate(notes)}) =`];

	for (const section of nonEmptySections) {
		entry.push('', `**${section.name}**`);
		for (const item of section.items) {
			entry.push(`* ${item}`);
		}
	}

	return `${entry.join('\n')}\n`;
}

function updateReadmeChangelog({ readmePath = DEFAULT_README_PATH, version, notes }) {
	if (!version || !notes || version.includes('-')) {
		return false;
	}

	const entry = notesToReadmeEntry(version, notes);
	if (!entry) {
		return false;
	}

	const readme = fs.readFileSync(readmePath, 'utf8');
	const changelogMatch = readme.match(/^== Changelog ==\n+/m);

	if (!changelogMatch || changelogMatch.index === undefined) {
		throw new Error(`Could not find changelog section in ${readmePath}`);
	}

	const sectionStart = changelogMatch.index + changelogMatch[0].length;
	const beforeChangelogEntries = readme.slice(0, sectionStart);
	const changelogAndRest = readme.slice(sectionStart);
	const existingEntryPattern = new RegExp(`^= ${escapeRegExp(version)}(?: \\([^\\n]+\\))? =\\n`, 'm');
	const existingEntry = existingEntryPattern.exec(changelogAndRest);

	let nextReadme;
	if (existingEntry && existingEntry.index !== undefined) {
		const nextEntryPattern = /^= .+ =\n|^== /gm;
		nextEntryPattern.lastIndex = existingEntry.index + existingEntry[0].length;
		const nextEntry = nextEntryPattern.exec(changelogAndRest);
		const existingEntryEnd = nextEntry ? nextEntry.index : changelogAndRest.length;

		nextReadme =
			beforeChangelogEntries +
			changelogAndRest.slice(0, existingEntry.index) +
			`${entry}\n` +
			changelogAndRest.slice(existingEntryEnd).replace(/^\n+/, '');
	} else {
		nextReadme = `${beforeChangelogEntries}${entry}\n${changelogAndRest.replace(/^\n+/, '')}`;
	}

	if (nextReadme === readme) {
		return false;
	}

	fs.writeFileSync(readmePath, nextReadme);
	return true;
}

function readmeHasChangelogEntry({ readmePath = DEFAULT_README_PATH, version }) {
	const readme = fs.readFileSync(readmePath, 'utf8');
	const entryPattern = new RegExp(`^= ${escapeRegExp(version)}(?: \\([^\\n]+\\))? =\\n`, 'm');
	return entryPattern.test(readme);
}

if (require.main === module) {
	const [, , version, notesPath, readmePath = DEFAULT_README_PATH] = process.argv;

	if (!version || !notesPath) {
		console.error('Usage: node bin/update-readme-changelog.cjs <version> <notes-file> [readme-file]');
		process.exit(1);
	}

	const notes = notesPath === '-' ? fs.readFileSync(0, 'utf8') : fs.readFileSync(notesPath, 'utf8');
	updateReadmeChangelog({ readmePath, version, notes });
}

module.exports = {
	notesToReadmeEntry,
	readmeHasChangelogEntry,
	updateReadmeChangelog,
};
