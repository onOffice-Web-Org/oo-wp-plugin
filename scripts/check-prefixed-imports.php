<?php

/**
 * Fails if any source file references a prefixed dependency under its original name.
 *
 * Such a reference either errors at runtime or - worse - silently binds to the copy bundled by
 * another plugin, which is the exact bug class the prefixing removes. The most likely ways for one
 * to appear are code copied from upstream documentation and a re-sync of SDK/ from onOfficeGmbH/sdk.
 *
 * The namespaces to look for are derived from vendor-prefixed/, so adding a dependency needs no
 * change here. Run "composer install" before this check.
 */

const SCAN_DIRECTORIES = ['plugin', 'config', 'SDK', 'tests', 'templates.dist'];
const SCAN_FILES = ['plugin.php', 'oo-updater.php'];

$root = dirname(__DIR__);
$composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
$namespacePrefix = $composer['extra']['strauss']['namespace_prefix'] ?? '';
$classmapPrefix = $composer['extra']['strauss']['classmap_prefix'] ?? '';

if (!is_dir($root . '/vendor-prefixed')) {
	fwrite(STDERR, 'vendor-prefixed/ is missing - run "composer install" first.' . PHP_EOL);
	exit(1);
}

/** @return array{0: string[], 1: string[]} namespace roots and global class names, unprefixed */
$collectPrefixedNames = static function (string $directory, string $namespacePrefix, string $classmapPrefix): array {
	$namespaces = [];
	$classes = [];
	$pFiles = new RegexIterator(
		new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)),
		'/\.php$/'
	);

	foreach ($pFiles as $pFile) {
		$contents = (string) file_get_contents($pFile->getPathname());

		if (preg_match('/^namespace\s+' . preg_quote($namespacePrefix, '/') . '([A-Za-z0-9_]+)/m', $contents, $m)) {
			$namespaces[$m[1]] = true;
		}
		if (preg_match('/^\s*(?:final\s+|abstract\s+)*(?:class|interface|trait)\s+'
			. preg_quote($classmapPrefix, '/') . '([A-Za-z0-9_]+)/m', $contents, $m)) {
			$classes[$m[1]] = true;
		}
	}

	return [array_keys($namespaces), array_keys($classes)];
};

[$namespaceRoots, $globalClasses] = $collectPrefixedNames($root . '/vendor-prefixed', $namespacePrefix, $classmapPrefix);

if ($namespaceRoots === []) {
	fwrite(STDERR, 'Found no prefixed namespaces in vendor-prefixed/ - is the prefixing broken?' . PHP_EOL);
	exit(1);
}

$sources = SCAN_FILES;

foreach (SCAN_DIRECTORIES as $directory) {
	if (!is_dir($root . '/' . $directory)) {
		continue;
	}
	foreach (new RegexIterator(
		new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory)),
		'/\.php$/'
	) as $pFile) {
		$sources[] = substr($pFile->getPathname(), strlen($root) + 1);
	}
}

$findings = [];

foreach ($sources as $source) {
	$path = $root . '/' . $source;

	if (!is_file($path)) {
		continue;
	}

	foreach (explode("\n", (string) file_get_contents($path)) as $number => $line) {
		// Blank out correctly prefixed references so only the bare originals can still match.
		$haystack = str_replace([$namespacePrefix, $classmapPrefix], "\0", $line);

		foreach ($namespaceRoots as $namespaceRoot) {
			if (preg_match('/(?<![\w\\\\\0])\\\\?' . preg_quote($namespaceRoot, '/') . '\\\\/', $haystack)) {
				$findings[] = $source . ':' . ($number + 1) . ' -> ' . $namespaceRoot . '\\' . ' (' . trim($line) . ')';
			}
		}
		foreach ($globalClasses as $globalClass) {
			if (preg_match('/(?:^\s*use\s+|\\\\)' . preg_quote($globalClass, '/') . '\b/', $haystack)) {
				$findings[] = $source . ':' . ($number + 1) . ' -> ' . $globalClass . ' (' . trim($line) . ')';
			}
		}
	}
}

if ($findings !== []) {
	fwrite(STDERR, 'Unprefixed references to prefixed dependencies found. Use the ' . $namespacePrefix
		. ' names instead, otherwise these bind to whatever copy another plugin loaded first:' . PHP_EOL);
	foreach ($findings as $finding) {
		fwrite(STDERR, '  ' . $finding . PHP_EOL);
	}
	exit(1);
}

printf("No unprefixed references found (%d namespaces, %d global classes checked).\n",
	count($namespaceRoots), count($globalClasses));
exit(0);
