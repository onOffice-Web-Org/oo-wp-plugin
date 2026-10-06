#!/usr/bin/env bash
#
# Fails if a change leaves a translatable string in the code without a German translation:
# either a new string was added without a German entry, or an existing German entry was deleted,
# emptied or replaced by its English source text while the code still uses it. Strings that already lacked a German translation on
# the base branch are only reported as a warning.
#
# Usage: check-translations.sh <head-checkout> <base-checkout>
# Requires WP-CLI and GNU gettext. Neither checkout is modified.

set -euo pipefail
export LC_ALL=C

PO_FILE="languages/onoffice-for-wp-websites-de_DE.po"

HEAD_DIR="$(cd "$1" && pwd)"
BASE_DIR="$(cd "$2" && pwd)"
WORK_DIR="$(mktemp -d)"
trap 'rm -rf "${WORK_DIR:?}"' EXIT

# Writes all strings used in the code of checkout $1 into the POT file $2 (same as `npm run i18n`)
extract_strings() {
    wp i18n make-pot "$1" "$2" --no-location --skip-audit \
        --exclude="onoffice-for-wp-websites,node_modules,vendor,tests" >/dev/null
}

# Prints one line per message of the PO/POT file $1: the msgid, prefixed with "[msgctxt] " if set.
# Further arguments are passed to msgattrib, e.g. --translated.
po_keys() {
    po_messages 0 "$@"
}

# Like po_keys, but only messages whose msgstr (msgstr[0] for plurals) equals the msgid
po_identical() {
    po_messages 1 "$@"
}

po_messages() {
    local identical="$1" file="$2"
    shift 2
    msguniq --use-first "$file" | msgattrib --no-obsolete --no-wrap "$@" | awk -v identical="$identical" '
        function unquote(s) { return substr(s, 2, length(s) - 2) }
        function flush() {
            if ((id != "" || ctxt != "") && (!identical || str == id)) print (ctxt != "" ? "[" ctxt "] " : "") id
            id = ""; ctxt = ""; str = ""; state = ""
        }
        /^msgctxt "/      { ctxt = unquote(substr($0, 9)); state = "ctxt"; next }
        /^msgid "/        { id = unquote(substr($0, 7)); state = "id"; next }
        /^msgstr "/       { str = unquote(substr($0, 8)); state = "str"; next }
        /^msgstr\[0\] "/ { str = unquote(substr($0, 11)); state = "str"; next }
        /^"/              { if (state == "ctxt") ctxt = ctxt unquote($0); else if (state == "id") id = id unquote($0); else if (state == "str") str = str unquote($0); next }
        /^$/              { flush(); next }
                          { if (state != "") state = "other" }
        END               { flush() }
    ' | sort -u
}

# Collects the used, existing and translated keys of checkout $2 into $WORK_DIR/$1
collect() {
    local out="$WORK_DIR/$1" dir="$2"
    mkdir -p "$out"
    extract_strings "$dir" "$out/code.pot"
    po_keys "$out/code.pot" > "$out/used"
    po_keys "$dir/$PO_FILE" > "$out/entries"
    po_keys "$dir/$PO_FILE" --translated > "$out/translated"
    po_identical "$dir/$PO_FILE" --translated > "$out/identical"
    comm -23 "$out/used" "$out/translated" > "$out/missing"
}

# Prints the lines of file $2 as GitHub annotations of type $1 (error, warning, notice)
annotate() {
    sed -e 's/%/%25/g' -e "s/^/::$1 title=$3::/" "$2"
}

summary() {
    if [ -n "${GITHUB_STEP_SUMMARY:-}" ]; then
        printf '%s\n' "$@" >> "$GITHUB_STEP_SUMMARY"
    fi
}

summary_list() {
    summary "### $1" "" '```' "$(cat "$2")" '```' ""
}

echo "Scanning head..."
collect head "$HEAD_DIR"
echo "Scanning base..."
collect base "$BASE_DIR"

H="$WORK_DIR/head"
B="$WORK_DIR/base"
comm -23 "$H/missing" "$B/missing" > "$WORK_DIR/new-missing"
comm -12 "$H/missing" "$B/missing" > "$WORK_DIR/known-missing"
# New gaps that had a German translation on the base branch were deleted or emptied by this change
comm -12 "$WORK_DIR/new-missing" "$B/translated" > "$WORK_DIR/removed"
comm -23 "$WORK_DIR/new-missing" "$B/translated" > "$WORK_DIR/added"
# Used strings whose German text on the base branch was replaced by the English source text
comm -23 "$B/translated" "$B/identical" | comm -12 - "$H/identical" | comm -12 - "$H/used" > "$WORK_DIR/reverted"
# Entries deleted from the PO file whose string is no longer used anywhere
comm -23 "$B/entries" "$H/entries" | comm -23 - "$H/used" > "$WORK_DIR/obsolete"

echo "Strings in code: $(wc -l < "$H/used"), German entries: $(wc -l < "$H/entries")"

if [ -s "$WORK_DIR/known-missing" ]; then
    echo "::warning title=Translations::$(wc -l < "$WORK_DIR/known-missing") strings already had no German translation on the base branch (not caused by this change)."
    echo "::group::Strings without German translation on the base branch"
    cat "$WORK_DIR/known-missing"
    echo "::endgroup::"
fi

if [ -s "$WORK_DIR/obsolete" ]; then
    echo "German entries removed because the string is no longer used in the code:"
    sed 's/^/  /' "$WORK_DIR/obsolete"
fi

if [ ! -s "$WORK_DIR/removed" ] && [ ! -s "$WORK_DIR/reverted" ] && [ ! -s "$WORK_DIR/added" ]; then
    echo "Every string added or changed by this change has a German translation, ok."
    exit 0
fi

if [ -s "$WORK_DIR/removed" ]; then
    annotate error "$WORK_DIR/removed" "German translation removed but still used"
    summary_list "German translation removed but still used in the code" "$WORK_DIR/removed"
fi
if [ -s "$WORK_DIR/reverted" ]; then
    annotate error "$WORK_DIR/reverted" "German translation replaced by the English text"
    summary_list "German translation replaced by the English source text" "$WORK_DIR/reverted"
fi
if [ -s "$WORK_DIR/added" ]; then
    annotate error "$WORK_DIR/added" "New string without German translation"
    summary_list "New strings without German translation" "$WORK_DIR/added"
fi

echo
echo "Add the German translation of every string listed above to $PO_FILE."
echo "Run \`npm run i18n\` to find the new msgids, but commit only the de_DE.po change."
exit 1
