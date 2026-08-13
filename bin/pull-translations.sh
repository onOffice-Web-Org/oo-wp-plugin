#!/usr/bin/env bash
set -euo pipefail

OUTPUT_DIR="${1:-languages}"

if [ -z "${POEDITOR_READONLY_API_KEY:-}" ] || [ -z "${POEDITOR_PROJECT_ID:-}" ]; then
  echo "Missing POEDITOR_READONLY_API_KEY or POEDITOR_PROJECT_ID"
  exit 1
fi

mkdir -p "$OUTPUT_DIR"

echo "Fetching languages from POEditor…"

RAW_LANGS=$(curl -sS -X POST https://api.poeditor.com/v2/languages/list \
  -d api_token="$POEDITOR_READONLY_API_KEY" \
  -d id="$POEDITOR_PROJECT_ID" \
  | jq -r '.result.languages[]? | select(.percentage > 0) | .code')

if [ -z "$RAW_LANGS" ]; then
  echo "No languages found"
  exit 1
fi

echo "Raw languages returned:"
echo "$RAW_LANGS"

declare -A WP_LOCALES=(
  [de-at]="de_AT"
  [de-ch]="de_CH"
  [de_formal]="de_DE_formal"
  [de-formal]="de_DE_formal"
  [es]="es_ES"
  [es-cl]="es_CL"
  [es-es]="es_ES"
  [fr]="fr_FR"
  [fr-fr]="fr_FR"
  [it]="it_IT"
  [it-it]="it_IT"
  [nl]="nl_NL"
  [nl-nl]="nl_NL"
  [hr]="hr"
)

should_sync_wp_locale() {
  local loc="$1"
  case "$loc" in
    de_AT|de_CH|de_DE|de_DE_formal|es_CL|es_ES|fr_FR|it_IT|nl_NL|hr) return 0 ;;
    *) return 1 ;;
  esac
}

export_url() {
  local lang="$1"
  local type="$2"

  curl -sS -X POST https://api.poeditor.com/v2/projects/export \
    -d api_token="$POEDITOR_READONLY_API_KEY" \
    -d id="$POEDITOR_PROJECT_ID" \
    -d language="$lang" \
    -d type="$type" \
    -d order="terms" \
    | jq -r '.result.url // empty'
}

rm -f \
  "$OUTPUT_DIR/onoffice-for-wp-websites-de.po" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-de.mo" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-es.po" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-es.mo" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-fr.po" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-fr.mo" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-it.po" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-it.mo" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-nl.po" \
  "$OUTPUT_DIR/onoffice-for-wp-websites-nl.mo" || true

while IFS= read -r RAW; do
  [ -z "$RAW" ] && continue

  if [ "$RAW" = "de" ]; then
    echo "Skipping source language: $RAW"
    continue
  fi

  RAW_KEY="${RAW,,}"
  RAW_KEY="${RAW_KEY//_/-}"

  WP_LOCALE="${WP_LOCALES[$RAW_KEY]:-}"
  [ -z "$WP_LOCALE" ] && continue
  should_sync_wp_locale "$WP_LOCALE" || continue

  if [ "$WP_LOCALE" = "hr" ]; then
    PO_OUT="$OUTPUT_DIR/onoffice-for-wp-websites_hr.po"
    MO_OUT="$OUTPUT_DIR/onoffice-for-wp-websites_hr.mo"
  else
    PO_OUT="$OUTPUT_DIR/onoffice-for-wp-websites-${WP_LOCALE}.po"
    MO_OUT="$OUTPUT_DIR/onoffice-for-wp-websites-${WP_LOCALE}.mo"
  fi

  echo "Exporting $RAW → $PO_OUT and $MO_OUT"

  PO_URL="$(export_url "$RAW" "po")"
  if [ -z "$PO_URL" ]; then
    echo "No PO export URL for $RAW"
    exit 1
  fi
  curl -sS -L -o "$PO_OUT" "$PO_URL"

  MO_URL="$(export_url "$RAW" "mo")"
  if [ -z "$MO_URL" ]; then
    echo "No MO export URL for $RAW"
    exit 1
  fi
  curl -sS -L -o "$MO_OUT" "$MO_URL"

done <<< "$RAW_LANGS"

echo "Exporting POT…"
POT_URL=$(curl -sS -X POST https://api.poeditor.com/v2/projects/export \
  -d api_token="$POEDITOR_READONLY_API_KEY" \
  -d id="$POEDITOR_PROJECT_ID" \
  -d language="de" \
  -d type="pot" \
  -d order="terms" \
  | jq -r '.result.url // empty')

if [ -z "$POT_URL" ]; then
  echo "No POT export URL (check your source language code, for example en)"
  exit 1
fi

curl -sS -L -o "$OUTPUT_DIR/onoffice-for-wp-websites.pot" "$POT_URL"

echo "Done"