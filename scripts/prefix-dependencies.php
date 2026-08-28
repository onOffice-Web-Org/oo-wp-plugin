<?php

/**
 * Prefixes the production dependencies into vendor-prefixed/ using Strauss.
 *
 * Usage: php scripts/prefix-dependencies.php [projectDirectory]
 *
 * The project directory defaults to the repository root (composer post-install/post-update). The
 * release build passes its staging directory, because Strauss always works on the current working
 * directory while its own code is only installed here as a dev dependency.
 *
 * The alias file Strauss writes afterwards is deleted on purpose: it re-registers the unprefixed
 * names (DI\create(), Parsedown, Psr\Container\*) in the global namespace and would bring back
 * exactly the plugin conflicts the prefixing prevents. Every call site in this repository uses the
 * prefixed names, so no aliases are needed.
 */

use BrianHenryIE\Strauss\Console\Application;
use Composer\InstalledVersions;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$root = dirname(__DIR__);
$projectDirectory = realpath($argv[1] ?? $root);

if ($projectDirectory === false) {
	fwrite(STDERR, 'Project directory not found: ' . ($argv[1] ?? $root) . PHP_EOL);
	exit(1);
}

if (!is_dir($root . '/vendor/brianhenryie/strauss')) {
	fwrite(STDERR, 'Strauss is not installed (composer install --no-dev?) - skipping dependency prefixing.' . PHP_EOL);
	exit(0);
}

$composer = json_decode((string) file_get_contents($projectDirectory . '/composer.json'), true);
$lock = json_decode((string) file_get_contents($projectDirectory . '/composer.lock'), true);
$strauss = $composer['extra']['strauss'] ?? [];

// A production dependency in neither list would ship unprefixed and could collide with another
// plugin - fail loudly instead of letting that slip through a dependency update.
$known = array_merge($strauss['packages'] ?? [], $strauss['exclude_from_copy']['packages'] ?? []);
$unlisted = array_diff(array_column($lock['packages'] ?? [], 'name'), $known);

if ($unlisted !== []) {
	fwrite(STDERR, 'These production dependencies are listed in neither extra.strauss.packages nor'
		. ' extra.strauss.exclude_from_copy.packages: ' . implode(', ', $unlisted) . PHP_EOL
		. 'Add PHP packages to the first list so they get prefixed, asset-only packages to the second.' . PHP_EOL);
	exit(1);
}

// Strauss reads the packages out of vendor/ and deletes them afterwards, so a second run without a
// fresh composer install finds nothing and would replace vendor-prefixed/ with an empty autoloader.
$pending = array_filter(
	$strauss['packages'] ?? [],
	static fn(string $package): bool => is_dir($projectDirectory . '/vendor/' . $package)
);

if ($pending === [] && is_dir($projectDirectory . '/vendor-prefixed')) {
	fwrite(STDERR, 'Dependencies are already prefixed - run "composer install" to rebuild them.' . PHP_EOL);
	exit(0);
}

require $root . '/vendor/autoload.php';

chdir($projectDirectory);

$pApplication = new Application((string) InstalledVersions::getPrettyVersion('brianhenryie/strauss'));
$pApplication->setAutoExit(false);
$exitCode = $pApplication->run(new ArrayInput([]), new ConsoleOutput());

if ($exitCode === 0) {
	@unlink($projectDirectory . '/vendor/composer/autoload_aliases.php');
}

exit($exitCode);
