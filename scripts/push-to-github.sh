#!/bin/bash
set -euo pipefail

REMOTE_URL="https://github.com/anomusss/social-media.git"
BRANCH="$(git rev-parse --abbrev-ref HEAD)"

if ! git remote get-url origin >/dev/null 2>&1; then
  git remote add origin "$REMOTE_URL"
else
  git remote set-url origin "$REMOTE_URL"
fi

echo "Ustawiono origin -> $REMOTE_URL"
echo "Wysyłam branch: $BRANCH"
git push -u origin "$BRANCH"

echo "Gotowe. Sprawdź na GitHub: $REMOTE_URL"
