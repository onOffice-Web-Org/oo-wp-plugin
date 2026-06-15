#!/usr/bin/env bash
set -euo pipefail

VERSION="${1:?Usage: prepare-release.sh <version>}"
PLUGIN_FOLDER="onoffice-for-wp-websites"
RELEASE_ROOT="/tmp/release"
WORKSPACE="${GITHUB_WORKSPACE:-$(pwd)}"

sed -i -E "s/^(Version:[[:space:]]*).*/\1${VERSION}/" plugin.php
sed -i -E "s/^(const ONOFFICE_PLUGIN_VERSION = ')[^']+(';)/\1${VERSION}\2/" plugin.php
sed -i -E "s/^(Stable tag:[[:space:]]*).*/\1${VERSION}/" readme.txt

npm install --package-lock-only
npm run build

rm -rf "${RELEASE_ROOT}"
PREFIX="${RELEASE_ROOT}/${PLUGIN_FOLDER}" make release

(cd "${RELEASE_ROOT}" && zip -r "${WORKSPACE}/release.zip" "${PLUGIN_FOLDER}")
