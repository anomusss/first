#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")/.."

ARCHIVE="dist/social-monitor-dashboard.zip"
rm -f "$ARCHIVE"

zip -r "$ARCHIVE" . \
  -x ".git/*" \
     "node_modules/*" \
     "dist/*" \
     "*.DS_Store"

echo "Gotowe: $ARCHIVE"
