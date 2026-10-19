#!/usr/bin/env bash
#
# Réinitialise le mot de passe administrateur, sans avoir à connaître le chemin
# de PHP.
#
#   bash tools/reset-admin.sh
#   bash tools/reset-admin.sh prof@exemple.fr
#   bash tools/reset-admin.sh prof@exemple.fr --password="MonNouveauMotDePasse"
#
# Le script cherche PHP automatiquement : MAMP (le plus récent installé),
# Homebrew, MacPorts, puis le PATH. Il s'arrête avec un message clair si aucun
# PHP 8 n'est trouvé.

set -euo pipefail
cd "$(dirname "$0")/.."

trouver_php() {
  local candidat version
  # MAMP : on prend la version la plus récente disponible (tri -V décroissant).
  if [ -d /Applications/MAMP/bin/php ]; then
    for candidat in $(ls -d /Applications/MAMP/bin/php/php8*/bin/php 2>/dev/null | sort -Vr); do
      [ -x "$candidat" ] && { echo "$candidat"; return 0; }
    done
    for candidat in $(ls -d /Applications/MAMP/bin/php/php*/bin/php 2>/dev/null | sort -Vr); do
      [ -x "$candidat" ] && { echo "$candidat"; return 0; }
    done
  fi
  # Autres emplacements courants.
  for candidat in /opt/homebrew/bin/php /usr/local/bin/php /opt/local/bin/php /usr/bin/php; do
    [ -x "$candidat" ] && { echo "$candidat"; return 0; }
  done
  # PATH, en dernier recours.
  if command -v php >/dev/null 2>&1; then command -v php; return 0; fi
  return 1
}

PHP_BIN="$(trouver_php || true)"

if [ -z "${PHP_BIN:-}" ]; then
  cat >&2 <<'MESSAGE'
PHP est introuvable sur cette machine.

Ouvre MAMP, clique sur « Open start page » puis sur l'onglet PHP : la version
utilisée y est indiquée. Lance ensuite le script avec le chemin complet, par
exemple :

  /Applications/MAMP/bin/php/php8.2.0/bin/php tools/reset-admin-password.php

Ou, sans terminal : dans phpMyAdmin, exécute

  DELETE FROM admins;

puis ouvre /admin/setup.php dans le navigateur pour créer un nouveau compte.
MESSAGE
  exit 1
fi

VERSION="$("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null || echo '?')"
echo "PHP utilisé : $PHP_BIN (version $VERSION)"
echo

exec "$PHP_BIN" tools/reset-admin-password.php "$@"
