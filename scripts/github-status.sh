#!/bin/bash
set -euo pipefail

echo "== GitHub status check =="
echo

echo "[1] Katalog"
pwd

echo
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "[2] Repo: OK"
else
  echo "[2] Repo: BRAK (to nie jest repo git)"
  exit 1
fi

echo
BRANCH="$(git rev-parse --abbrev-ref HEAD)"
echo "[3] Branch: $BRANCH"

echo
if git remote get-url origin >/dev/null 2>&1; then
  ORIGIN="$(git remote get-url origin)"
  echo "[4] Origin: $ORIGIN"
else
  echo "[4] Origin: brak"
fi

echo
echo "[5] Ostatni commit lokalny:"
git log --oneline -n 1 || true

echo
echo "[6] Status plików:"
git status --short || true

echo
echo "[7] Spróbuj ręcznie wypchnąć na GitHub:"
echo "    git push -u origin $BRANCH"
