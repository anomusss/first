#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

echo "== Social Media Monitoring Dashboard: start =="

# Guard: verify script is executed from project root
if [ ! -f "package.json" ] || [ ! -d "apps/api" ] || [ ! -d "apps/web" ]; then
  echo "[ERROR] To nie wygląda na katalog projektu social-monitor-dashboard."
  echo "[TIP] Najpierw wejdź do folderu projektu (cd social-monitor-dashboard), potem uruchom: open URUCHOM.command"
  exit 1
fi

if ! command -v node >/dev/null 2>&1; then
  echo "[ERROR] Brak Node.js. Zainstaluj Node.js 20+ i uruchom ponownie."
  exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "[ERROR] Brak npm. Zainstaluj npm 10+ i uruchom ponownie."
  exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "[ERROR] Brak Dockera. Zainstaluj Docker Desktop i uruchom ponownie."
  exit 1
fi

if [ ! -f .env ]; then
  cp .env.example .env
  echo "[OK] Utworzono .env z .env.example"
fi

echo "[1/4] Uruchamiam PostgreSQL przez Docker..."
npm run db:up

echo "[2/4] Instaluję zależności npm..."
npm install

echo "[3/4] Tworzę lokalną paczkę ZIP (opcjonalnie)..."
bash scripts/create-release-zip.sh || true

echo "[4/4] Uruchamiam API + Frontend..."
echo "Frontend: http://localhost:5173"
echo "API:      http://localhost:4000/api/v1/health"

npm run dev
