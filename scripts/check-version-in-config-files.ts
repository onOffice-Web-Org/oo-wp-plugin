await check();

/**
 * Ensures that the version passed as an argument is formatted correctly and that it is set correctly in the config files (readme.txt and plugin.php).
 *
 * Run this script from this repo's root (such that './readme.txt' works) with Deno (https://deno.land/), for example:
 * $ deno --allow-read scripts/check-version-in-config-files.ts v1.2.3
 */
async function check() {
  if (Deno.args.length !== 1) {
    console.error(
      `I expected just the version to check as an argument, but I received ${Deno.args.length} arguments.`,
    );
    Deno.exit(1);
  }

  const newVersion = Deno.args[0];
  const isValidVersion = /^\d+.\d+.\d+$/.test(newVersion)
  if (!isValidVersion) {
    console.error(
      `The given version '${newVersion} is not valid. The version has to be in the form 'v1.2.3'.`,
    );
    Deno.exit(1);
  }

  const readme = await Deno.readTextFile("./readme.txt");

  const versionInReadme = readme.match(/Stable tag: (.+)\s*$/m)?.at(1);
  assertValidVersion(
    versionInReadme,
    newVersion,
    `The 'readme.txt' is missing a 'Stable tag'.`,
    `The 'readme.txt' has a 'Stable tag' with the wrong version. It points to ${versionInReadme}, but the new version is ${newVersion}.`,
  );

  const versionInChangelog = readme.match(
    /==\s*Changelog\s*==.*?=\s*(.+?)\s*=/s,
  )
    ?.at(1);
  assertValidVersion(
    versionInChangelog,
    newVersion,
    `Could not find the Changelog in the 'readme.txt'. Make sure there is a line '== Changelog ==' and below it an entry '= ${newVersion} ='.`,
    `The newest changelog entry in the 'readme.txt' has the wrong version. It points to ${versionInChangelog}, but the new version is ${newVersion}.`,
  );

  const pluginPhp = await Deno.readTextFile("./plugin.php");

  const versionInPluginPhp = pluginPhp.match(/Version: (.+)/)?.at(1);
  assertValidVersion(
    versionInPluginPhp,
    newVersion,
    `The 'plugin.php' is missing a 'Version'.`,
    `The 'plugin.php' has a 'Version' with the wrong version. It points to ${versionInPluginPhp}, but the new version is ${newVersion}.`,
  );

  console.log(
    `Success: Version ${newVersion} is set correctly in the 'readme.txt' and the 'plugin.php'.`,
  );
}

function assertValidVersion(
  entry: string | undefined,
  newVersion: string,
  errorWhenMissing: string,
  errorWhenInvalid: string,
) {
  if (entry !== newVersion) {
    if (entry === undefined) {
      console.error(
        errorWhenMissing,
      );
    } else {
      console.error(
        errorWhenInvalid,
      );
    }
    Deno.exit(1);
  }
}
