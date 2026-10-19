#!/usr/bin/env bash
# Lance la suite de tests avec le PHP de MAMP (aucun PHP dans le PATH sur macOS).
set -euo pipefail
cd "$(dirname "$0")/.."

PHP_BIN=""
for c in $(ls -d /Applications/MAMP/bin/php/php8*/bin/php 2>/dev/null | sort -Vr) /opt/homebrew/bin/php /usr/local/bin/php; do
  [ -x "$c" ] && { PHP_BIN="$c"; break; }
done
[ -z "$PHP_BIN" ] && command -v php >/dev/null 2>&1 && PHP_BIN="$(command -v php)"

if [ -z "$PHP_BIN" ]; then
  echo "PHP introuvable. Utilisez le chemin complet, par exemple :" >&2
  echo "  /Applications/MAMP/bin/php/php8.2.0/bin/php tools/tests.php" >&2
  exit 2
fi

echo "PHP : $PHP_BIN"
exec "$PHP_BIN" tools/tests.php "$@"
